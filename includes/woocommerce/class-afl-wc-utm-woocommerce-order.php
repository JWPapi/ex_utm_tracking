<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_WOOCOMMERCE_ORDER
{
  const META_PREFIX = '_afl_wc_utm_';
  const INTEGRATION_COLOR = 'purple';

  private static $instance;

  public static function get_instance()
  {
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct(){

  }

  public static function register_hooks(){

    if (!class_exists('woocommerce')) {
      return;
    }

    //register conversion event
    self::action_register_conversion_events();

    //WooCommerce Admin - Order Page
    add_action( 'add_meta_boxes', array(__CLASS__, 'action_order_meta_box_register') );
    add_action( 'woocommerce_checkout_update_order_meta', array(__CLASS__, 'action_woocommerce_checkout_update_order_meta'), 30, 2 );

    //WooCommerce Admin - Orders Page
    add_filter( 'manage_edit-shop_order_columns', array(__CLASS__, 'filter_orders_column'), 10, 1 );
    add_action( 'manage_shop_order_posts_custom_column', array(__CLASS__, 'action_orders_column_value'), 10, 1 );

    //merge tags for {afl_wc_utm}
    add_filter( 'woocommerce_email_format_string', array(__CLASS__, 'filter_woocommerce_email_format_string'), 10, 2);

    //Admin Reports - Active Sessions
    add_filter( 'afl_wc_utm_filter_admin_reports_active_conversion', array(__CLASS__, 'filter_admin_reports_active_conversion'), 10, 1);

    //Admin Reports - User Report
    add_action( 'afl_wc_utm_action_admin_user_report_conversion_attributions', array(__CLASS__, 'action_admin_user_report_conversion_attributions'), 10, 1);

    //WooCommerce Subscription
    add_filter( 'wcs_renewal_order_meta_query', __CLASS__ . '::filter_wcs_renewal_order_meta_query', 10, 3);

  }

  public static function get_meta($order_id, $meta_key, $single = true){
    return get_post_meta($order_id, self::META_PREFIX . $meta_key, $single);
  }

  public static function update_meta($order_id, $meta_key, $meta_value){
    return update_post_meta($order_id, self::META_PREFIX . $meta_key, $meta_value);
  }

  public static function filter_orders_column($columns){

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

  public static function action_orders_column_value($column){
    global $post;

    try {

      switch ($column) :
        case 'afl_wc_utm_admin_column_conversion_lag':
        case 'afl_wc_utm_admin_column_utm_first':
        case 'afl_wc_utm_admin_column_utm_last':
        case 'afl_wc_utm_admin_column_clid':
        case 'afl_wc_utm_admin_column_sess_referer':

          $attribution = self::get_conversion_attribution($post->ID);

          echo AFL_WC_UTM_HTML::get_table_column_value($column, $attribution);

          break;

        default:

          break;
      endswitch;

    } catch (\Exception $e) {

    }

  }

  public static function action_order_meta_box_register(){

			add_meta_box(
				'afl-wc-utm-meta-box',
				'AFL UTM Tracker',
				array(__CLASS__, 'action_order_meta_box_display'),
				'shop_order',
				'side',
				'low'
			);

	}

	public static function action_order_meta_box_display(){
		global $post;

		if (!empty($post->ID)) :

      $attribution = self::get_conversion_attribution($post->ID);

      AFL_WC_UTM_HTML::render_metabox_content(AFL_WC_UTM_HTML::get_html_variables(), $attribution);
		endif;

  }

  public static function action_woocommerce_checkout_update_order_meta($order_id, $data){

    try {

      $order = wc_get_order($order_id);

      if (empty($order)) :
        return null;
      endif;

      $order_id = $order->get_id();
      $user_id = $order->get_customer_id();
      $date_created_timestamp = self::get_order_date_created_timestamp($order);

      //set version
      self::update_meta($order_id, 'version', AFL_WC_UTM_VERSION);

      //created by user
      if (is_user_logged_in() && !AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :
        self::update_meta($order_id, 'created_by', get_current_user_id());
      endif;

      //prepare session
      $instance_session = AFL_WC_UTM_SESSION::instance();
      $instance_session->setup($user_id);

      $user_synced_session = $instance_session->get('user_synced_session');
      $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_lag($user_synced_session, $date_created_timestamp);

      //get conversion event
      $conversion_event = self::prepare_conversion_event($order_id, $user_synced_session);

      //set conversion type
      $user_synced_session['conversion_type']['value'] = !empty($conversion_event['type']) ? $conversion_event['type'] : '';

      //prepare attribution for saving
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($user_synced_session, 'converted');

      //save conversion attribution
      self::save_conversion_attribution($attribution, $order_id);

      AFL_WC_UTM_SERVICE::trigger_conversion($conversion_event, $user_synced_session, $user_id);

    } catch (\Exception $e) {

    }

  }

  /**
   * @since 2.0.0
   */
  public static function action_register_conversion_events(){

    $cookie_conversion_order = AFL_WC_UTM_SETTINGS::get('cookie_conversion_order');

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'woocommerce',
      'label' => __( 'WooCommerce', AFL_WC_UTM_TEXTDOMAIN),
      'type' => AFL_WC_UTM_CONVERSION::TYPE_ORDER,
      'cookie_expiry' => $cookie_conversion_order,
      'css' => 'tw-bg-purple-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

  }

  /**
  * @since 2.0.0
  */
  public static function filter_admin_reports_active_conversion($conversion){

     if (!isset($conversion['event']) || $conversion['event'] !== 'woocommerce') {
       return $conversion;
     }

     if (!empty($conversion['data']['order_id'])) :

       $conversion['data']['url'] = AFL_WC_UTM_WOOCOMMERCE_ORDER::get_admin_url_order(
         $conversion['data']['order_id'],
         !empty($conversion['data']['blog_id']) ? $conversion['data']['blog_id'] : null
       );

       $conversion['label'] .= ' #' . $conversion['data']['order_id'];
     endif;

     return $conversion;
  }

  /**
   * @since 2.0.0
   */
  public static function filter_woocommerce_email_format_string($text, $email){

    if ( strpos( $text, '{afl_wc_utm}' ) === false ) :
      return $text;
    endif;

    if (empty($email->object) || !is_a($email->object, 'WC_Order')) :
      return $text;
    endif;

    $attribution = self::get_conversion_attribution($email->object->get_id(), 'email');

    $html = AFL_WC_UTM_HTML::get_conversion_report_table_for_email($attribution);

    $text = str_replace('{afl_wc_utm}', $html, $text);

    return $text;
  }

  /**
   * @since 2.0.0
   */
  public static function get_admin_url_order($order_id, $blog_id = null){

    return add_query_arg(
      array(
        'post' => urlencode($order_id),
        'action' => 'edit'
      ),
      get_admin_url($blog_id, 'post.php')
    );
  }

  /**
   * @since 2.0.0
   */
  public static function action_admin_user_report_conversion_attributions($user){

    if (empty($user->ID)) {
      return;
    }

    if (!function_exists('wc_get_customer_last_order')) {
      return;
    }

    $last_order = wc_get_customer_last_order($user->ID);

    if (empty($last_order)) :
      return;
    endif;

    $order_id = $last_order->get_id();

    echo AFL_WC_UTM_HTML::get_conversion_report_metabox_for_integration(
      __('WooCommerce Order', AFL_WC_UTM_TEXTDOMAIN),
      $order_id,
      self::get_admin_url_order($order_id),
      self::get_conversion_attribution($order_id),
      self::INTEGRATION_COLOR
    );

  }

  /**
   * @since 2.4.0
   */
   public static function prepare_conversion_event($order_id, $meta_whitelist){

     $event = AFL_WC_UTM_CONVERSION::get_registered_event('woocommerce');
     $event['data'] = array(
       'conversion_ts' => !empty($meta_whitelist['conversion_ts']['value']) ? $meta_whitelist['conversion_ts']['value'] : time(),
       'sess_visit' => !empty($meta_whitelist['sess_visit']['value']) ? $meta_whitelist['sess_visit']['value'] : '',
       'order_id' => $order_id,
       'blog_id' => get_current_blog_id()
     );

     return $event;

   }

   /**
    * @since 2.4.0
    */
   public static function save_conversion_attribution($attribution, $entry_id){

     //save attribution
     if (!empty($attribution)) :
       if (AFL_WC_UTM_SETTINGS::get('attribution_format') === 'json') :
         //save to meta
         self::update_meta($entry_id, 'attribution', AFL_WC_UTM_UTIL::json_encode($attribution));
       else:
         foreach ($attribution as $attribution_key => $attribution_value) :
           self::update_meta($entry_id, $attribution_key, $attribution_value);
         endforeach;
       endif;
     endif;

   }

  /**
   * @since 2.4.0
   */
  public static function get_conversion_attribution($entry_id, $scope = 'converted'){

    try {
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

    } catch (\Exception $e) {

    }

    return apply_filters('afl_wc_utm_woocommerce_order_get_conversion_attribution', $meta_whitelist, $entry_id, $scope);
  }

  /**
   * @since 2.4.0
   */
  public static function get_order_date_created_timestamp($order){

    try {

      $dt_date_created = $order->get_date_created();

      if (!($dt_date_created instanceof WC_DateTime)) :
        return time();
      endif;

      return $dt_date_created->getTimestamp();

    } catch (\Exception $e) {

    }

    return time();

  }

  /**
   * @since 2.4.0
   */
  public static function filter_wcs_renewal_order_meta_query($meta_query, $to_order, $from_order){
    global $wpdb;

    if (!empty(self::META_PREFIX)) :
      $meta_query .= $wpdb->prepare(" AND `meta_key` NOT LIKE %s", $wpdb->esc_like(self::META_PREFIX) . '_%%');
    endif;

    return $meta_query;
  }

  /**
   * @since 2.0.0
   */
  public static function get_converted_session($order_id){

    _deprecated_function('AFL_WC_UTM_WOOCOMMERCE_ORDER::get_converted_session', '2.4.0', 'AFL_WC_UTM_WOOCOMMERCE_ORDER::get_conversion_attribution');

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist();

    if (!empty($order_id)) :
      foreach ($meta_whitelist as $meta_key => &$meta) :
        $meta['value'] = self::get_meta($order_id, $meta_key, true);
      endforeach;
    endif;

    return apply_filters('afl_wc_utm_woocommerce_order_get_converted_session', $meta_whitelist, $order_id);
  }

  /**
   * @since 2.0.0
   */
  public static function calculate_conversion_lag($order){

    _deprecated_function('AFL_WC_UTM_WOOCOMMERCE_ORDER::calculate_conversion_lag', '2.4.0', 'AFL_WC_UTM_WOOCOMMERCE_ORDER::prepare_conversion_lag');

    try {

      if (!($order instanceof WC_Order)) {
        return false;
      }

      $order_id = $order->get_id();

      $dt_date_created = $order->get_date_created();

      if (!($dt_date_created instanceof WC_DateTime)) {
        return false;
      }

      $date_created_timestamp = $dt_date_created->getTimestamp();
      $sess_visit_timestamp = AFL_WC_UTM_WOOCOMMERCE_ORDER::get_meta($order_id, 'sess_visit');

      if ( $date_created_timestamp <= 0 || $sess_visit_timestamp <= 0) :
        return false;
      endif;

      AFL_WC_UTM_WOOCOMMERCE_ORDER::update_meta($order_id, 'conversion_ts', $date_created_timestamp);
      AFL_WC_UTM_WOOCOMMERCE_ORDER::update_meta($order_id, 'conversion_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($date_created_timestamp, 'Y-m-d H:i:s'));
      AFL_WC_UTM_WOOCOMMERCE_ORDER::update_meta($order_id, 'conversion_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($date_created_timestamp, 'Y-m-d H:i:s'));

      $conversion_lag = $date_created_timestamp - $sess_visit_timestamp;
      AFL_WC_UTM_WOOCOMMERCE_ORDER::update_meta($order_id, 'conversion_lag', $conversion_lag);
      AFL_WC_UTM_WOOCOMMERCE_ORDER::update_meta($order_id, 'conversion_lag_human', AFL_WC_UTM_UTIL::seconds_to_duration($conversion_lag));

    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

}
