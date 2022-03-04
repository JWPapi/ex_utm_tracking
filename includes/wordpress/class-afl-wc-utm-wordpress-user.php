<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_WORDPRESS_USER
{

  const META_PREFIX = 'afl_wc_utm_';
  const META_PREFIX_ACTIVE = 'afl_wc_utm_active_';
  const INTEGRATION_COLOR = 'blue';
  const INTEGRATION_COLOR_ACTIVE = 'green';

  private static $instance;
  private static $options;

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

  /**
  * @since 2.0.4
  */
  public static function init($options = array()){

    self::$options = AFL_WC_UTM_UTIL::merge_default($options, array(
      'global_user_option' => false
    ));

  }

  public static function register_hooks(){

    //register conversion event
    self::action_register_conversion_events();

    //register user
    add_action( 'user_register', array(__CLASS__, 'action_user_register'), 30, 1 );
    add_action( 'add_user_to_blog', array(__CLASS__, 'action_add_user_to_blog'), 30, 3 );

    //Admin Reports - Active Sessions
    add_filter( 'afl_wc_utm_filter_admin_reports_active_conversion', array(__CLASS__, 'filter_admin_reports_active_conversion'), 10, 1);

    //Admin Reports - User Report
    add_action( 'afl_wc_utm_action_admin_user_report_conversion_attributions', array(__CLASS__, 'action_admin_user_report_conversion_attributions_active_session'), 1, 1);
    add_action( 'afl_wc_utm_action_admin_user_report_conversion_attributions', array(__CLASS__, 'action_admin_user_report_conversion_attributions_registered_session'), 100, 1);

    //REST API
    add_action( 'rest_api_init', array(__CLASS__, 'action_rest_api_register_fields'), 10, 1);

  }

  public static function get_user_option($user_id, $option_name){

    if (is_multisite()) :

      if (self::get_option('global_user_option')) :

        return get_user_option(self::META_PREFIX . $option_name, $user_id);

      else:

        global $wpdb;

        return get_user_meta($user_id, $wpdb->get_blog_prefix() . self::META_PREFIX . $option_name, true);

      endif;
      
    else:

      return get_user_option(self::META_PREFIX . $option_name, $user_id);

    endif;

  }

  public static function update_user_option($user_id, $option_name, $value){
    update_user_option($user_id, self::META_PREFIX . $option_name, $value, self::get_option('global_user_option'));
  }

  public static function delete_user_option($user_id, $option_name){
    delete_user_option($user_id, self::META_PREFIX . $option_name, self::get_option('global_user_option'));
  }

  public static function action_user_register($user_id){

    try {

      //registered time
      $registered_ts = time();

      //set version
      self::update_user_option($user_id, 'version', AFL_WC_UTM_VERSION);

      self::update_user_option($user_id, 'registered_ts', $registered_ts);
      self::update_user_option($user_id, 'registered_blog_id', get_current_blog_id());

      //created by user
      if (is_user_logged_in() && !AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :
        self::update_user_option($user_id, 'created_by', get_current_user_id());
      endif;

      //prepare session
      $instance_session = AFL_WC_UTM_SESSION::instance();
      $instance_session->setup($user_id);

      $user_synced_session = $instance_session->get('user_synced_session');
      $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_lag($user_synced_session, $registered_ts);

      //get conversion event
      $conversion_event = self::prepare_conversion_event($user_id, $user_synced_session);

      //set conversion type
      $user_synced_session['conversion_type']['value'] = !empty($conversion_event['type']) ? $conversion_event['type'] : '';

      //save conversion attribution
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($user_synced_session, 'converted');
      self::save_conversion_attribution($attribution, $user_id);

      AFL_WC_UTM_SERVICE::trigger_conversion($conversion_event, $user_synced_session, $user_id);

    } catch (\Exception $e) {

    }

  }

  public static function action_add_user_to_blog($user_id, $role, $blog_id){

    try {

      if (
        self::get_user_option($user_id, 'registered_ts') >= 0
        && self::get_option('global_user_option')
        ) :
        return true;
      endif;

      //set version
      self::update_user_option($user_id, 'version', AFL_WC_UTM_VERSION);

      //registered time
      $registered_ts = time();

      self::update_user_option($user_id, 'registered_ts', $registered_ts);

      //created by user
      if (is_user_logged_in() && !AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :
        self::update_user_option($user_id, 'created_by', get_current_user_id());
      endif;

      //prepare session
      $instance_session = AFL_WC_UTM_SESSION::instance();
      $instance_session->setup($user_id);

      $user_synced_session = $instance_session->get('user_synced_session');
      $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_lag($user_synced_session, $registered_ts);

      //get conversion event
      $conversion_event = self::prepare_conversion_event($user_id, $user_synced_session);

      //set conversion type
      $user_synced_session['conversion_type']['value'] = !empty($conversion_event['type']) ? $conversion_event['type'] : '';

      //save conversion attribution
      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($user_synced_session, 'converted');
      self::save_conversion_attribution($attribution, $user_id);

      AFL_WC_UTM_SERVICE::trigger_conversion($conversion_event, $user_synced_session, $user_id);

    } catch (\Exception $e) {

    }

  }

  /**
   * @since 2.0.0
   */
  public static function get_active_user_option($user_id, $option_name){

    if (is_multisite()) :

      //check this first
      if (self::get_option('global_user_option')) :

        return get_user_option(self::META_PREFIX_ACTIVE . $option_name, $user_id);

      else:

        global $wpdb;

        return get_user_meta($user_id, $wpdb->get_blog_prefix() . self::META_PREFIX_ACTIVE . $option_name, true);

      endif;

    else:

      return get_user_option(self::META_PREFIX_ACTIVE . $option_name, $user_id);

    endif;

  }

  /**
   * @since 2.0.0
   */
  public static function update_active_user_option($user_id, $option_name, $value){
    update_user_option($user_id, self::META_PREFIX_ACTIVE . $option_name, $value, self::get_option('global_user_option'));
  }

  /**
   * @since 2.0.0
   */
  public static function delete_active_user_option($user_id, $option_name){
    delete_user_option($user_id, self::META_PREFIX_ACTIVE . $option_name, self::get_option('global_user_option'));
  }

  /**
   * @since 2.0.0
   */
  public static function action_register_conversion_events(){

    $cookie_conversion_account = AFL_WC_UTM_SETTINGS::get('cookie_conversion_account');

    AFL_WC_UTM_CONVERSION::register_event(array(
      'event' => 'wordpress_account',
      'label' => __( 'WordPress Account', AFL_WC_UTM_TEXTDOMAIN),
      'type' => AFL_WC_UTM_CONVERSION::TYPE_LEAD,
      'cookie_expiry' => $cookie_conversion_account,
      'css' => 'tw-bg-blue-600 hover:tw-bg-opacity-75 tw-text-white hover:tw-text-white'
    ));

  }

  /**
  * @since 2.0.0
  */
  public static function filter_admin_reports_active_conversion($conversion){

     if (!isset($conversion['event']) || $conversion['event'] !== 'wordpress_account') {
       return $conversion;
     }

     if (!empty($conversion['data']['user_id'])) :
       $conversion['data']['url'] = AFL_WC_UTM_WORDPRESS_USER::get_admin_url_profile(
         $conversion['data']['user_id'],
         !empty($conversion['data']['blog_id']) ? $conversion['data']['blog_id'] : null
       );

       $conversion['label'] .= ' #' . $conversion['data']['user_id'];
     endif;

     return $conversion;
  }

  /**
  * @since 2.0.0
  */
  public static function get_active_session($user_id){

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('active');

    if (empty($user_id)) {
      return $meta_whitelist;
    }

    try {

      $attribution = self::get_active_user_option($user_id, 'attribution');

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
          $meta_value = self::get_active_user_option($user_id, $meta_key);
          $meta['value'] = $meta_value !== false ? $meta_value : '';
        endforeach;

      endif;

    } catch (\Exception $e) {

    }

    return $meta_whitelist;
  }

  /*
  * @since  2.0.0
  */
  public static function update_active_session($user_id, $meta_whitelist){

    if (empty($user_id)) :
      return false;
    endif;

    try {

      $attribution = AFL_WC_UTM_SERVICE::prepare_attribution_data_for_saving($meta_whitelist, 'active');

      //save attribution
      if (AFL_WC_UTM_SETTINGS::get('attribution_format') === 'json') :

        //save to meta
        self::update_active_user_option($user_id, 'attribution', AFL_WC_UTM_UTIL::json_encode($attribution));

        //delete old
        if (self::get_active_user_option($user_id, 'cookie_consent') !== false || self::get_active_user_option($user_id, 'sess_visit') !== false) :

          foreach ($meta_whitelist as $meta_key => $meta) :
            self::delete_active_user_option($user_id, $meta_key);
          endforeach;

        endif;

      else:

        foreach ($meta_whitelist as $meta_key => $meta) :

          if (isset($attribution[$meta_key]) && $attribution[$meta_key] !== '' && $attribution[$meta_key] !== null && $attribution[$meta_key] !== false) :
            //update
            self::update_active_user_option($user_id, $meta_key, $attribution[$meta_key]);
          else:
            //delete
            self::delete_active_user_option($user_id, $meta_key);
          endif;

        endforeach;

        //delete old
        self::delete_active_user_option($user_id, 'attribution');

      endif;

      self::update_active_session_timestamp($user_id);

      //set version
      self::update_active_user_option($user_id, 'version', AFL_WC_UTM_VERSION);

    } catch (\Exception $e) {

    }

  }

  /*
  * @since  2.0.0
  */
  public static function delete_active_session($user_id){

    if (empty($user_id)) {
      return false;
    }

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('active');

    if (self::get_active_user_option($user_id, 'cookie_consent') !== false || self::get_active_user_option($user_id, 'sess_visit') !== false) :

      foreach ($meta_whitelist as $meta_key => $meta) :
        self::delete_active_user_option($user_id, $meta_key);
      endforeach;

    endif;

    self::delete_active_user_option($user_id, 'attribution');
    self::delete_active_user_option($user_id, 'conversions');
    self::delete_active_user_option($user_id, 'updated_ts');
    self::delete_active_user_option($user_id, 'updated_date_utc');
    self::delete_active_user_option($user_id, 'updated_date_local');
    self::delete_active_user_option($user_id, 'has_lead');
    self::delete_active_user_option($user_id, 'has_order');
    self::delete_active_user_option($user_id, 'version');

  }

  /*
  * @since  2.0.0
  */
  public static function update_active_session_timestamp($user_id){

    //updated time
    $time = time();

    self::update_active_user_option($user_id, 'updated_ts', $time);
    self::update_active_user_option($user_id, 'updated_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($time));
    self::update_active_user_option($user_id, 'updated_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($time));
  }

  /*
  * @since  2.0.0
  */
  public static function reset_active_session_if_expired($user_id = null){

    if (empty($user_id)) {
      return false;
    }

    $active_session = self::get_active_session($user_id);

    $cookie_expiry_in_days = !empty($active_session['cookie_expiry']['value']) ? intval($active_session['cookie_expiry']['value']) : 0;
    $updated_ts = intval(self::get_active_user_option($user_id, 'updated_ts'));

    if (empty($cookie_expiry_in_days) || empty($updated_ts)) {
      return false;
    }

    $ts_expiry = $updated_ts + ($cookie_expiry_in_days * 86400);
    $ts_now = time();

    //current time over the expiry time
    if ($ts_now < $ts_expiry) :
      return false;
    endif;

    //reset
    self::delete_active_session($user_id);

    return true;
  }

  /*
  * @since  2.0.0
  */
  public static function add_active_conversion($user_id, $conversion_event = array(), $user_synced_session = array()){

    if (empty($user_id)) :
      return false;
    endif;

    $conversion_event = AFL_WC_UTM_UTIL::merge_default($conversion_event, array(
      'event' => '',
      'type' => '',
      'data' => ''
    ));
    $conversion_event['data'] = isset($conversion_event['data']) ? $conversion_event['data'] : array();

    //save conversion list
    $conversions = self::get_active_user_option($user_id, 'conversions');

    if (empty($conversions)) :
      $conversions = array();
    endif;

    $conversions[] = $conversion_event;

    $offset_conversions = count($conversions);

    if ($offset_conversions > 5) :
      $offset_conversions = $offset_conversions - 5;
    else:
      $offset_conversions = 0;
    endif;

    $conversions = array_slice($conversions, $offset_conversions, 5);

    self::update_active_user_option($user_id, 'conversions', $conversions);

    switch($conversion_event['type']):

      case 'lead':

        self::update_active_user_option($user_id, 'has_lead', 1);

        if (!empty($conversion_event['data']['conversion_ts'])) :
          self::update_user_option($user_id, 'last_lead_ts', $conversion_event['data']['conversion_ts']);
          self::update_user_option($user_id, 'last_lead_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($conversion_event['data']['conversion_ts']));
          self::update_user_option($user_id, 'last_lead_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($conversion_event['data']['conversion_ts']));
        endif;

        break;

      case 'order':

        self::update_active_user_option($user_id, 'has_order', 1);

        if (!empty($conversion_event['data']['conversion_ts'])) :
          self::update_user_option($user_id, 'last_order_ts', $conversion_event['data']['conversion_ts']);
          self::update_user_option($user_id, 'last_order_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($conversion_event['data']['conversion_ts']));
          self::update_user_option($user_id, 'last_order_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($conversion_event['data']['conversion_ts']));
        endif;

        break;

    endswitch;

    return true;
  }

  /**
   * @since 2.0.0
   */
  public static function get_admin_url_profile($user_id, $blog_id = null){

    return add_query_arg(
      array(
      'user_id' => urlencode($user_id),
      ),
      get_admin_url($blog_id, 'user-edit.php')
    );
  }

  /**
   * @since 2.0.0
   */
  public static function action_admin_user_report_conversion_attributions_active_session($user){

    if (empty($user->ID)) {
      return;
    }

    echo AFL_WC_UTM_HTML::get_conversion_report_metabox_for_integration(
      __('Active Session', AFL_WC_UTM_TEXTDOMAIN),
      $user->ID,
      self::get_admin_url_profile($user->ID, get_current_blog_id()),
      self::get_active_session($user->ID),
      self::INTEGRATION_COLOR_ACTIVE
    );

  }

  /**
   * @since 2.0.0
   */
  public static function action_admin_user_report_conversion_attributions_registered_session($user){

    if (empty($user->ID)) {
      return;
    }

    echo AFL_WC_UTM_HTML::get_conversion_report_metabox_for_integration(
      __('WordPress Account', AFL_WC_UTM_TEXTDOMAIN),
      $user->ID,
      self::get_admin_url_profile($user->ID, get_current_blog_id()),
      self::get_conversion_attribution($user->ID),
      self::INTEGRATION_COLOR
    );

  }

  /**
   * @since 2.0.4
  */
  public static function get_meta_prefix(){
    global $wpdb;

    if (self::get_option('global_user_option')) :
      return self::META_PREFIX;
    else:
      return $wpdb->get_blog_prefix() . self::META_PREFIX;
    endif;

  }

  /**
   * @since 2.0.4
  */
  public static function get_meta_prefix_active(){
    global $wpdb;

    if (self::get_option('global_user_option')) :
      return self::META_PREFIX_ACTIVE;
    else:
      return $wpdb->get_blog_prefix() . self::META_PREFIX_ACTIVE;
    endif;

  }

  /*
  * @since  2.0.6
  */
  public static function get_option($key){

    if (isset(self::$options[$key])) {
      return self::$options[$key];
    } else {
      throw new \Exception('Invalid option key');
    }

  }

  /**
   * @since 2.4.0
   */
  public static function save_conversion_attribution($attribution, $user_id){

    //save attribution
    if (!empty($attribution)) :
      if (AFL_WC_UTM_SETTINGS::get('attribution_format') === 'json') :
        //save to meta
        self::update_user_option($user_id, 'attribution', AFL_WC_UTM_UTIL::json_encode($attribution));
      else:
        foreach ($attribution as $attribution_key => $attribution_value) :
          self::update_user_option($user_id, $attribution_key, $attribution_value);
        endforeach;
      endif;
    endif;

  }

  /**
   * @since 2.4.0
   */
  public static function get_conversion_attribution($user_id, $scope = 'converted'){

    try {

      $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist($scope);

      $user = get_user_by('ID', $user_id);

      if (empty($user)) :
        return $meta_whitelist;
      endif;

      $attribution = self::get_user_option($user_id, 'attribution');

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
          $meta_value = self::get_user_option($user_id, $meta_key);
          $meta['value'] = $meta_value !== false ? $meta_value : '';
        endforeach;

      endif;

    } catch (\Exception $e) {

    }

    return apply_filters('afl_wc_utm_wordpress_user_get_conversion_attribution', $meta_whitelist, $user_id, $scope);
  }

  /**
   * @since 2.4.0
   */
  public static function prepare_conversion_event($user_id, $meta_whitelist){

    $event = AFL_WC_UTM_CONVERSION::get_registered_event('wordpress_account');
    $event['data'] = array(
    'conversion_ts' => !empty($meta_whitelist['conversion_ts']['value']) ? $meta_whitelist['conversion_ts']['value'] : time(),
    'sess_visit' => !empty($meta_whitelist['sess_visit']['value']) ? $meta_whitelist['sess_visit']['value'] : '',
    'user_id' => $user_id,
    'blog_id' => get_current_blog_id()
    );

    return $event;
  }

  /**
   * @since 2.4.0
   */
  public static function has_active_attribution($user_id){

    $attribution = self::get_active_user_option($user_id, 'attribution');

    if (!empty($attribution)) :

      $attribution = AFL_WC_UTM_UTIL::json_decode($attribution);

      if (isset($attribution['sess_visit'])) :
        return true;
      endif;

    endif;

    $sess_visit = self::get_active_user_option($user_id, 'sess_visit');

    if ($sess_visit !== false) :
      return true;
    endif;

    return false;

  }

  /*
  * @since  2.4.0
  */
  public static function delete_active_attribution($user_id){

    if (empty($user_id)) {
      return false;
    }

    self::delete_active_user_option($user_id, 'attribution');

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist('active');

    if (self::get_active_user_option($user_id, 'sess_visit')) :

      foreach ($meta_whitelist as $meta_key => $meta) :
        self::delete_active_user_option($user_id, $meta_key);
      endforeach;

    endif;

  }

  /**
  * @since 2.0.0
  */
  public static function get_converted_session($user_id){

    _deprecated_function('AFL_WC_UTM_WORDPRESS_USER::get_converted_session', '2.4.0', 'AFL_WC_UTM_WORDPRESS_USER::get_conversion_attribution');

    $meta_whitelist = AFL_WC_UTM_SERVICE::get_meta_whitelist();

    $user = get_user_by('ID', $user_id);

    if (empty($user)) :
      return $meta_whitelist;
    endif;

    //populate value
    foreach ($meta_whitelist as $meta_key => &$meta) :
        $meta['value'] = self::get_user_option($user->ID, $meta_key);
    endforeach;

    return $meta_whitelist;
  }

  /**
   * @since 2.0.0
   */
  public static function calculate_conversion_lag($user_id){

    _deprecated_function('AFL_WC_UTM_WORDPRESS_USER::calculate_conversion_lag', '2.4.0', 'AFL_WC_UTM_WORDPRESS_USER::prepare_conversion_lag');

    try {

      $date_created_timestamp = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'registered_ts');
      $sess_visit_timestamp = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'sess_visit');

      if ($date_created_timestamp <= 0 || $sess_visit_timestamp <= 0) :
        return false;
      endif;

      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'conversion_ts', $date_created_timestamp);
      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'conversion_date_local', AFL_WC_UTM_UTIL::timestamp_to_local_date_database($date_created_timestamp, 'Y-m-d H:i:s'));
      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'conversion_date_utc', AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($date_created_timestamp, 'Y-m-d H:i:s'));

      $conversion_lag = intval($date_created_timestamp) - intval($sess_visit_timestamp);
      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'conversion_lag', $conversion_lag);
      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'conversion_lag_human', AFL_WC_UTM_UTIL::seconds_to_duration($conversion_lag));

    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

  /**
   * @since 2.4.12
   */
  public static function action_rest_api_register_fields($rest_server){

      try {

        if (defined('AFL_WC_UTM_SWITCH_REST_API_USER') && AFL_WC_UTM_SWITCH_REST_API_USER === true) :

          register_rest_field(
            'user',
            'afl_wc_utm_attribution',
              array(
                  'get_callback'          => 'AFL_WC_UTM_WORDPRESS_USER::get_rest_api_conversion_attribution',
                  'update_callback'       => null
              )
          );

          register_rest_field(
            'user',
            'afl_wc_utm_active_attribution',
              array(
                  'get_callback'          => 'AFL_WC_UTM_WORDPRESS_USER::get_rest_api_active_attribution',
                  'update_callback'       => null
              )
          );

          register_rest_field(
            'customer',
            'afl_wc_utm_attribution',
              array(
                  'get_callback'          => 'AFL_WC_UTM_WORDPRESS_USER::get_rest_api_conversion_attribution',
                  'update_callback'       => null
              )
          );

          register_rest_field(
            'customer',
            'afl_wc_utm_active_attribution',
              array(
                  'get_callback'          => 'AFL_WC_UTM_WORDPRESS_USER::get_rest_api_active_attribution',
                  'update_callback'       => null
              )
          );

        endif;

      } catch (\Exception $e) {

      }

  }

  /**
   * @since 2.4.12
   */
  public static function get_rest_api_conversion_attribution($user){

    $output = array();

    try {

      if (empty($user['id']) || !current_user_can('list_users')) :
        return $output;
      endif;

      $attribution = self::get_conversion_attribution($user['id']);

      if (!empty($attribution)) :
        foreach ($attribution as $attr_key => $attr) :
          $output[$attr_key] = isset($attr['value']) ? $attr['value'] : '';
        endforeach;
      endif;

    } catch (\Exception $e) {
      $output = array();
    }

    return $output;

  }

  /**
   * @since 2.4.12
   */
  public static function get_rest_api_active_attribution($user){

    $output = array();

    try {

      if (empty($user['id']) || !current_user_can('list_users') || AFL_WC_UTM_SETTINGS::get('active_attribution') !== '1') :
        return $output;
      endif;

      $attribution = self::get_active_session($user['id']);

      if (!empty($attribution)) :
        foreach ($attribution as $attr_key => $attr) :
          $output[$attr_key] = isset($attr['value']) ? $attr['value'] : '';
        endforeach;
      endif;

    } catch (\Exception $e) {
      $output = array();
    }

    return $output;

  }

}
