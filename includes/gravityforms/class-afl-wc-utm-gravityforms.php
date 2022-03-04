<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.4.0
 */
class AFL_WC_UTM_GRAVITYFORMS extends AFL_WC_UTM_GRAVITYFORMS_LEGACY
{

  const META_PREFIX = '_afl_wc_utm_';
  const DEFAULT_CONVERSION_TYPE = 'lead';
  const DEFAULT_COOKIE_EXPIRY = 30;//days

  const INTEGRATION_SLUG = 'afl_wc_utm';
  const INTEGRATION_COLOR = 'orange';
  const INTEGRATION_SETTING_META_KEY = 'gravityformsaddon_afl_wc_utm_settings';

  private function __construct(){

  }

  public static function register_hooks(){

    //gravity forms addon
    if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
        return;
    }

    self::action_register_conversion_events();

    GFAddOn::register( 'AFL_WC_UTM_GRAVITYFORMS_ADDON' );

    //register entry meta
    add_filter( 'gform_entry_meta', 'AFL_WC_UTM_GRAVITYFORMS::gform_entry_meta', 10, 2);

    //register merge tags
    add_filter( 'gform_custom_merge_tags', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_custom_merge_tags', 10, 4 );
    add_filter( 'gform_merge_tag_data', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_merge_tag_data', 10, 4 );
    add_filter( 'gform_replace_merge_tags', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_replace_merge_tags' , 10, 7 );

    //form submission
    add_action( 'gform_entry_created', 'AFL_WC_UTM_GRAVITYFORMS::action_gform_entry_created', 10, 2 );

    //populate lead
    add_filter( 'gform_entry_post_save', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_post_save', 10, 2 );

    //partial entries
    add_action( 'gform_partialentries_post_entry_saved', 'AFL_WC_UTM_GRAVITYFORMS::action_gform_partialentries_post_entry_saved', 10, 2 );
    add_action( 'gform_partialentries_post_entry_updated', 'AFL_WC_UTM_GRAVITYFORMS::action_gform_partialentries_post_entry_saved', 10, 2 );

    //webhook
    add_filter( 'gform_webhooks_request_data', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_webhooks_request_data', 10, 4);

    //zapier
    add_filter('gform_zapier_request_body', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_zapier_request_body', 10, 4);

    //GF Admin - entries page
    add_filter( 'gform_entry_list_columns', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_list_columns', 10, 2 );
    add_filter( 'gform_entries_column_filter', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entries_column_filter', 10, 5 );

    //GF Admin - entry page
    add_filter( 'gform_entry_detail_meta_boxes', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_detail_meta_boxes' , 10, 3 );

    //GF Admin - export
    add_filter( 'gform_leads_before_export', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_leads_before_export', 10, 3 );

    //Plugin - Reports - Active table
    add_filter( 'afl_wc_utm_filter_admin_reports_active_conversion', 'AFL_WC_UTM_GRAVITYFORMS::filter_admin_reports_active_conversion', 10, 1);

    //Admin Reports - User Report
    add_action( 'afl_wc_utm_action_admin_user_report_conversion_attributions', 'AFL_WC_UTM_GRAVITYFORMS::action_admin_user_report_conversion_attributions', 10, 1);

  }

  public static function get_meta($entry_id, $meta_key){
    return gform_get_meta($entry_id, self::META_PREFIX . $meta_key);
  }

  public static function update_meta($entry_id, $meta_key, $meta_value, $form_id = null){
    return gform_update_meta($entry_id, self::META_PREFIX . $meta_key, $meta_value, $form_id);
  }

  public static function get_plugin_settings(){

    $settings = get_option(self::INTEGRATION_SETTING_META_KEY);

    $settings = AFL_WC_UTM_UTIL::merge_default($settings, array(
      'global_status' => '1'
    ));

    return $settings;
  }

  public static function get_form_settings($form){

    //global status
    $plugin_settings = self::get_plugin_settings();

    $default_enable = 1;

    if (isset($plugin_settings['global_status']) && $plugin_settings['global_status'] == 'default_disable') :
      $default_enable = 0;
    endif;

    $form_settings = rgar( $form, self::INTEGRATION_SLUG );

    $form_settings = AFL_WC_UTM_UTIL::merge_default($form_settings, array(
      'enable_attribution' => $default_enable,
      'conversion_type' => self::DEFAULT_CONVERSION_TYPE,
      'cookie_expiry' => self::DEFAULT_COOKIE_EXPIRY
    ));

    //force disable
    if (isset($plugin_settings['global_status']) && $plugin_settings['global_status'] == 'force_disable') :
      $form_settings['enable_attribution'] = 0;
    endif;

    return $form_settings;
  }

  public static function is_form_enabled($form){

    try {

      if (is_int($form)) :
        $form = GFAPI::get_form($form);
      endif;

      $form_settings = self::get_form_settings($form);

      if (isset($form_settings['enable_attribution'])) :
        return $form_settings['enable_attribution'] ? true : false;
      endif;

    } catch (\Exception $e) {

    }

    //always enabled if no settings
    return true;

  }

  public static function get_admin_url_entry($entry_id, $form_id, $blog_id = null){

   return add_query_arg(
     array(
       'page' => 'gf_entries',
       'view' => 'entry',
       'id' => urlencode($form_id),
       'lid' => urlencode($entry_id),
     ),
     get_admin_url($blog_id, 'admin.php')
   );

  }

  public static function get_conversion_attribution($entry_id, $scope = 'converted'){

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist($scope);

    try {

      $attribution = self::get_meta($entry_id, 'attribution');

      if (!empty($attribution)) :

        $attribution = AFL_WC_UTM_UTIL::json_decode($attribution);

        //populate value
        if (!empty($attribution) && is_array($attribution)) :
          foreach ($attribution as $meta_key => $meta_value) :
            if (isset($meta_whitelist[$meta_key]['value'])) :
              $meta_whitelist[$meta_key]['value'] = $attribution[$meta_key];
            endif;
          endforeach;
        endif;

      else:

        foreach ($meta_whitelist as $meta_key => &$meta) :
          $meta_value = self::get_meta($entry_id, $meta_key);
          $meta['value'] = $meta_value !== false ? $meta_value : '';
        endforeach;

      endif;

    } catch (\Exception $e) {

    }

    return apply_filters('afl_wc_utm_gravityforms_get_conversion_attribution', $meta_whitelist, $entry_id, $scope);
  }

  public static function action_register_conversion_events(){

    $gf = __( 'Gravity Forms', AFL_WC_UTM_TEXTDOMAIN);

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'gravityforms_lead',
      'label' => $gf,
      'type' => AFL_WC_UTM_CONVERSION::TYPE_LEAD,
      'cookie_expiry' => 30,
      'css' => 'tw-bg-orange-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'gravityforms_order',
      'label' => $gf,
      'type' => AFL_WC_UTM_CONVERSION::TYPE_ORDER,
      'cookie_expiry' => 7,
      'css' => 'tw-bg-orange-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

  }

  public static function action_gform_entry_created($entry, $form){

    try {

      //check form settings
      if (empty($form['id']) || empty($entry['id']) || !self::is_form_enabled($form)) :
        self::clear_form_hidden_fields($form, $entry);
        return;
      endif;

      $user_id = rgar($entry, 'created_by');

      //prepare session
      $session_instance = AFL_WC_UTM_GRAVITYFORMS_SESSION::instance();
      $session_instance->setup($form, $entry);

      //save hidden fields
      self::save_form_hidden_fields($form, $entry, $session_instance->get('hidden_fields'));

      //set version
      self::update_meta($entry['id'], 'version', AFL_WC_UTM_VERSION, $form['id']);

      //created by user
      if (is_user_logged_in() && !AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :
        self::update_meta($entry['id'], 'created_by', get_current_user_id(), $form['id']);
      endif;

      //save conversion attribution
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($session_instance->get('user_synced_session'), 'converted');
      self::save_conversion_attribution($attribution, $entry['id'], $form['id']);

      //trigger conversion event
      $conversion_event = self::prepare_form_conversion_event($form, $entry['id'], $session_instance->get('user_synced_session'));
      AFL_WC_UTM_SERVICE::trigger_conversion($conversion_event, $session_instance->get('user_synced_session'), $session_instance->get('created_by'));

    } catch (\Exception $e) {

    }

  }

  public static function action_gform_partialentries_post_entry_saved($entry, $form){

    try {

      //check form settings
      if (empty($form['id']) || empty($entry['id']) || !self::is_form_enabled($form)) :
        self::clear_form_hidden_fields($form, $entry);
        return;
      endif;

      $user_id = rgar($entry, 'created_by');

      //prepare session
      $session_instance = AFL_WC_UTM_GRAVITYFORMS_SESSION::instance();
      $session_instance->setup($form, $entry);

      //save hidden fields
      self::save_form_hidden_fields($form, $entry, $session_instance->get('hidden_fields'));

      //set version
      self::update_meta($entry['id'], 'version', AFL_WC_UTM_VERSION, $form['id']);

      //created by user
      if (is_user_logged_in() && !AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :
        self::update_meta($entry['id'], 'created_by', get_current_user_id(), $form['id']);
      endif;

      //save conversion attribution
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($session_instance->get('user_synced_session'), 'converted');
      self::save_conversion_attribution($attribution, $entry['id'], $form['id']);

    } catch (\Exception $e) {

    }

  }

  public static function save_conversion_attribution($attribution, $entry_id, $form_id){

    //save attribution
    if (!empty($attribution)) :
      if (AFL_WC_UTM_SETTINGS::get('attribution_format') === 'json') :
        //save to meta
        self::update_meta($entry_id, 'attribution', AFL_WC_UTM_UTIL::json_encode($attribution), $form_id);
      else:
        foreach ($attribution as $attribution_key => $attribution_value) :
          self::update_meta($entry_id, $attribution_key, $attribution_value, $form_id);
        endforeach;
      endif;
    endif;

  }

  public static function prepare_form_conversion_event($form_id, $entry_id, $meta_whitelist){

    if (isset($form_id['id'])) :

      $form = $form_id;
      $form_id = $form_id['id'];

    else:

      $form = GFAPI::get_form($form_id);

    endif;

    $form_settings = self::get_form_settings($form);

    $conversion_type = !empty($form_settings['conversion_type']) ? $form_settings['conversion_type'] : self::DEFAULT_CONVERSION_TYPE;

    $event = AFL_WC_UTM_CONVERSION::get_registered_event('gravityforms_' . $conversion_type);

    $event['cookie_expiry'] = !empty($form_settings['cookie_expiry']) ? $form_settings['cookie_expiry'] : self::DEFAULT_COOKIE_EXPIRY;
    $event['data'] = array(
      'conversion_ts' => !empty($meta_whitelist['conversion_ts']['value']) ? $meta_whitelist['conversion_ts']['value'] : time(),
      'sess_visit' => !empty($meta_whitelist['sess_visit']['value']) ? $meta_whitelist['sess_visit']['value'] : '',
      'entry_id' => $entry_id,
      'form_id' => $form_id,
      'blog_id' => get_current_blog_id()
    );

    return $event;
  }

  public static function filter_gform_entry_detail_meta_boxes($meta_boxes, $entry, $form){

    //check form settings
    if (!self::is_form_enabled($form)) :
      return $meta_boxes;
    endif;

    $meta_boxes[ self::INTEGRATION_SLUG ] = array(
      'title'    => 'AFL UTM Tracker',
      'callback' => 'AFL_WC_UTM_GRAVITYFORMS::render_entry_metabox_content',
      'context'  => 'side'
    );

    return $meta_boxes;
  }

  public static function render_entry_metabox_content($args){
    $form  = $args['form'];
    $entry = $args['entry'];

    $attribution = self::get_conversion_attribution($entry['id']);

    AFL_WC_UTM_HTML::render_metabox_content(AFL_WC_UTM_HTML::get_html_variables(), $attribution);
  }

  public static function filter_gform_entry_list_columns( $columns, $form_id ){

    //check form settings
    if (!self::is_form_enabled($form_id)) :
      return $columns;
    endif;

    $settings = AFL_WC_UTM_SETTINGS::get();

    if (!empty($settings['admin_column_conversion_lag'])) :
      $columns['afl_wc_utm_admin_column_conversion_lag'] = 'Conversion Lag';
    endif;

    if (!empty($settings['admin_column_utm_first'])) :
      $columns['afl_wc_utm_admin_column_utm_first'] = 'UTM (First)';
    endif;

    if (!empty($settings['admin_column_utm_last'])) :
      $columns['afl_wc_utm_admin_column_utm_last'] = 'UTM (Last)';
    endif;

    if (!empty($settings['admin_column_sess_referer'])) :
      $columns['afl_wc_utm_admin_column_sess_referer'] = 'Website Referrer';
    endif;

    if (!empty($settings['admin_column_clid'])) :
      $columns['afl_wc_utm_admin_column_clid'] = 'Click Identifier';
    endif;

    return $columns;
  }

  public static function filter_gform_entries_column_filter( $value, $form_id, $column, $entry, $query_string  ) {

    try {

      switch ($column) :
        case 'afl_wc_utm_admin_column_conversion_lag':
        case 'afl_wc_utm_admin_column_utm_first':
        case 'afl_wc_utm_admin_column_utm_last':
        case 'afl_wc_utm_admin_column_clid':
        case 'afl_wc_utm_admin_column_sess_referer':

          $attribution = self::get_conversion_attribution($entry['id']);

          return AFL_WC_UTM_HTML::get_table_column_value($column, $attribution);

          break;

        default:

          break;
      endswitch;

    } catch (\Exception $e) {

    }

    return $value;
  }

  public static function gform_entry_meta($entry_meta, $form_id){

    try {

      //check form setting
      if (!self::is_form_enabled($form_id)) :
        return $entry_meta;
      endif;

      $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('converted');

      if (empty($meta_whitelist)) :
        return $entry_meta;
      endif;

      foreach ($meta_whitelist as $meta_key => $meta) :

        $is_numeric = false;

        if (isset($meta['type'])) :
          switch($meta['type']):
            case 'timestamp':
            case 'integer':
              $is_numeric = true;
              break;
            endswitch;
        endif;

        $entry_meta[self::META_PREFIX . $meta_key] = array(
          'label' => 'AFL UTM | ' . $meta['label'],
          'is_numeric' => $is_numeric,
          'is_default_column' => false
        );

      endforeach;

    } catch (\Exception $e) {

    }

    return $entry_meta;
  }


  public static function filter_gform_entry_post_save($entry, $form){

    try {

      //check form setting
      if (empty($entry['id']) || empty($form['id']) || !self::is_form_enabled($form)) :
        return $entry;
      endif;

      //prepare session
      $session_instance = AFL_WC_UTM_GRAVITYFORMS_SESSION::instance();
      $session_instance->setup($form, $entry);

      $user_synced_session = $session_instance->get('user_synced_session');
      $hidden_fields = $session_instance->get('hidden_fields');

      //populate hidden fields
      if (!empty($hidden_fields)) :

        foreach ($hidden_fields as $field_key => $field_value) :
          if (isset($entry[$field_key])) :
            $entry[$field_key] = $field_value;
          endif;
        endforeach;

      endif;

      //populate entry meta into lead
      $entry_meta = GFFormsModel::get_entry_meta($form['id']);

  		foreach ( $entry_meta as $meta_key => $meta_config ) :

        if (strpos($meta_key, self::META_PREFIX) === false) :
          continue;
        endif;

        $short_meta_key = str_replace(self::META_PREFIX, '', $meta_key);
        $entry[$meta_key] = isset($user_synced_session[$short_meta_key]['value']) ? $user_synced_session[$short_meta_key]['value'] : '';

  		endforeach;

    } catch (\Exception $e) {

    }

    return $entry;
  }

  public static function filter_gform_custom_merge_tags($merge_tags, $form_id, $fields, $element_id){

    try {

      //check form setting
      if (!self::is_form_enabled($form_id)) :
        return $merge_tags;
      endif;

      $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('converted');

      if (empty($meta_whitelist)) :
        return $merge_tags;
      endif;

      $merge_tags[] = array(
        'label' => 'AFL UTM - All Fields',
        'tag' => '{afl_wc_utm}'
      );

      foreach ($meta_whitelist as $meta_key => $meta) :
        $merge_tags[] = array(
          'label' => esc_html('AFL UTM - ' . $meta['label']),
          'tag' => '{afl_wc_utm:' . sanitize_key($meta_key) . '}'
        );
      endforeach;

    } catch (\Exception $e) {

    }

    return $merge_tags;
  }


  public static function filter_gform_merge_tag_data($data, $text, $form, $entry) {

    try {

      //check form setting
      if (empty($entry['id']) || !self::is_form_enabled($form)) :
        return $data;
      endif;

      $attribution = self::get_conversion_attribution($entry['id'] , 'converted');

      foreach ($attribution as $meta_key => $meta) :

        if (isset($attribution[$meta_key]['type']) && isset($attribution[$meta_key]['value'])) :

          $data['afl_wc_utm'][$meta_key] = AFL_WC_UTM_UTIL::sanitize_meta_value_by_type($attribution[$meta_key]['value'], $attribution[$meta_key]['type']);

        else:

          $data['afl_wc_utm'][$meta_key] = '';

        endif;

      endforeach;
      
    } catch (\Exception $e) {

    }

    return $data;
  }

  public static function filter_gform_replace_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format){

    try {

      //check form setting
      if (empty($entry['id']) || !self::is_form_enabled($form)) :
        return $text;
      endif;

      if ( strpos( $text, '{afl_wc_utm}' ) === false ) :
        return $text;
      endif;

      $attribution = self::get_conversion_attribution($entry['id'], 'email');

      $html = AFL_WC_UTM_HTML::get_conversion_report_table_for_email($attribution);

      $text = str_replace('{afl_wc_utm}', $html, $text);

    } catch (\Exception $e) {

    }

    return $text;
  }

   public static function filter_admin_reports_active_conversion($conversion){

     if (!isset($conversion['event'])) {
       return $conversion;
     }

     switch ($conversion['event']) :
       case 'gravityforms_lead':
       case 'gravityforms_order':

         if (!empty($conversion['data']['entry_id']) && !empty($conversion['data']['form_id'])) :

           $conversion['data']['url'] = self::get_admin_url_entry(
             $conversion['data']['entry_id'],
             $conversion['data']['form_id'],
             !empty($conversion['data']['blog_id']) ? $conversion['data']['blog_id'] : null
           );

           $conversion['label'] .= ' #' . $conversion['data']['entry_id'];
         endif;

         break;

      endswitch;

      return $conversion;
   }

  public static function action_admin_user_report_conversion_attributions($user){

    if (empty($user->ID)) :
      return;
    endif;

    $search_criteria = array(
      'field_filters' => array(
        array('key' => 'created_by', 'value' => $user->ID)
      )
    );

    $sorting = array('key' => 'id', 'direction' => 'DESC', 'is_numeric' => true);
    $paging = array('offset' => 0, 'page_size' => 1);

    $entries = GFAPI::get_entries(0, $search_criteria, $sorting, $paging);

    if (is_wp_error($entries) || empty($entries)) :
      return;
    endif;

    $entry_id = rgar($entries[0], 'id');
    $form_id = rgar($entries[0], 'form_id');

    echo AFL_WC_UTM_HTML::get_conversion_report_metabox_for_integration(
      __('Gravity Forms', AFL_WC_UTM_TEXTDOMAIN),
      $entry_id,
      self::get_admin_url_entry($entry_id, $form_id),
      self::get_conversion_attribution($entry_id),
      self::INTEGRATION_COLOR
    );

  }

  public static function filter_gform_leads_before_export( $entries, $form, $paging ){

    if (!self::is_form_enabled($form)) :
      return $entries;
    endif;

    try {

      if (count($entries)) :

        $export_blank_setting = sanitize_text_field(AFL_WC_UTM_SETTINGS::get('export_blank'));

        foreach ($entries as $entry_index => $entry) :

          if (!empty($entry['id'])) :

            $attribution = self::get_conversion_attribution($entry['id']);

            foreach ($attribution as $attribute_key => $attribute) :

              $attribute_meta_key = self::META_PREFIX . $attribute_key;

              if (isset($entries[$entry_index][$attribute_meta_key])) :

                if (isset($attribute['value']) && $attribute['value'] !== false && $attribute['value'] !== '' && $attribute['value'] !== null) :

                  $entries[$entry_index][$attribute_meta_key] = $attribute['value'];

                else:

                  $entries[$entry_index][$attribute_meta_key] = $export_blank_setting;

                endif;

              endif;

            endforeach;

          endif;

        endforeach;
      endif;

    } catch (\Exception $e) {

    }

    return $entries;

  }

  /**
   * @since 2.4.3
   */
  public static function filter_gform_webhooks_request_data($request_data, $feed, $entry, $form){

    try {

      if (is_array($request_data) && !empty($request_data)) :

        foreach ($request_data as $tmp_key => $tmp_value) :

          if (empty($tmp_value) && gettype($tmp_value) === 'boolean') :

            if (strpos($tmp_key, AFL_WC_UTM_GRAVITYFORMS::META_PREFIX) === 0 || strpos($tmp_key, 'AFL UTM |') === 0) :
              $request_data[$tmp_key] = (string) $request_data[$tmp_key];
            endif;

          endif;

        endforeach;

      endif;

    } catch (\Exception $e) {

    }

    return $request_data;
  }

  /**
   * @since 2.4.4
   */
  public static function filter_gform_zapier_request_body($body, $feed, $entry, $form){

    try {

      if (!isset($form['id']) || !isset($entry['id'])) :
        return $body;
      endif;

      //check form setting
      if (!self::is_form_enabled($form)) :
        return $body;
      endif;

      //repopulate body
      $attribution = self::get_conversion_attribution($entry['id']);

      if (empty($attribution)) :
        return $body;
      endif;

      foreach ($attribution as $attribute_key => $attribute) :

        if (isset($attribute['label'])) :

          $body_key = 'AFL UTM | ' . $attribute['label'];

          if (isset($body[$body_key]) && isset($attribute['value'])) :

            $body[$body_key] = $attribute['value'];

            if (empty($body[$body_key]) && gettype($body[$body_key]) === 'boolean') :
              $body[$body_key] = (string) $body[$body_key];
            endif;

          endif;

        endif;

      endforeach;

    } catch (\Exception $e) {

    }

    return $body;

  }

  /*
  * @since  2.4.6
  */
  public static function prepare_form_hidden_fields($form, $user_synced_session){

    $output = array();

    if (!isset($form['fields'])) :
      return $output;
    endif;

    foreach ($form['fields'] as $field_key => $field) :

      $field_id = rgar($field, 'id');
      $default_value = rgar($field, 'defaultValue');

      if (!empty($default_value) && AFL_WC_UTM_UTIL::has_merge_tag($default_value)) :

        $output[$field_id] = AFL_WC_UTM_UTIL::get_merge_tag_value($default_value, $user_synced_session);

      elseif ((rgar($field, 'allowsPrepopulate'))) :

        $input_name = rgar($field, 'inputName');

        if (AFL_WC_UTM_UTIL::has_merge_tag($input_name)) :

          $output[$field_id] = AFL_WC_UTM_UTIL::get_merge_tag_value($input_name, $user_synced_session);

        endif;

      endif;

    endforeach;

    return $output;

  }

  /*
  * @since  2.4.6
  */
  public static function save_form_hidden_fields($form, $entry, $hidden_fields){

    if (!isset($form['fields']) || !isset($entry['id'])) :
      return false;
    endif;

    foreach ($form['fields'] as $field_key => $field) :

      $field_id = rgar($field, 'id');

      if (isset($hidden_fields[$field_id])) :
        gform_update_meta($entry['id'], $field_id, sanitize_text_field($hidden_fields[$field_id]));
      endif;

    endforeach;

    return true;

  }

  /*
  * @since  2.4.6
  */
  public static function clear_form_hidden_fields($form, $entry){

    if (!isset($form['fields']) || !isset($entry['id'])) :
      return false;
    endif;

    foreach ($form['fields'] as $field_key => $field) :

      $field_id = rgar($field, 'id');
      $default_value = rgar($field, 'defaultValue');

      if (AFL_WC_UTM_UTIL::has_merge_tag($default_value)) :
        gform_delete_meta($entry['id'], $field_id);
      endif;

    endforeach;

    return true;

  }

  /*
  * @since  2.4.6
  */
  public static function get_form_conversion_type($form){

    $form_settings = self::get_form_settings($form);

    return isset($form_settings['conversion_type']) ? $form_settings['conversion_type'] : self::DEFAULT_CONVERSION_TYPE;

  }

}
