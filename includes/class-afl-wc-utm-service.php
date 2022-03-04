<?php defined( 'ABSPATH' ) || exit;

class AFL_WC_UTM_SERVICE
{

  const COOKIE_PREFIX_NAME = 'afl_wc_utm_';

  const COOKIE_EXPIRY_SHORT = 604800;//7 days
  const COOKIE_EXPIRY_MEDIUM = 2592000;//30 days
  const COOKIE_EXPIRY_LONG = 7776000;//90 days

  const DEFAULT_CONVERSION_TYPE = 'lead';

  private static $instance;
  private static $options = array();

  private static $meta_whitelist = array();
  private static $cookie_whitelist = array();

  private static $utm_whitelist = array(
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_term',
    'utm_content'
  );

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

  /*
  * @since  2.0.0
  */
  public static function init($options = array()){

    self::$options = AFL_WC_UTM_UTIL::merge_default($options, array(
      'site_settings' => ''
    ));

    self::$meta_whitelist = include AFL_WC_UTM_DIR_INCLUDES . 'config/meta.php';

    //setup cookie whitelist
    $cookies = array();

    foreach (self::$meta_whitelist as $meta_key => $meta) :
      if (!empty($meta['is_cookie'])) :
        $cookies[$meta_key] = $meta;
      endif;
    endforeach;

    self::$cookie_whitelist = $cookies;

  }

  public static function get_meta_whitelist($scope = ''){

    if (!empty($scope)) :

      $scope_whitelist = array();

      foreach (self::$meta_whitelist as $meta_key => $meta) :

        if (isset($meta['scope']) && in_array($scope, (array)$meta['scope'])) :
          $scope_whitelist[$meta_key] = $meta;
        endif;

      endforeach;

      return $scope_whitelist;

    else:

      return self::$meta_whitelist;

    endif;

  }

  public static function get_cookie_whitelist(){

    return self::$cookie_whitelist;
  }

  public static function get_cookie_name($cookie_name = ''){

    return self::get_cookie_prefix() . $cookie_name;
  }

  public static function get_cookie_prefix(){

    if (self::get_site_settings('cookie_domain')) {
      return self::COOKIE_PREFIX_NAME;
    } elseif (is_multisite()) {
      return self::COOKIE_PREFIX_NAME . get_current_blog_id() . '_';
    } else {
      return self::COOKIE_PREFIX_NAME;
    }

  }

  public static function get_domain_info(){

    if (self::get_site_settings('cookie_domain')) :

      $cookie_domain = rtrim(self::get_site_settings('cookie_domain'), '/');
      $cookie_domain = ltrim($cookie_domain, '.');

      $parse_domain = parse_url('http://' . $cookie_domain);

      $info = array(
        'domain' => isset($parse_domain['host']) ? $parse_domain['host'] : '',
        'path' => '/'
      );

    elseif (is_multisite()) :

      $blog = get_blog_details(get_current_blog_id(), true);

      $info = array(
        'domain' => $blog->domain,
        'path' => rtrim($blog->path, '/') . '/'
      );

    else:

      $home_url = parse_url(get_option('home', ''));

      $info = array(
        'domain' => isset($home_url['host']) ? $home_url['host'] : '',
        'path' => '/',
      );

    endif;

    return $info;
  }

  public static function is_verified_domain($parse_url){

    if (is_string($parse_url)) :
      $parse_url = parse_url($parse_url);
    endif;

    if (!isset($parse_url['host']) && empty($parse_url['host'])) :
      return false;
    endif;

    if (self::get_site_settings('cookie_domain')) :

      $parse_cookie_domain = parse_url('https://' . self::get_site_settings('cookie_domain'));

      if (isset($parse_cookie_domain['host'])) :
        $split_cookie_domain = explode('.', $parse_cookie_domain['host']);
        $split_cookie_domain = array_reverse($split_cookie_domain);

        $total_split_cookie_domain = count($split_cookie_domain);
      else:
        // not verified domain
        return false;
      endif;

      $split_cookie_value = explode('.', $parse_url['host']);
      $split_cookie_value = array_reverse($split_cookie_value);

      $total_split_url = count($split_cookie_value);

      foreach ($split_cookie_domain as $split_index => $split_value) :
        if (isset($split_cookie_value[$split_index])) :
          if ($split_value != $split_cookie_value[$split_index]) :
            // not verified domain
            return false;
          endif;
        else:
          // not verified domain
          return false;
        endif;
      endforeach;

      //verified
      return true;

    elseif (is_multisite()):

      $blog = get_blog_details(get_current_blog_id(), true);

      $home_url = rtrim(rtrim($blog->domain, '/') . $blog->path, '/');

      if (isset($parse_url['host'])) :
        $check_url = rtrim($parse_url['host'] . (isset($parse_url['path']) ? $parse_url['path'] : ''), '/');
        $check_url = substr($check_url, 0, strlen($home_url));
      else:
        $check_url = '';
      endif;

      return $check_url === $home_url ? true : false;

    else:

      $parse_home = parse_url(get_option('home', ''));

      if (isset($parse_url['host'])) :
        $check_url = substr($parse_url['host'], 0, strlen($parse_home['host']));
      else:
        $check_url = '';
      endif;

      return $check_url === $parse_home['host'] ? true : false;

    endif;

  }

  public static function is_self_referer($referer_url){

    $home_url = '';

    if (empty($referer_url)) :
      return false;
    endif;

    if (is_multisite()) :

      $blog = get_blog_details(get_current_blog_id(), true);

      $home_url = rtrim($blog->domain . $blog->path, '/');

    else :

      $parse_home = parse_url(get_option('home', ''));

      if (isset($parse_home['host'])) :
        $home_url = rtrim($parse_home['host'], '/');
      endif;

    endif;

    $check_url = '';
    $parse_referer = parse_url($referer_url);

    if (isset($parse_referer['host'])) :
      $check_url = $parse_referer['host'] . (isset($parse_referer['path']) ? $parse_referer['path'] : '');
      $check_url = rtrim($check_url, '/');
    endif;

    $check_url = substr($check_url, 0, strlen($home_url));

    return $check_url === $home_url ? true : false;
  }

  /*
  * @since 2.0.0
  */
  public static function get_user_synced_session($user_id = 0){

    if (empty($user_id) || $user_id < 0) :
      $user_id = 0;
    endif;

    $cookie_consent = self::get_cookie_consent_value();

    AFL_WC_UTM_WORDPRESS_USER::reset_active_session_if_expired($user_id);

    if ($cookie_consent === 'deny') :

      $active_session = self::get_meta_whitelist('active');

    else:

      $active_session = AFL_WC_UTM_WORDPRESS_USER::get_active_session($user_id);

      //get cookie values from browser
      $browser_user_cookies = AFL_WC_UTM_SERVICE::get_user_browser_cookies($user_id);

      AFL_WC_UTM_SERVICE::sync_cookie_expiry($active_session, $browser_user_cookies);
      AFL_WC_UTM_SERVICE::sync_first_session($active_session, $browser_user_cookies);
      AFL_WC_UTM_SERVICE::sync_utm_session($active_session, $browser_user_cookies);
      AFL_WC_UTM_SERVICE::sync_click_identifier_session('gclid', $active_session, $browser_user_cookies);
      AFL_WC_UTM_SERVICE::sync_click_identifier_session('fbclid', $active_session, $browser_user_cookies);
      AFL_WC_UTM_SERVICE::sync_click_identifier_session('msclkid', $active_session, $browser_user_cookies);

    endif;

    //get latest cookie consent
    if (isset($active_session['cookie_consent']['value'])) :
      $active_session['cookie_consent']['value'] = $cookie_consent;
    endif;

    //sanitize
    foreach ($active_session as $key => $row) :

      if (isset($row['value'])) :

        if (isset($row['type'])) :
          $active_session[$key]['value'] = AFL_WC_UTM_UTIL::sanitize_meta_value_by_type($active_session[$key]['value'], $active_session[$key]['type']);
        else:
          $active_session[$key]['value'] = AFL_WC_UTM_UTIL::sanitize_meta_value_by_type($active_session[$key]['value']);
        endif;

      else:

        $active_session[$key]['value'] = '';
        
      endif;

    endforeach;

    return $active_session;
  }

  /*
  * @since 2.0.0
  */
  private static function sync_first_session(&$active_session, &$browser_user_cookies){

    //error recovery
    if (empty($active_session['sess_visit']['value']) || empty($active_session['sess_landing']['value'])):
      $active_session['sess_visit']['value'] = '';
      $active_session['sess_visit_date_local']['value'] = '';
      $active_session['sess_visit_date_utc']['value'] = '';
      $active_session['sess_landing']['value'] = '';
      $active_session['sess_landing_clean']['value'] = '';
      $active_session['sess_referer']['value'] = '';
      $active_session['sess_referer_clean']['value'] = '';
    endif;

    if (empty($browser_user_cookies['sess_visit']['value']) || empty($browser_user_cookies['sess_landing']['value'])):
      $browser_user_cookies['sess_visit']['value'] = '';
      $browser_user_cookies['sess_landing']['value'] = '';
      $browser_user_cookies['sess_referer']['value'] = '';
    endif;

    //sync
    if (
      (empty($active_session['sess_visit']['value']) || empty($active_session['sess_landing']['value']))
      || ($active_session['sess_visit']['value'] > 0 && $browser_user_cookies['sess_visit']['value'] > 0 && $browser_user_cookies['sess_visit']['value'] < $active_session['sess_visit']['value'])
    ) :

      $active_session['sess_visit']['value'] = $browser_user_cookies['sess_visit']['value'];
      $active_session['sess_visit_date_local']['value'] = AFL_WC_UTM_UTIL::timestamp_to_local_date_database($browser_user_cookies['sess_visit']['value']);
      $active_session['sess_visit_date_utc']['value'] = AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($browser_user_cookies['sess_visit']['value']);

      $active_session['sess_landing']['value'] = $browser_user_cookies['sess_landing']['value'];
      $active_session['sess_landing_clean']['value'] = AFL_WC_UTM_UTIL::clean_url($browser_user_cookies['sess_landing']['value']);

      $active_session['sess_referer']['value'] = $browser_user_cookies['sess_referer']['value'];
      $active_session['sess_referer_clean']['value'] = AFL_WC_UTM_UTIL::clean_url($browser_user_cookies['sess_referer']['value']);

    endif;

    if (isset($active_session['sess_ga'])) :
      $active_session['sess_ga']['value'] = $browser_user_cookies['sess_ga']['value'];
    endif;

  }

  /*
  * @since 2.0.0
  */
  private static function sync_utm_session(&$active_session, &$browser_user_cookies){

    $sort = array();

    //active
    if ($active_session['utm_1st_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($active_session['utm_1st_url']['value'], 'utm_source')) {
      $sort[$active_session['utm_1st_visit']['value']] = array('timestamp' => $active_session['utm_1st_visit']['value'], 'url' => $active_session['utm_1st_url']['value']);
    }

    if ($active_session['utm_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($active_session['utm_url']['value'], 'utm_source')) {
      $sort[$active_session['utm_visit']['value']] = array('timestamp' => $active_session['utm_visit']['value'], 'url' => $active_session['utm_url']['value']);
    }

    if ($active_session['gclid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($active_session['gclid_url']['value'], 'utm_source')) {
      $sort[$active_session['gclid_visit']['value']] = array('timestamp' => $active_session['gclid_visit']['value'], 'url' => $active_session['gclid_url']['value']);
    }

    if ($active_session['fbclid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($active_session['fbclid_url']['value'], 'utm_source')) {
      $sort[$active_session['fbclid_visit']['value']] = array('timestamp' => $active_session['fbclid_visit']['value'], 'url' => $active_session['fbclid_url']['value']);
    }

    if ($active_session['msclkid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($active_session['msclkid_url']['value'], 'utm_source')) {
      $sort[$active_session['msclkid_visit']['value']] = array('timestamp' => $active_session['msclkid_visit']['value'], 'url' => $active_session['msclkid_url']['value']);
    }

    //browser
    if ($browser_user_cookies['utm_1st_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies['utm_1st_url']['value'], 'utm_source')) {
      $sort[$browser_user_cookies['utm_1st_visit']['value']] = array('timestamp' => $browser_user_cookies['utm_1st_visit']['value'], 'url' => $browser_user_cookies['utm_1st_url']['value']);
    }

    if ($browser_user_cookies['utm_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies['utm_url']['value'], 'utm_source')) {
      $sort[$browser_user_cookies['utm_visit']['value']] = array('timestamp' => $browser_user_cookies['utm_visit']['value'], 'url' => $browser_user_cookies['utm_url']['value']);
    }

    if ($browser_user_cookies['gclid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies['gclid_url']['value'], 'utm_source')) {
      $sort[$browser_user_cookies['gclid_visit']['value']] = array('timestamp' => $browser_user_cookies['gclid_visit']['value'], 'url' => $browser_user_cookies['gclid_url']['value']);
    }

    if ($browser_user_cookies['fbclid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies['fbclid_url']['value'], 'utm_source')) {
      $sort[$browser_user_cookies['fbclid_visit']['value']] = array('timestamp' => $browser_user_cookies['fbclid_visit']['value'], 'url' => $browser_user_cookies['fbclid_url']['value']);
    }

    if ($browser_user_cookies['msclkid_visit']['value'] > 0 && AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies['msclkid_url']['value'], 'utm_source')) {
      $sort[$browser_user_cookies['msclkid_visit']['value']] = array('timestamp' => $browser_user_cookies['msclkid_visit']['value'], 'url' => $browser_user_cookies['msclkid_url']['value']);
    }

    if (!empty($sort)) :
      ksort($sort, SORT_NUMERIC);
      $first = reset($sort);

      //first touch
      $active_session['utm_1st_visit']['value'] = $first['timestamp'];
      $active_session['utm_1st_visit_date_local']['value'] = AFL_WC_UTM_UTIL::timestamp_to_local_date_database($first['timestamp']);
      $active_session['utm_1st_visit_date_utc']['value'] = AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($first['timestamp']);

      $active_session['utm_1st_url']['value'] = $first['url'];
      $active_session['utm_1st_url_clean']['value'] = AFL_WC_UTM_UTIL::clean_url($first['url']);

      //last touch
      $active_session['utm_visit']['value'] = $active_session['utm_1st_visit']['value'];
      $active_session['utm_visit_date_local']['value'] = $active_session['utm_1st_visit_date_local']['value'];
      $active_session['utm_visit_date_utc']['value'] = $active_session['utm_1st_visit_date_utc']['value'];

      $active_session['utm_url']['value'] = $active_session['utm_1st_url']['value'];
      $active_session['utm_url_clean']['value'] = $active_session['utm_1st_url_clean']['value'];

      //last touch
      if (count($sort) > 1 ) :
        $end = end($sort);

        $cookie_last_touch_window_seconds = self::get_site_settings('cookie_last_touch_window') * 60;

        if (
          ($end['timestamp'] - $first['timestamp']) > $cookie_last_touch_window_seconds
          || $first['url'] != $end['url']
        ) :
          $active_session['utm_visit']['value'] = $end['timestamp'];
          $active_session['utm_visit_date_local']['value'] = AFL_WC_UTM_UTIL::timestamp_to_local_date_database($end['timestamp']);
          $active_session['utm_visit_date_utc']['value'] = AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($end['timestamp']);

          $active_session['utm_url']['value'] = $end['url'];
          $active_session['utm_url_clean']['value'] = AFL_WC_UTM_UTIL::clean_url($end['url']);
        endif;

      endif;

    else:

      $active_session['utm_1st_visit']['value'] = '';
      $active_session['utm_1st_visit_date_local']['value'] = '';
      $active_session['utm_1st_visit_date_utc']['value'] = '';

      $active_session['utm_1st_url']['value'] = '';
      $active_session['utm_1st_url_clean']['value'] = '';

      $active_session['utm_visit']['value'] = '';
      $active_session['utm_visit_date_local']['value'] = '';
      $active_session['utm_visit_date_utc']['value'] = '';

      $active_session['utm_url']['value'] = '';
      $active_session['utm_url_clean']['value'] = '';

    endif;

    //save utm parameters into individual meta
    //first touch utm
    if (!empty($active_session['utm_1st_url']['value'])) :

      $utm_1st_url_query = AFL_WC_UTM_UTIL::get_url_query($active_session['utm_1st_url']['value'], false);

      foreach (AFL_WC_UTM_SERVICE::$utm_whitelist as $utm_parameter) :
        $active_session[$utm_parameter . '_1st']['value'] = isset($utm_1st_url_query[$utm_parameter]) ? $utm_1st_url_query[$utm_parameter] : '';
      endforeach;

    else:

      //reset
      foreach (AFL_WC_UTM_SERVICE::$utm_whitelist as $utm_parameter) :
        $active_session[$utm_parameter . '_1st']['value'] = '';
      endforeach;

    endif;

    //save utm parameters into individual meta
    //last touch utm
    if (!empty($active_session['utm_url']['value'])) :

      $utm_url_query = AFL_WC_UTM_UTIL::get_url_query($active_session['utm_url']['value'], false);

      foreach (AFL_WC_UTM_SERVICE::$utm_whitelist as $utm_parameter) :
        $active_session[$utm_parameter]['value'] = isset($utm_url_query[$utm_parameter]) ? $utm_url_query[$utm_parameter] : '';
      endforeach;

    else:

      //reset
      foreach (AFL_WC_UTM_SERVICE::$utm_whitelist as $utm_parameter) :
        $active_session[$utm_parameter]['value'] = '';
      endforeach;

    endif;
  }

  /*
  * @since 2.3.0
  */
  private static function sync_click_identifier_session($click_identifier, &$active_session, &$browser_user_cookies){

    //error recovery
    if (empty($active_session[$click_identifier . '_visit']['value']) || empty($active_session[$click_identifier . '_url']['value'])):
      $active_session[$click_identifier . '_visit']['value'] = '';
      $active_session[$click_identifier . '_visit_date_local']['value'] = '';
      $active_session[$click_identifier . '_visit_date_utc']['value'] = '';
      $active_session[$click_identifier . '_url']['value'] = '';
      $active_session[$click_identifier . '_url_clean']['value'] = '';
      $active_session[$click_identifier . '_value']['value'] = '';
    endif;

    if (empty($browser_user_cookies[$click_identifier . '_visit']['value']) || !AFL_WC_UTM_UTIL::has_url_query($browser_user_cookies[$click_identifier . '_url']['value'], $click_identifier)):
      $browser_user_cookies[$click_identifier . '_visit']['value'] = '';
      $browser_user_cookies[$click_identifier . '_url']['value'] = '';
    endif;

    //sync
    if (
      (empty($active_session[$click_identifier . '_visit']['value']) || empty($active_session[$click_identifier . '_url']['value']))
      || ($active_session[$click_identifier . '_visit']['value'] > 0 && $browser_user_cookies[$click_identifier . '_visit']['value'] > 0 && $browser_user_cookies[$click_identifier . '_visit']['value'] > $active_session[$click_identifier . '_visit']['value'])
    ) :

      $active_session[$click_identifier . '_visit']['value'] = $browser_user_cookies[$click_identifier . '_visit']['value'];
      $active_session[$click_identifier . '_visit_date_local']['value'] = AFL_WC_UTM_UTIL::timestamp_to_local_date_database($browser_user_cookies[$click_identifier . '_visit']['value']);
      $active_session[$click_identifier . '_visit_date_utc']['value'] = AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($browser_user_cookies[$click_identifier . '_visit']['value']);

      $active_session[$click_identifier . '_url']['value'] = $browser_user_cookies[$click_identifier . '_url']['value'];
      $active_session[$click_identifier . '_url_clean']['value'] = AFL_WC_UTM_UTIL::clean_url($browser_user_cookies[$click_identifier . '_url']['value']);

      $active_session[$click_identifier . '_value']['value'] = AFL_WC_UTM_UTIL::get_url_query_by_parameter($browser_user_cookies[$click_identifier . '_url']['value'], $click_identifier);
    endif;

  }

  /*
  * @since  2.0.0
  */
  private static function get_user_browser_cookies($user_id = 0){

    $time = time();
    $cookie_whitelist = AFL_WC_UTM_SERVICE::get_cookie_whitelist();

    if ($user_id > 0 && is_user_logged_in() && $user_id !== get_current_user_id()) :
      return $cookie_whitelist;
    endif;

    foreach ($cookie_whitelist as $cookie_key => &$cookie_row) :

      $cookie_name = !empty($cookie_row['cookie_name']) ? $cookie_row['cookie_name'] : AFL_WC_UTM_SERVICE::get_cookie_name($cookie_key);

      $meta_value = '';

      if (isset($_COOKIE[$cookie_name])) :
        $meta_value = wp_unslash($_COOKIE[$cookie_name]);
      endif;

      if ($cookie_row['type'] === 'url') :

        $meta_value = AFL_WC_UTM_UTIL::sanitize_url($meta_value);

        if (!empty($meta_value)) :
          //check verified domain
          if (isset($cookie_row['is_own_url']) && $cookie_row['is_own_url'] === true) :

            if (AFL_WC_UTM_SERVICE::is_verified_domain($meta_value)) :
              $cookie_row['value'] = $meta_value;
            endif;

          else:

            //check if not self referer, then set
            if ($cookie_key === 'sess_referer' && !AFL_WC_UTM_SERVICE::is_self_referer($meta_value)) :
              $cookie_row['value'] = $meta_value;
            endif;

          endif;
        endif;

      elseif ($cookie_row['type'] === 'timestamp') :

        if (is_numeric($meta_value)) :
          $cookie_row['value'] = intval($meta_value);

          if ($cookie_row['value'] <= 0):
            $cookie_row['value'] = $time;
          endif;
        else:
          $cookie_row['value'] = $time;
        endif;

      elseif ($cookie_row['type'] === 'integer') :

        if (is_numeric($meta_value)) :
          $cookie_row['value'] = intval($meta_value);
        else:
          $cookie_row['value'] = '';
        endif;

      else :

        $cookie_row['value'] = sanitize_text_field($meta_value);

      endif;

    endforeach;

    return $cookie_whitelist;
  }

  /*
  * @deprecated
  * @since 2.0.0
  */
  private static function sync_gclid_session(&$active_session, &$browser_user_cookies){
    _deprecated_function('AFL_WC_UTM_SERVICE::sync_gclid_session', '2.3.0', 'AFL_WC_UTM_SERVICE::sync_click_identifier_session');

    return self::sync_click_identifier_session('gclid', $active_session, $browser_user_cookies);
  }

  /*
  * @deprecated
  * @since 2.0.0
  */
  private static function sync_fbclid_session(&$active_session, &$browser_user_cookies){
    _deprecated_function('AFL_WC_UTM_SERVICE::sync_fbclid_session', '2.3.0', 'AFL_WC_UTM_SERVICE::sync_click_identifier_session');

    return self::sync_click_identifier_session('fbclid', $active_session, $browser_user_cookies);
  }

  /*
  * @deprecated
  * @since  2.0.0
  */
  public static function rewrite_cookies($event = 'default', $user_id = null){

    _deprecated_function('AFL_WC_UTM_SERVICE::rewrite_cookies', '2.4.0', 'AFL_WC_UTM_SERVICE::set_cookies');

    AFL_WC_UTM_WORDPRESS_USER::reset_active_session_if_expired($user_id);

    $cookie_expiry = AFL_WC_UTM_SERVICE::determine_cookie_expiry_time($event, $user_id);
    $domain_info = AFL_WC_UTM_SERVICE::get_domain_info();

    //get
    $user_synced_session = AFL_WC_UTM_SERVICE::get_user_synced_session($user_id);

    //set new cookie expiry value
    $user_synced_session['cookie_expiry']['value'] = $cookie_expiry['days'];

    //save
    AFL_WC_UTM_WORDPRESS_USER::update_active_session($user_id, $user_synced_session);

    //rewrite cookies
    foreach (AFL_WC_UTM_SERVICE::get_cookie_whitelist() as $cookie_key => $cookie) :

      if (!empty($cookie['is_cookie']) && !empty($cookie['rewrite_cookie']) && isset($user_synced_session[$cookie_key]['value'])) :

        if ($user_synced_session[$cookie_key]['value'] !== ''
          || ($user_synced_session[$cookie_key]['value'] === '' && isset($_COOKIE[AFL_WC_UTM_SERVICE::get_cookie_name($cookie_key)]))
        ) :
          setcookie(
            AFL_WC_UTM_SERVICE::get_cookie_name($cookie_key),
            $user_synced_session[$cookie_key]['value'],
            $cookie_expiry['expires_in_seconds'],
            $domain_info['path'],
            $domain_info['domain'],
            true
          );
        endif;

      endif;

    endforeach;

  }

  /*
  * @deprecated
  * @since  2.0.0
  */
  private static function determine_cookie_expiry_time($event = 'default', $user_id = null){

    _deprecated_function('AFL_WC_UTM_SERVICE::determine_cookie_expiry_time', '2.4.0', 'AFL_WC_UTM_SERVICE::prepare_cookie_expiry_details');

    //set cookie expiry by event
    if (!empty($event['cookie_expiry'])) :

      $cookie_expiry = array(
        'name' => AFL_WC_UTM_SERVICE::get_cookie_name('cookie_expiry'),
        'days' => absint($event['cookie_expiry']),
        'expires_in_seconds' => 0
      );

    else:

      $cookie_expiry = array(
        'name' => AFL_WC_UTM_SERVICE::get_cookie_name('cookie_expiry'),
        'days' => self::get_site_settings('cookie_attribution_window'),
        'expires_in_seconds' => 0
      );

    endif;

    //get number of expiry days from browser cookies
    if (isset($_COOKIE[$cookie_expiry['name']])) :

      if (is_numeric($_COOKIE[$cookie_expiry['name']])) :

        //get from browser
        $browser_cookie_expiry = intval($_COOKIE[$cookie_expiry['name']]);

      else:

        //support version 1.2.5 and below
        switch(strtolower($_COOKIE[$cookie_expiry['name']])):

          case 'short':

            $browser_cookie_expiry = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_SHORT;
            break;

          case 'medium':

            $browser_cookie_expiry = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_MEDIUM;
            break;

          default:

            $browser_cookie_expiry = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_LONG;
            break;

        endswitch;

      endif;

      //overwrite if user browser cookies expires earlier
      if (
        !empty($browser_cookie_expiry)
        && $browser_cookie_expiry > 0
        && $browser_cookie_expiry < $cookie_expiry['days']
      ) :
        $cookie_expiry['days'] = $browser_cookie_expiry;
      endif;

      //overwrite if user recent cookies expires earlier
      $active_cookie_expiry = $user_id ? absint(AFL_WC_UTM_WORDPRESS_USER::get_active_user_option($user_id, 'cookie_expiry')) : null;

      if (
        !empty($active_cookie_expiry)
        && $active_cookie_expiry > 0
        && $active_cookie_expiry < $cookie_expiry['days']
      ) :
        $cookie_expiry['days'] = $active_cookie_expiry;
      endif;

    endif;

    //validate
    if ($cookie_expiry['days'] <= 0) :
      $cookie_expiry['days'] = self::get_site_settings('cookie_attribution_window');
    endif;

    //calculate cookie expiry in seconds
    $cookie_expiry['expires_in_seconds'] = time() + ((int)$cookie_expiry['days'] * 86400);

    return $cookie_expiry;
  }

  /*
  * @since  2.0.6
  */
  public static function get_site_settings($key){

    if (isset(self::$options['site_settings'][$key])) {
      return self::$options['site_settings'][$key];
    } else {
      throw new \Exception('Invalid site setting key');
    }

  }

  /*
  * @since 2.4.0
  */
  public static function sync_cookie_expiry(&$active_session, &$browser_user_cookies){

    $default_cookie_expiry = absint(self::get_site_settings('cookie_attribution_window'));

    if (isset($browser_user_cookies['cookie_expiry']['value']) && !empty($browser_user_cookies['cookie_expiry']['value'])) :

      if (!is_numeric($browser_user_cookies['cookie_expiry']['value'])) :

        //version 1
        switch ($browser_user_cookies['cookie_expiry']['value']) :
          case 'short':

            $browser_user_cookies['cookie_expiry']['value'] = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_SHORT;
            break;

          case 'medium':

            $browser_user_cookies['cookie_expiry']['value'] = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_MEDIUM;
            break;

          default:

            $browser_user_cookies['cookie_expiry']['value'] = AFL_WC_UTM_SETTINGS::COOKIE_EXPIRY_DAYS_LONG;
            break;
        endswitch;

      endif;

      $browser_user_cookies['cookie_expiry']['value'] = absint($browser_user_cookies['cookie_expiry']['value']);

      if ($browser_user_cookies['cookie_expiry']['value'] <= 0 || $browser_user_cookies['cookie_expiry']['value'] > $default_cookie_expiry) :

        $browser_user_cookies['cookie_expiry']['value'] = $default_cookie_expiry;

      endif;

    endif;

    if (isset($active_session['cookie_expiry']['value'])) :

      if (empty($active_session['cookie_expiry']['value'])) :
        $active_session['cookie_expiry']['value'] = $default_cookie_expiry;
      endif;

      if (
        isset($browser_user_cookies['cookie_expiry']['value'])
        && $browser_user_cookies['cookie_expiry']['value'] > 0
        && $browser_user_cookies['cookie_expiry']['value'] < $active_session['cookie_expiry']['value']
        ) :

        //if browser lower value, use browser value
        $active_session['cookie_expiry']['value'] = $browser_user_cookies['cookie_expiry']['value'];

      endif;

      $active_session['cookie_expiry']['value'] = absint($active_session['cookie_expiry']['value']);

      if (empty($active_session['cookie_expiry']['value']) || $active_session['cookie_expiry']['value'] > $default_cookie_expiry) :

        $active_session['cookie_expiry']['value'] = $default_cookie_expiry;

      endif;

    endif;

  }

  /*
  * @since  2.4.0
  */
  public static function set_cookies($meta_whitelist = array()){

    //cookie consent deny
    if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
      self::delete_cookies();
      return false;
    endif;

    $cookie_expiry_in_seconds = 0;

    //calculate cookie expiry in seconds
    if (!empty($meta_whitelist['cookie_expiry']['value']) && $meta_whitelist['cookie_expiry']['value'] > 0) :
      $cookie_expiry_in_seconds = time() + (absint($meta_whitelist['cookie_expiry']['value']) * 86400);
    else:
      $cookie_expiry_in_days = absint(self::get_site_settings('cookie_attribution_window'));
      $cookie_expiry_in_seconds = time() + ($cookie_expiry_in_days * 86400);
    endif;

    $domain_info = self::get_domain_info();

    //set cookies
    foreach (self::get_cookie_whitelist() as $cookie_key => $cookie) :

      if (!empty($cookie['is_cookie']) && !empty($cookie['rewrite_cookie']) && isset($meta_whitelist[$cookie_key]['value'])) :

        if ($meta_whitelist[$cookie_key]['value'] !== ''
          || ($meta_whitelist[$cookie_key]['value'] === '' && isset($_COOKIE[self::get_cookie_name($cookie_key)]))
        ) :

          setcookie(
            self::get_cookie_name($cookie_key),
            $meta_whitelist[$cookie_key]['value'],
            $cookie_expiry_in_seconds,
            $domain_info['path'],
            $domain_info['domain'],
            true
          );

        endif;

      endif;

    endforeach;

  }

  /*
  * @since  2.4.0
  */
  public static function delete_cookies(){

    $domain_info = self::get_domain_info();

    foreach (self::get_cookie_whitelist() as $cookie_key => $cookie) :

      if (
        isset($_COOKIE[self::get_cookie_name($cookie_key)])
        && !empty($cookie['is_cookie'])
        && !empty($cookie['rewrite_cookie'])
      ) :
        setcookie(
          self::get_cookie_name($cookie_key),
          '',
          1,
          $domain_info['path'],
          $domain_info['domain'],
          true
        );
      endif;

    endforeach;

  }

  /**
   * @since 2.4.0
   */
  public static function prepare_conversion_lag($user_synced_session, $date_created_timestamp){

    //calculate conversion lag
    $date_created_timestamp = !empty($date_created_timestamp) ? $date_created_timestamp : time();
    $sess_visit_timestamp = !empty($user_synced_session['sess_visit']['value']) ? $user_synced_session['sess_visit']['value'] : 0;

    if ($date_created_timestamp <= 0 || $sess_visit_timestamp <= 0) :
      return $user_synced_session;
    endif;

    //calculate conversion lag
    $conversion_lag = $date_created_timestamp - $sess_visit_timestamp;

    $user_synced_session['conversion_ts']['value'] = $date_created_timestamp;
    $user_synced_session['conversion_date_local']['value'] = AFL_WC_UTM_UTIL::timestamp_to_local_date_database($date_created_timestamp, 'Y-m-d H:i:s');
    $user_synced_session['conversion_date_utc']['value'] = AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($date_created_timestamp, 'Y-m-d H:i:s');

    $user_synced_session['conversion_lag']['value'] = $conversion_lag;
    $user_synced_session['conversion_lag_human']['value'] = AFL_WC_UTM_UTIL::seconds_to_duration($conversion_lag);

    return $user_synced_session;
  }

  /**
   * @since 2.4.0
   */
  public static function prepare_attribution_data_for_saving($user_synced_session, $scope = 'converted'){

    $attribution = array();

    if (!is_array($user_synced_session)) :
      return $attribution;
    endif;

    //cookie consent deny so clear attribution
    if (isset($user_synced_session['cookie_consent']['value']) && $user_synced_session['cookie_consent']['value'] === 'deny') :

      $attribution['cookie_consent'] = sanitize_text_field($user_synced_session['cookie_consent']['value']);
      return $attribution;

    endif;

    foreach ($user_synced_session as $meta_key => $meta) :

      //check scope
      if (!isset($meta['scope']) || !in_array($scope, (array)$meta['scope'])) :
        continue;
      endif;

      if (isset($meta['value']) && $meta['value'] !== '' && $meta['value'] !== null && $meta['value'] !== false) :
        //save meta
        $attribution[$meta_key] = $meta['value'];
      endif;

    endforeach;

    return $attribution;
  }

  /**
   * @since 2.4.0
   */
  public static function prepare_cookie_expiry_after_conversion($user_synced_session, $conversion_event = array()){

    if (empty($user_synced_session['cookie_expiry']['value'])) :
      return $user_synced_session;
    endif;

    //set cookie expiry by event
    if (!empty($conversion_event['cookie_expiry']) && $conversion_event['cookie_expiry'] > 0) :

      if ($conversion_event['cookie_expiry'] < $user_synced_session['cookie_expiry']['value']) :
        $user_synced_session['cookie_expiry']['value'] = absint($conversion_event['cookie_expiry']);
      endif;

    endif;

    if ($user_synced_session['cookie_expiry']['value'] <= 0) :
      $user_synced_session['cookie_expiry']['value'] = absint(self::get_site_settings('cookie_attribution_window'));
    endif;

    return $user_synced_session;
  }

  /**
   * @since 2.4.0
   */
  public static function trigger_conversion($conversion_event, $user_synced_session, $user_id = 0){

    $user_synced_session = self::prepare_cookie_expiry_after_conversion($user_synced_session, $conversion_event);

    AFL_WC_UTM_WORDPRESS_USER::add_active_conversion($user_id, $conversion_event, $user_synced_session);

    if (is_user_logged_in()) :

      //important to be inner
      if (AFL_WC_UTM_UTIL::is_current_logged_in_user_id($user_id)) :

        AFL_WC_UTM_WORDPRESS_USER::update_active_session($user_id, $user_synced_session);
        self::set_cookies($user_synced_session);

      endif;

    else:

      AFL_WC_UTM_WORDPRESS_USER::update_active_session($user_id, $user_synced_session);
      self::set_cookies($user_synced_session);

    endif;

  }

  /*
  * @since  2.4.0
  */
  public static function has_cookie_consent(){

    if (!function_exists('wp_has_consent')) :
      return true;
    endif;

    $has_consent = true;

    if (!wp_has_consent(self::get_site_settings('cookie_consent_category'))) :
      $has_consent = false;
    endif;

    return $has_consent;
  }

  /*
  * @since  2.4.0
  */
  public static function get_cookie_consent_value(){

    if (self::is_wp_consent_api_installed()) :
      return self::has_cookie_consent() ? 'allow' : 'deny';
    else:
      return 'n/a';
    endif;

  }

  /*
  * @since  2.4.0
  */
  public static function format_cookie_consent_value($has_consent){

    if (self::is_wp_consent_api_installed()) :
      return $has_consent ? 'allow' : 'deny';
    else:
      return 'n/a';
    endif;

  }

  /*
  * @since  2.4.0
  */
  public static function is_wp_consent_api_installed(){

    if (function_exists('wp_has_consent')) :
      return true;
    else:
      return false;
    endif;

  }

  /*
  * @since  2.4.6
  */
  public static function prepare_conversion_type($user_synced_session, $conversion_type){

    if (isset($user_synced_session['conversion_type'])) :
      $user_synced_session['conversion_type']['value'] = !empty($conversion_type) ? $conversion_type : self::DEFAULT_CONVERSION_TYPE;
    endif;

    return $user_synced_session;

  }


}
