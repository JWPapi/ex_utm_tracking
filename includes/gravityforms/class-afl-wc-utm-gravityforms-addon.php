<?php defined( 'ABSPATH' ) || exit;
GFForms::include_addon_framework();

/**
 *
 */
class AFL_WC_UTM_GRAVITYFORMS_ADDON extends GFAddOn
{
  const META_PREFIX = '_afl_wc_utm_';
  const C_DEFAULT_CONVERSION_TYPE = 'lead';
  const C_DEFAULT_COOKIE_EXPIRY = 30;//days

  protected $_version = AFL_WC_UTM_VERSION;
  protected $_min_gravityforms_version = '2.4';
  protected $_slug = 'afl_wc_utm';
  protected $_path = 'afl-wc-utm/includes/gravityforms/class-afl-wc-utm-gravityforms-addon.php';
  protected $_full_path = __FILE__;
  protected $_title = 'AFL UTM Tracker';
  protected $_short_title = 'AFL UTM Tracker';

  private static $instance;

  public static function get_instance(){
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * @since 2.0.0
   */
  public function plugin_settings_fields() {

    $columns_section = array(
      'title'  => esc_html__( 'General Settings', AFL_WC_UTM_TEXTDOMAIN ),
      'description' => '<p>' . esc_html__('The table columns settings have been moved to our main plugin settings menu.') . '</p>',
      'fields' => array(
        array(
          'label'   => esc_html__( 'Global Status', AFL_WC_UTM_TEXTDOMAIN ),
          'type'    => 'select',
          'name'    => 'global_status',
          'tooltip' => esc_html__( 'Enable this Addon', AFL_WC_UTM_TEXTDOMAIN ),
          'description' => esc_html__('Go to the individual form settings to overwrite this setting but FORCE DISABLE will take the highest priority.', AFL_WC_UTM_TEXTDOMAIN),
          'horizontal' => true,
          'default_value' => '1',
          'choices' => array(
            array(
              'label' => esc_html__( 'By default ENABLE conversion attribution for all forms', AFL_WC_UTM_TEXTDOMAIN ),
              'value'  => 'default_enable'
            ),
            array(
              'label' => esc_html__( 'By default DISABLE conversion attribution for all forms', AFL_WC_UTM_TEXTDOMAIN ),
              'value'  => 'default_disable'
            ),
            array(
              'label' => esc_html__( 'FORCE DISABLE conversion attribution for all forms', AFL_WC_UTM_TEXTDOMAIN ),
              'value'  => 'force_disable'
            )
          )
        )
      )
    );

    return array(
      $columns_section
    );
  }

  /**
   * @since 2.0.0
   */
  public function form_settings_fields($form){

    $plugin_settings = AFL_WC_UTM_GRAVITYFORMS::get_plugin_settings();

    $enable_attribution_default = $plugin_settings['global_status'] == '1' ? '1' : '0';

    return array(
      array(
        'title' => esc_html__( 'Conversion Attribution', AFL_WC_UTM_TEXTDOMAIN ),
        'fields' => array(
          array(
                'label'   => esc_html__( 'Enable Attribution', AFL_WC_UTM_TEXTDOMAIN ),
                'type'    => 'checkbox',
                'name'    => 'enable_attribution',
                'tooltip' => esc_html__( 'Do you want to save the user\'s conversion attribution for this form?', AFL_WC_UTM_TEXTDOMAIN ),
                'choices' => array(
                    array(
                        'label' => esc_html__( 'Enabled', AFL_WC_UTM_TEXTDOMAIN ),
                        'name'  => 'enable_attribution',
                        'default_value' => $enable_attribution_default,
                    ),
                )
            ),
            array(
                'label'   => esc_html__( 'Conversion Type', AFL_WC_UTM_TEXTDOMAIN ),
                'type'    => 'select',
                'name'    => 'conversion_type',
                'tooltip' => esc_html__( 'How do you want to categorized the form submission? (Default value is Lead)', AFL_WC_UTM_TEXTDOMAIN ),
                'default_value' => 'lead',
                'choices' => array(
                    array(
                        'label' => esc_html__( 'Lead', AFL_WC_UTM_TEXTDOMAIN ),
                        'value' => 'lead'
                    ),
                    array(
                        'label' => esc_html__( 'Order', AFL_WC_UTM_TEXTDOMAIN ),
                        'value' => 'order'
                    )
                )
            ),
            array(
                'label'               => esc_html__( 'Reset Attribution after Form Submission (days)', AFL_WC_UTM_TEXTDOMAIN ),
                'type'                => 'text',
                'name'                => 'cookie_expiry',
                'tooltip'             => esc_html__( "When the user submits the form, reset the user's attribution after number of days of inactivity. This allows a new attribution session to start.", AFL_WC_UTM_TEXTDOMAIN ),
                'description'         => esc_html__('Recommended Values. Lead: 30 days | Order: 7 days | Minimum 1 day.', AFL_WC_UTM_TEXTDOMAIN),
                'default_value'       => self::C_DEFAULT_COOKIE_EXPIRY,
                'validation_callback' => array( $this, 'c_validation_is_valid_cookie_expiry' ),
                'error_message'       => esc_html__( 'Minimum value is 1.', AFL_WC_UTM_TEXTDOMAIN )
            ),
        )//fields
      )//section
    );

  }

  public function c_validation_is_valid_cookie_expiry($field, $field_value){

    if (ctype_digit($field_value) && $field_value > 0) :

      return true;

    else:

      $this->set_field_error($field, rgar( $field, 'error_message' ) );
      return false;

    endif;

  }

  public function c_action_gform_save_field_value($value, $entry, $field, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_action_gform_save_field_value', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::action_gform_save_field_value');

    return AFL_WC_UTM_GRAVITYFORMS::action_gform_save_field_value($value, $entry, $field, $form);
  }

  public function c_action_gform_entry_created($entry, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_action_gform_entry_created', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::action_gform_entry_created');

    AFL_WC_UTM_GRAVITYFORMS::action_gform_entry_created($entry, $form);

  }

  public function c_filter_gform_entry_detail_meta_boxes($meta_boxes, $entry, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_entry_detail_meta_boxes', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_detail_meta_boxes');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_detail_meta_boxes($meta_boxes, $entry, $form);
  }

  public function c_render_entry_metabox_content($args){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_render_entry_metabox_content', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::render_entry_metabox_content');

    AFL_WC_UTM_GRAVITYFORMS::render_entry_metabox_content($args);
  }

  public function c_filter_gform_entry_list_columns( $table_columns, $form_id ){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_entry_list_columns', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_list_columns');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_list_columns( $table_columns, $form_id );
  }

  public function c_filter_gform_entries_column_filter( $value, $form_id, $field_id, $entry, $query_string  ) {

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_entries_column_filter', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entries_column_filter');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_entries_column_filter( $value, $form_id, $field_id, $entry, $query_string  );
  }

  /**
   * @since 2.0.0
   */
  public static function c_get_meta($entry_id, $meta_key){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_meta', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS_ADDON::get_meta');

    return AFL_WC_UTM_GRAVITYFORMS::get_meta($entry_id, $meta_key);
  }

  /**
   * @since 2.0.0
   */
  public static function c_update_meta($entry_id, $meta_key, $meta_value, $form_id = null){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS_ADDON::update_meta');

    return AFL_WC_UTM_GRAVITYFORMS::update_meta($entry_id, $meta_key, $meta_value, $form_id);
  }

  /**
   * @since 2.0.0
   */
  public function c_is_form_enabled_attribution($form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_is_form_enabled_attribution', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::is_form_enabled');

    try {

      if (is_int($form)) :
        $form = GFAPI::get_form($form);
      endif;

      $form_settings = $this->get_form_settings($form);

      //if attribution disabled, dont continue
      if (isset($form_settings['enable_attribution']) && empty($form_settings['enable_attribution'])) :
        return false;
      endif;

      return true;

    } catch (\Exception $e) {

    }

    return false;

  }

  /**
   * @since 2.0.0
   */
  public function c_gform_entry_meta($entry_meta, $form_id){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_gform_entry_meta', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::gform_entry_meta');

    return AFL_WC_UTM_GRAVITYFORMS::gform_entry_meta($entry_meta, $form_id);
  }

  /**
   * @since 2.0.0
   */
  public function c_filter_gform_entry_post_save($lead, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_entry_post_save', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_post_save');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_entry_post_save($lead, $form);
  }

  /**
   * @since 2.0.0
   */
  public function c_get_form_conversion_event($form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_form_conversion_event', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::get_form_conversion_event');

    $form_settings = $this->get_form_settings($form);

    $conversion_type = !empty($form_settings['conversion_type']) ? $form_settings['conversion_type'] : self::C_DEFAULT_CONVERSION_TYPE;

    $event = AFL_WC_UTM_CONVERSION::get_registered_event('gravityforms_' . $conversion_type);
    $event['cookie_expiry'] = !empty($form_settings['cookie_expiry']) ? $form_settings['cookie_expiry'] : self::C_DEFAULT_COOKIE_EXPIRY;
    return $event;
  }

  /**
   * @since 2.0.0
   */
  public static function c_get_converted_session($entry){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_converted_session', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::get_conversion_attribution');

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist();

    //populate value
    foreach ($meta_whitelist as $meta_key => &$meta) :
        //get gravity forms meta
        $meta['value'] = AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_meta($entry['id'], $meta_key);

        if ($meta['value'] === false) :
          $meta['value'] = '';
        endif;
    endforeach;

    return apply_filters('afl_wc_utm_gravityforms_get_converted_session', $meta_whitelist, $entry);
  }

  /**
   * @since 2.0.0
   */
  public static function c_calculate_conversion_lag($entry){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_calculate_conversion_lag', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::prepare_conversion_lag');

    try {

      $date_created = rgar($entry, 'date_created');
      $date_created_timestamp = $date_created ? AFL_WC_UTM_UTIL::utc_date_database_to_timestamp($date_created) : time();
      $sess_visit_timestamp = AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_meta($entry['id'], 'sess_visit');

      if ($date_created_timestamp <= 0 || $sess_visit_timestamp <= 0) :
        return false;
      endif;

      AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta($entry['id'], 'conversion_ts', $date_created_timestamp);
      AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta($entry['id'], 'conversion_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($date_created_timestamp, 'Y-m-d H:i:s'));
      AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta($entry['id'], 'conversion_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($date_created_timestamp, 'Y-m-d H:i:s'));

      $conversion_lag = $date_created_timestamp - $sess_visit_timestamp;
      AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta($entry['id'], 'conversion_lag', $conversion_lag);
      AFL_WC_UTM_GRAVITYFORMS_ADDON::c_update_meta($entry['id'], 'conversion_lag_human', AFL_WC_UTM_UTIL::seconds_to_duration($conversion_lag));

    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

  /**
   * @since 2.0.0
   */
  public function c_filter_gform_custom_merge_tags($merge_tags, $form_id, $fields, $element_id){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_custom_merge_tags', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_custom_merge_tags');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_custom_merge_tags($merge_tags, $form_id, $fields, $element_id);
  }

  /**
   * @since 2.0.0
   */
  public function c_filter_gform_merge_tag_data($data, $text, $form, $entry) {

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_merge_tag_data', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_merge_tag_data');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_merge_tag_data($data, $text, $form, $entry);
  }

  /**
   * @since 2.0.0
   */
  public function c_gform_replace_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_gform_replace_merge_tags', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_replace_merge_tags');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_replace_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format);
  }

  /**
   * @since 2.0.0
   */
  public static function c_get_admin_url_entry($entry_id, $form_id, $blog_id = null){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_get_admin_url_entry', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::get_admin_url_entry');

    return AFL_WC_UTM_GRAVITYFORMS::get_admin_url_entry($entry_id, $form_id, $blog_id);
  }

  /**
   * @since 2.0.0
   */
   public static function c_filter_admin_reports_active_conversion($conversion){

     _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_admin_reports_active_conversion', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_admin_reports_active_conversion');

     return AFL_WC_UTM_GRAVITYFORMS::filter_admin_reports_active_conversion($conversion);
   }

  /**
   * @since 2.0.0
   */
  public static function c_action_admin_user_report_conversion_attributions_session($user){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_action_admin_user_report_conversion_attributions_session', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::action_admin_user_report_conversion_attributions_session');

    AFL_WC_UTM_GRAVITYFORMS::action_admin_user_report_conversion_attributions_session($user);

  }

  public function c_filter_gform_export_field_value( $value, $form_id, $field_id, $entry ) {

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_gform_export_field_value', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_gform_export_field_value');

    return AFL_WC_UTM_GRAVITYFORMS::filter_gform_export_field_value($value, $form_id, $field_id, $entry);
  }

  /**
   * @since 2.3.0
   */
  public function c_filter_migrate_field_values( $entries, $form, $paging ){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_filter_migrate_field_values', '2.4.0', 'AFL_WC_UTM_GRAVITYFORMS::filter_migrate_field_values');

    return AFL_WC_UTM_GRAVITYFORMS::filter_migrate_field_values($entries, $form, $paging);

  }

}
