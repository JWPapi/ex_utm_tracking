<?php defined( 'ABSPATH' ) || exit;

use FluentForm\App\Helpers\Helper as FluentFormHelper;
use FluentForm\App\Modules\Acl\Acl as FluentAcl;

/**
 *
 */
class AFL_WC_UTM_FLUENTFORM
{

  const META_PREFIX = '_afl_wc_utm_';
  const DEFAULT_CONVERSION_TYPE = 'lead';
  const DEFAULT_COOKIE_EXPIRY = 30;//days
  const INTEGRATION_COLOR = 'orange';
  const INTEGRATION_SETTING_META_KEY = 'afl_wc_utm_feed';

  private function __construct(){

  }

  public static function register_hooks(){

    if (!function_exists('wpFluentForm') || !function_exists('wpFluent') || !method_exists('FluentForm\App\Helpers\Helper', 'getFormMeta') || !method_exists('FluentForm\App\Helpers\Helper', 'getSubmissionMeta')) :
      return;
    endif;

    self::action_register_conversion_events();

    new AFL_WC_UTM_FLUENTFORM_BOOTSTRAP(wpFluentForm());

    //Plugin - Reports - Active table
    add_filter( 'afl_wc_utm_filter_admin_reports_active_conversion', 'AFL_WC_UTM_FLUENTFORM::filter_admin_reports_active_conversion', 10, 1);

    //Plugin - Reports - User Report
    add_action( 'afl_wc_utm_action_admin_user_report_conversion_attributions', 'AFL_WC_UTM_FLUENTFORM::action_admin_user_report_conversion_attributions', 10, 1);

    //admin
    add_action( 'admin_enqueue_scripts', 'AFL_WC_UTM_FLUENTFORM::action_enqueue_admin_script', 10, 1 );

  }

  public static function action_enqueue_admin_script($hook){

    if (!isset($_GET['page']) || $_GET['page'] !== 'fluent_forms') :
      return;
    endif;

    if (isset($_GET['route']) && $_GET['route'] === 'entries' && !empty($_GET['form_id'])) :

      if (self::is_form_enabled(wp_unslash($_GET['form_id']))) :
        wp_enqueue_script( 'afl-wc-utm-admin-fluentform-entry', plugin_dir_url( AFL_WC_UTM_PLUGIN_FILE ) . 'admin/js/fluentform-entry.min.js', array(), AFL_WC_UTM::VERSION );
      endif;

    endif;
  }

  public static function action_register_conversion_events(){

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'fluentform_lead',
      'label' => 'Fluent Form',
      'type' => AFL_WC_UTM_CONVERSION::TYPE_LEAD,
      'cookie_expiry' => 30,
      'css' => 'tw-bg-orange-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'fluentform_order',
      'label' => 'Fluent Form',
      'type' => AFL_WC_UTM_CONVERSION::TYPE_ORDER,
      'cookie_expiry' => 7,
      'css' => 'tw-bg-orange-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

  }

  public static function validate_form_settings($integration, $integrationId, $formId){

    if (isset($integration['conversion_type'])
      && !in_array($integration['conversion_type'], array('lead', 'order'))
      ) :

        wp_send_json_error([
            'message' => 'Validation Failed',
            'errors'  => [
                'name' => ['Conversion Type value not selected.']
            ]
        ], 423);

    endif;

    if (isset($integration['cookie_expiry'])) :

      $integration['cookie_expiry'] = intval($integration['cookie_expiry']);

      if ($integration['cookie_expiry'] <= 0) :

        wp_send_json_error([
            'message' => 'Validation Failed',
            'errors'  => [
                'name' => ["'Reset Attribution after Form Submission' value must be more than 0"]
            ]
        ], 423);

      endif;

    endif;

    return $integration;

  }

  public static function get_form_settings($form_id){

    $form_settings = FluentFormHelper::getFormMeta($form_id, self::INTEGRATION_SETTING_META_KEY, array());

    $form_settings = AFL_WC_UTM_UTIL::merge_default($form_settings, array(
      'enabled' => 0,
      'conversion_type' => self::DEFAULT_CONVERSION_TYPE,
      'cookie_expiry' => self::DEFAULT_COOKIE_EXPIRY
    ));

    return $form_settings;

  }

  public static function is_form_enabled($form_id){

    $form_settings = self::get_form_settings($form_id);

    return $form_settings['enabled'] ? true : false;

  }

  public static function get_meta($entry_id, $meta_key){

    return FluentFormHelper::getSubmissionMeta($entry_id, sanitize_key(self::META_PREFIX . $meta_key), '');

  }

  //sanitize before use
  public static function update_meta($entry_id, $meta_key, $meta_value, $form_id){

    $meta_key = sanitize_key(self::META_PREFIX . $meta_key);
    $meta_value = maybe_serialize($meta_value);

    // check if submission exist
    $meta = wpFluent()->table('fluentform_submission_meta')
        ->where('response_id', $entry_id)
        ->where('meta_key', $meta_key)
        ->first();

    if ($meta) {
        wpFluent()->table('fluentform_submission_meta')
            ->where('id', $meta->id)
            ->insert([
                'value'      => $meta_value,
                'updated_at' => current_time('mysql')
            ]);
        return $meta->id;
    }

    if (!$form_id) {
        $submission = wpFluent()->table('fluentform_submissions')
            ->find($entry_id);
        if ($submission) {
            $form_id = $submission->form_id;
        }
    }

    return wpFluent()->table('fluentform_submission_meta')
        ->insert([
            'response_id' => $entry_id,
            'form_id'     => $form_id,
            'meta_key'    => $meta_key,
            'value'       => $meta_value,
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql')
        ]);

  }

  public static function get_conversion_attribution($entry_id, $scope = 'converted'){

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist($scope);

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

    return apply_filters('afl_wc_utm_fluentform_get_conversion_attribution', $meta_whitelist, $entry_id, $scope);
  }

  public static function get_user_latest_entry($user_id){

    global $wpdb;

    try {

      $entry = array();

      $entry = $wpdb->get_row(
        $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'fluentform_submissions WHERE user_id = %d ORDER BY id DESC LIMIT 1',
          $user_id
        ),
        ARRAY_A
      );

    } catch (\Exception $e) {

    }

    return $entry;

  }

  public static function get_admin_url_entry($entry_id, $form_id, $blog_id = null){

    return add_query_arg(
      array(
        'page' => 'fluent_forms',
        'route' => 'entries',
        'form_id' => urlencode($form_id)
      ),
      get_admin_url($blog_id, 'admin.php')
    ) . '#/entries/' . urlencode($entry_id);

  }

  public static function filter_register_smartcodes($groups, $form){

    try {

      if (empty($form->id) || !self::is_form_enabled($form->id)) :
        return $groups;
      endif;

      $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('converted');

      $shortcodes = array();
      $shortcodes['{afl_wc_utm.html_table}'] = 'AFL UTM - HTML Report Table';

      foreach ($meta_whitelist as $meta_key => $meta) :
        //select only converted scope
        $shortcodes['{afl_wc_utm.' . sanitize_key($meta_key) . '}'] = sanitize_text_field('AFL UTM - ' . $meta['label']);
      endforeach;

      $groups[] = array(
        'title' => 'AFL UTM Tracker',
        'shortcodes' => $shortcodes
      );

    } catch (\Exception $e) {

    }

    return $groups;

  }

  public static function filter_get_smartcode_value($meta_key, $shortcodeparser){

    try {

      $entry = $shortcodeparser->getEntry();

      if (empty($entry->id)) :
        return '';
      endif;

      if ($meta_key === 'html_table') :

        $attribution = self::get_conversion_attribution($entry->id, 'email');

        return AFL_WC_UTM_HTML::get_conversion_report_table_for_email($attribution);

      else:

        $attribution = self::get_conversion_attribution($entry->id, 'converted');

        if (isset($attribution[$meta_key]['type']) && isset($attribution[$meta_key]['value'])) :

          return AFL_WC_UTM_UTIL::sanitize_meta_value_by_type($attribution[$meta_key]['value'], $attribution[$meta_key]['type']);

        else:

          return '';

        endif;

      endif;

    } catch (\Exception $e) {

    }

    return '';

  }

  public static function action_form_submit($entry_id, $form_data, $form){

    try {

      if (empty($form->id) || !self::is_form_enabled($form->id)) :
        return;
      endif;

      $form_id = $form->id;

      //set version
      self::update_meta($entry_id, 'version', AFL_WC_UTM_VERSION, $form_id);

      //prepare session
      $instance_session = AFL_WC_UTM_FLUENTFORM_SESSION::instance();
      $instance_session->setup($form_id);
      $user_synced_session = $instance_session->get('user_synced_session');

      //save conversion attribution
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($user_synced_session, 'converted');
      self::save_conversion_attribution($attribution, $entry_id, $form_id);

      //trigger conversion event
      $conversion_event = self::prepare_form_conversion_event($form_id, $entry_id, $user_synced_session);
      AFL_WC_UTM_SERVICE::trigger_conversion($conversion_event, $user_synced_session, $instance_session->get('user_id'));

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

    $form_settings = self::get_form_settings($form_id);

    $conversion_type = !empty($form_settings['conversion_type']) ? $form_settings['conversion_type'] : self::DEFAULT_CONVERSION_TYPE;

    $event = AFL_WC_UTM_CONVERSION::get_registered_event('fluentform_' . $conversion_type);

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

  public static function filter_fluentform_all_entry_labels($labels, $form_id){

    if (empty($_GET['action']) || $_GET['action'] !== 'fluentform-form-entries') :
      return $labels;
    endif;

    if (!self::is_form_enabled($form_id)) :
      return $labels;
    endif;

    $settings = AFL_WC_UTM_SETTINGS::get();

    if (!empty($settings['admin_column_conversion_lag'])) :
      $labels['afl_wc_utm_admin_column_conversion_lag'] = 'Conversion Lag';
    endif;

    if (!empty($settings['admin_column_utm_first'])) :
      $labels['afl_wc_utm_admin_column_utm_first'] = 'UTM (First)';
    endif;

    if (!empty($settings['admin_column_utm_last'])) :
      $labels['afl_wc_utm_admin_column_utm_last'] = 'UTM (Last)';
    endif;

    if (!empty($settings['admin_column_sess_referer'])) :
      $labels['afl_wc_utm_admin_column_sess_referer'] = 'Website Referrer';
    endif;

    if (!empty($settings['admin_column_clid'])) :
      $labels['afl_wc_utm_admin_column_clid'] = 'Click Identifier';
    endif;

    return $labels;
  }

  public static function filter_fluentform_all_entries($submissions){

    try {

      $request = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_GET), array(
        'action' => '',
        'form_id' => 0
      ));

      if ($request['action'] !== 'fluentform-form-entries' || empty($request['form_id']) || empty($submissions['data'])) :
        return $submissions;
      endif;

      //check form setting
      if (!self::is_form_enabled($request['form_id'])) :
        return $submissions;
      endif;

      $settings = AFL_WC_UTM_SETTINGS::get();

      //populate entries
      foreach ($submissions['data'] as $index => $single_submission) :

        if (!isset($single_submission->id)) :
          continue;
        endif;

        $attribution = self::get_conversion_attribution($single_submission->id);

        if (!empty($settings['admin_column_conversion_lag'])) :
          $submissions['data'][$index]->user_inputs['afl_wc_utm_admin_column_conversion_lag'] = AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_conversion_lag', $attribution);
        endif;

        if (!empty($settings['admin_column_utm_first'])) :
          $submissions['data'][$index]->user_inputs['afl_wc_utm_admin_column_utm_first'] = AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_first', $attribution);
        endif;

        if (!empty($settings['admin_column_utm_last'])) :
          $submissions['data'][$index]->user_inputs['afl_wc_utm_admin_column_utm_last'] = AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_last', $attribution);
        endif;

        if (!empty($settings['admin_column_sess_referer'])) :
          $submissions['data'][$index]->user_inputs['afl_wc_utm_admin_column_sess_referer'] = AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_referer', $attribution);
        endif;

        if (!empty($settings['admin_column_clid'])) :
          $submissions['data'][$index]->user_inputs['afl_wc_utm_admin_column_clid'] = AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_clid', $attribution);
        endif;

      endforeach;

    } catch (\Exception $e) {

    }

    return $submissions;
  }

  public static function action_ff_fluentform_form_application_view_entries($form_id){

    //check form setting
    if (!self::is_form_enabled($form_id)) :
      return;
    endif;

    $html = <<<'EOT'
<div class="el-row" id="afl-wc-utm-fluentform-entry-wrapper" style="display:none">
  <div class="el-col el-col-24 el-col-xs-24 el-col-sm-18 el-col-md-18 el-col-lg-18">
    <div class="entry_info_box" id="afl-wc-utm-metabox" data-ajax_url="%1$s" data-action="afl_wc_utm_admin_fluentform_get_conversion_attribution">
      <div class="entry_info_header">
        <div class="info_box_header">AFL UTM Tracker</div>
      </div>
      <div class="entry_info_body"></div>
    </div>
  </div>
</div>
EOT;

    printf($html,
      esc_url(admin_url('admin-ajax.php'))
    );

  }

  public static function action_ajax_get_entry_attribution(){

    try {

      //check permission
      FluentAcl::verify('fluentform_entries_viewer');

      //double check
      self::verify_admin_ajax('fluentform_entries_viewer');

      $request = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_GET), array(
        'form_id' => '',
        'entry_id' => 0
      ));

      //check form setting
      if (empty($request['entry_id']) || !self::is_form_enabled($request['form_id'])) :
        return;
      endif;

      $attribution = self::get_conversion_attribution($request['entry_id']);

      $html = AFL_WC_UTM_HTML::get_metabox_content(AFL_WC_UTM_HTML::get_html_variables(), $attribution);

      wp_send_json_success(array(
        'metabox' => $html
      ));

    } catch (\Exception $e) {

    }

  }

  public static function filter_export_data($data, $form, $exportData, $inputLabels){

    //exit if not found
    if (!is_array($data) || !self::is_form_enabled($form->id)) :
      return $data;
    endif;

    try {

      //find entry id column index
      $found_column_index = false;

      foreach ($data as $data_index => $row) :

        if ($data_index == 0 && is_array($row)) :

            foreach ($row as $column_index => $column) :

              if ($column === 'entry_id') :

                $found_column_index = $column_index;
                break;

              endif;

            endforeach;

        endif;

      endforeach;

      //exit if not found
      if ($found_column_index === false) :
        return $data;
      endif;

      $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('converted');

      //insert column names
      foreach ($data as $data_index => &$data_row) :

        if ($data_index == 0) :

          $column_names = array();

          foreach ($meta_whitelist as $meta_key => $meta) :
            if (isset($meta['label'])) :
              $column_names[] = sanitize_text_field('AFL UTM | ' . $meta['label']);
            else:
              $column_names[] = 'AFL UTM | Unknown Column';
            endif;
          endforeach;

          $data_row = array_merge($data_row, $column_names);
          break;

        endif;

      endforeach;

      $export_blank_setting = sanitize_text_field(AFL_WC_UTM_SETTINGS::get('export_blank'));

      //insert column values
      foreach ($data as $data_index => &$data_row) :

        if ($data_index === 0) :
          continue;
        endif;

        //get entry id
        $tmp_entry_id = $data_row[$found_column_index];

        //get attribution
        $attribution = self::get_conversion_attribution($tmp_entry_id);

        $csv_attribution = array();

        foreach ($meta_whitelist as $meta_key => $meta) :

          if (isset($attribution[$meta_key]['value'])) :

            if ($attribution[$meta_key]['value'] !== false && $attribution[$meta_key]['value'] !== '' && $attribution[$meta_key]['value'] !== null) :
              $csv_attribution[] = $attribution[$meta_key]['value'];
            else:
              $csv_attribution[] = $export_blank_setting;
            endif;

          else:

            $csv_attribution[] = $export_blank_setting;

          endif;

        endforeach;

        $data_row = array_merge($data_row, $csv_attribution);

      endforeach;

    } catch (\Exception $e) {

    }

    return $data;
  }

  public static function filter_fluentform_webhook_request_data($selectedData, $settings, $data, $form, $entry){

    try {

      //exit if not found
      if (!is_array($selectedData)) :
        return $selectedData;
      endif;

      if (!isset($entry->form_id) || !isset($entry->id) || !self::is_form_enabled($entry->form_id)) :
        return $selectedData;
      endif;

      $meta_whitelist = self::get_conversion_attribution($entry->id);

      if (empty($meta_whitelist)) :
        return $selectedData;
      endif;

      $output_attribution = array();

      foreach ($meta_whitelist as $meta_key => $meta) :

          if (!isset($meta['value'])) :
            continue;
          endif;

          if (isset($meta['type'])) :

            switch($meta['type']):

              case 'url':

                $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = AFL_WC_UTM_UTIL::sanitize_url($meta['value']);

                break;

              default:

                $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = sanitize_text_field($meta['value']);

                break;

            endswitch;

          else:

            $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = sanitize_text_field($meta['value']);

          endif;

      endforeach;

      $selectedData = array_merge($selectedData, $output_attribution);

    } catch (\Exception $e) {

    }

    return $selectedData;

  }

  public static function filter_fluentform_integration_data_zapier($payload, $feed, $entry){

    try {

      //exit if not found
      if (!isset($payload['body']) || !is_array($payload['body'])) :
        return $payload;
      endif;

      if (!isset($entry->form_id) || !isset($entry->id) || !self::is_form_enabled($entry->form_id)) :
        return $payload;
      endif;

      $meta_whitelist = self::get_conversion_attribution($entry->id);

      if (empty($meta_whitelist)) :
        return $payload;
      endif;

      $output_attribution = array();

      foreach ($meta_whitelist as $meta_key => $meta) :

          if (!isset($meta['value'])) :
            continue;
          endif;

          if (isset($meta['type'])) :

            switch($meta['type']):

              case 'url':

                $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = AFL_WC_UTM_UTIL::sanitize_url($meta['value']);

                break;

              default:

                $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = sanitize_text_field($meta['value']);

                break;

            endswitch;

          else:

            $output_attribution[self::META_PREFIX . sanitize_key($meta_key)] = sanitize_text_field($meta['value']);

          endif;

      endforeach;

      $payload['body'] = array_merge($payload['body'], $output_attribution);

    } catch (\Exception $e) {

    }

    return $payload;

  }

  public static function filter_admin_reports_active_conversion($conversion){

    if (!isset($conversion['event'])) {
      return $conversion;
    }

    switch ($conversion['event']) :
      case 'fluentform_lead':
      case 'fluentform_order':

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

    $entry = self::get_user_latest_entry($user->ID);

    if (empty($entry['id']) || empty($entry['form_id'])) :
      return;
    endif;

    $attribution = self::get_conversion_attribution($entry['id']);

    if (empty($attribution)) :
      return;
    endif;

    echo AFL_WC_UTM_HTML::get_conversion_report_metabox_for_integration(
      __('Fluent Form', AFL_WC_UTM_TEXTDOMAIN),
      $entry['id'],
      self::get_admin_url_entry($entry['id'], $entry['form_id']),
      $attribution,
      self::INTEGRATION_COLOR
    );

  }

  /*
  * @since  2.4.6
  */
  public static function filter_insert_response_data($form_data, $form_id, $input_configs){

    try {

      if (empty($form_id) || !self::is_form_enabled($form_id)) :
        return $form_data;
      endif;

      //prepare session
      $instance_session = AFL_WC_UTM_FLUENTFORM_SESSION::instance();
      $instance_session->setup($form_id);
      $user_synced_session = $instance_session->get('user_synced_session');

      foreach ($input_configs as $input_key => $config) :

        if (isset($config['raw']['attributes']['type'])
         && $config['raw']['attributes']['type'] === 'hidden'
         && isset($config['raw']['attributes']['value'])
         ) :

          $merge_tag = $config['raw']['attributes']['value'];

          if (isset($form_data[$input_key]) && AFL_WC_UTM_UTIL::has_merge_tag($merge_tag)) :

            //replace
            $form_data[$input_key] = AFL_WC_UTM_UTIL::get_merge_tag_value($merge_tag, $user_synced_session);

          endif;

        endif;

      endforeach;

    } catch (\Exception $e) {

    }

    return $form_data;
  }

  /*
  * @since  2.4.6
  */
  public static function get_form_conversion_type($form_id){

    $form_settings = self::get_form_settings($form_id);

    return isset($form_settings['conversion_type']) ? $form_settings['conversion_type'] : self::DEFAULT_CONVERSION_TYPE;

  }

  /*
  * @since  2.4.6
  */
  public static function verify_admin_ajax($permission, $formId = null){

    if (!wp_doing_ajax() || !is_user_logged_in()) :
			return;
		endif;

    $nonce = isset($_REQUEST['fluent_forms_admin_nonce']) ? sanitize_text_field($_REQUEST['fluent_forms_admin_nonce']) : '';

    if (!wp_verify_nonce($nonce, 'fluent_forms_admin_nonce')) :

			wp_send_json_error(array(
				'message' => __('Nonce verification failed, please try again.', AFL_WC_UTM_TEXTDOMAIN)
			), 422);

		endif;

    $allowed = self::has_permission($permission, $formId);

    if (!$allowed) :

      wp_send_json_error(array(
          'message' => __('You do not have permission.', AFL_WC_UTM_TEXTDOMAIN)
      ), 422);

    endif;

  }

  /*
  * @since  2.4.6
  */
  public static function has_permission($permission, $formId = false){

    if (current_user_can('fluentform_full_access')) {
        return true;
    }

    if (is_array($permission)) {
        foreach ($permission as $eachPermission) {
            $allowed = current_user_can($eachPermission);
            if ($allowed) {
                return apply_filters('fluentform_verify_user_permission_' . $eachPermission, $allowed, $formId);
            } else {
                $isHookAllowed = apply_filters('fluentform_permission_callback', false, $eachPermission, $formId);
                if ($isHookAllowed) {
                    return true;
                }
            }
        }
        return false;
    }

    $allowed = current_user_can($permission);
    $allowed = apply_filters('fluentform_verify_user_permission_' . $permission, $allowed, $formId);

    if ($allowed) {
        return true;
    }

    return apply_filters('fluentform_permission_callback', false, $permission, $formId);

  }

}
