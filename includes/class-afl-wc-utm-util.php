<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_UTIL
{

  public static function get_instance(){
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct(){

  }

  public static function dd($var){
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
  }

  public static function merge_default($var, $default){

    foreach ($default as $key => $value) :
      if (isset($var[$key])) :
        if (is_array($value)) :
          $default[$key] = self::merge_default($var[$key], $value);
        else:
          $default[$key] = $var[$key];
        endif;
      endif;
    endforeach;

    return $default;
  }

  public static function merge_default_without_blank($var, $default){

    foreach ($default as $key => $value) :
      if (isset($var[$key])) :
        if (is_array($value)) :
          $default[$key] = self::merge_default($var[$key], $value);
        else:
          $default[$key] = $var[$key];
        endif;
      endif;

      if ($default[$key] === '') :
        unset($default[$key]);
      endif;
    endforeach;

    return $default;
  }

  public static function timestamp_to_utc_date_database($timestamp, $format = 'Y-m-d H:i:s'){
    return AFL_WC_UTM_UTIL::date_format($timestamp, 'U', 'UTC', $format, 'UTC');
  }

  public static function timestamp_to_utc_date_human($timestamp, $format = 'M j, Y g:i a'){
    return AFL_WC_UTM_UTIL::date_format($timestamp, 'U', 'UTC', $format, 'UTC');
  }

  public static function timestamp_to_local_date_database($timestamp, $format = 'Y-m-d H:i:s'){
    return AFL_WC_UTM_UTIL::date_format($timestamp, 'U', 'UTC', $format, 'local');
  }

  public static function timestamp_to_local_date_human($timestamp, $format = 'M j, Y g:i a'){
    return AFL_WC_UTM_UTIL::date_format($timestamp, 'U', 'UTC', $format, 'local');
  }

  public static function utc_date_database_to_timestamp($date_string){

    return AFL_WC_UTM_UTIL::date_format($date_string, 'Y-m-d H:i:s', 'UTC', 'U', 'UTC');
  }

  public static function local_date_database_to_timestamp($date_string, $current_timezone = 'UTC'){

    return AFL_WC_UTM_UTIL::date_format($date_string, 'Y-m-d H:i:s', 'local', 'U', 'UTC');
  }

  public static function utc_date_database_to_local_date_database($date_string){

    return AFL_WC_UTM_UTIL::date_format($date_string, 'Y-m-d H:i:s', 'UTC', 'U', 'local');
  }

  public static function date_format($date_string, $current_format = 'Y-m-d H:i:s', $current_timezone = 'UTC', $converted_format = 'Y-m-d H:i:s', $converted_timezone = 'UTC'){

    $output = '';

    try {

      $current_timezone = $current_timezone === 'local' ? AFL_WC_UTM_UTIL::wp_timezone() : new DateTimeZone($current_timezone);
      $converted_timezone = $converted_timezone === 'local' ? AFL_WC_UTM_UTIL::wp_timezone() : new DateTimeZone($converted_timezone);

      $dt = DateTime::createFromFormat($current_format, $date_string, $current_timezone);

      if (!empty($dt)) :
        $dt->setTimezone($converted_timezone);
        $output = $dt->format($converted_format);
      endif;

    } catch (\Exception $e) {
      $output = '';
    }

    return $output;
  }

  public static function local_date_format($date_string, $format = 'Y-m-d H:i:s'){

    try {

      if (is_numeric($date_string)) :
        $date_string = '@' . intval($date_string);
      endif;

      $dt = new DateTime($date_string, new DateTimeZone('UTC'));
      $dt->setTimezone(self::wp_timezone());

      return $dt->format($format);

    } catch (\Exception $e) {
      return null;
    }

  }

  public static function utc_date_format($date_string, $format = 'Y-m-d H:i:s'){

    try {

      if (is_numeric($date_string)) :
        $date_string = '@' . intval($date_string);
      endif;

      $dt = new DateTime($date_string, self::wp_timezone());
      $dt->setTimezone(new DateTimeZone('UTC'));

      return $dt->format($format);

    } catch (\Exception $e) {
      return null;
    }

  }

  public static function date_database_to_human($date_string){

    try {

      if (is_numeric($date_string)) :
        $date_string = '@' . intval($date_string);
      endif;

      $dt = new DateTime($date_string, new DateTimeZone('UTC'));

      return $dt->format('M j, Y g:i a');

    } catch (\Exception $e) {
      return null;
    }

  }

  public static function date_ago($date_string_1, $date_string_2){

    $date_ago = '';

    if (empty($date_string_1) || empty($date_string_2)) {
      return null;
    }

    if (is_numeric($date_string_1)) {
      $date_string_1 = '@' . $date_string_1;
    }

    if (is_numeric($date_string_2)) {
      $date_string_2 = '@' . $date_string_2;
    }

    try {
      $dt_1 = new DateTime($date_string_1);
      $dt_2 = new DateTime($date_string_2);

      $dateinterval = $dt_1->diff($dt_2);

      $date_ago = self::interval_to_duration($dateinterval);

    } catch (\Exception $e) {
      $date_ago = null;
    }

    return $date_ago;
  }

  public static function seconds_to_duration($seconds){

    try {
      $dt_1 = new DateTime('@0');
      $dt_2 = new DateTime('@' . absint($seconds));

      $dateinterval = $dt_1->diff($dt_2);

      return AFL_WC_UTM_UTIL::interval_to_duration($dateinterval);
    } catch (\Exception $e) {
      return null;
    }

  }

  public static function interval_to_duration($dateinterval){

    try {
      $duration = '';

      if (!empty($dateinterval->days) && $dateinterval->days > 30) :

        $duration = sprintf(self::plural('about %s day', 'about %s days', $dateinterval->days), $dateinterval->days);

      else:

        $parts = array(
          'd' => 'day',
          'h' => 'hour',
          'i' => 'minute',
          's' => 'second'
        );

        $count_part = 0;

        foreach ($parts as $part_key => $part) :

          switch($part_key):
            case 'd':

              if (!empty($dateinterval->d)) :
                $duration .= sprintf(' %1$s %2$s',
                  $dateinterval->d,
                  self::plural('day', 'days', $dateinterval->d)
                );

                $count_part++;
              endif;

              break;

            case 'h':

              if (!empty($dateinterval->h)) :
                $duration .= sprintf(' %1$s %2$s',
                  $dateinterval->h,
                  self::plural('hour', 'hours', $dateinterval->h)
                );

                $count_part++;
              endif;

              break;

            case 'i':

              if (!empty($dateinterval->i)) :
                $duration .= sprintf(' %1$s %2$s',
                  $dateinterval->i,
                  self::plural('minute', 'minutes', $dateinterval->i)
                );

                $count_part++;
              endif;

              break;

            case 's':

              if (!empty($dateinterval->s)) :
                $duration .= sprintf(' %1$s %2$s',
                  $dateinterval->s,
                  self::plural('second', 'seconds', $dateinterval->s)
                );

                $count_part++;
              endif;

              break;

          endswitch;

          if ($count_part > 1) :
            break;
          endif;

        endforeach;
      endif;

      return trim($duration);

    } catch (\Exception $e) {
      return null;
    }

  }

  public static function wp_timezone(){

    if (function_exists('wp_timezone')) {
      return wp_timezone();
    } else {
      return new DateTimeZone(self::wp_timezone_string());
    }

  }

  public static function wp_timezone_string(){
    $timezone_string = get_option( 'timezone_string' );

  	if ( $timezone_string ) {
  		return $timezone_string;
  	}

  	$offset  = (float) get_option( 'gmt_offset' );
  	$hours   = (int) $offset;
  	$minutes = ( $offset - $hours );

  	$sign      = ( $offset < 0 ) ? '-' : '+';
  	$abs_hour  = abs( $hours );
  	$abs_mins  = abs( $minutes * 60 );
  	$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

  	return $tz_offset;
  }

  public static function get_url_query($url, $decode = true){

    $parse_url = parse_url($url);

    if (!isset($parse_url['query'])) :
      return array();
    endif;

    $url_query = array();
    parse_str($parse_url['query'], $url_query);

    return $url_query;

  }

  public static function get_url_query_by_parameter($url, $parameter_name){

    $parse_url = parse_url($url);

    if (!isset($parse_url['query'])) :
      return '';
    endif;

    $url_query = array();
    parse_str($parse_url['query'], $url_query);

    return isset($url_query[$parameter_name]) ? $url_query[$parameter_name] : '';

  }

  public static function has_url_query($url, $parameter_name){

    $parse_url = parse_url($url);

    if (empty($parse_url['query'])) :
      return false;
    endif;

    parse_str($parse_url['query'], $url_query);

    if (!empty($url_query)) :
      foreach ($url_query as $key => $value) :
        if ($key === $parameter_name) :
          return true;
        endif;
      endforeach;
    endif;

    return false;
  }

  public static function clean_url($url){

    $parse_url = parse_url($url);

    $output = '';

    if (isset($parse_url['scheme'])) {
      $output .= $parse_url['scheme'] . '://';
    }

    if (isset($parse_url['host'])) {
      $output .= $parse_url['host'];
    }

    if (isset($parse_url['path'])) {
      $output .= $parse_url['path'];
    }

    $output = rtrim($output, '/');

    if (!empty($output)) {
      $output .= '/';
    }

    return $output;
  }

  public static function pretty_url_with_break($url){

    if (empty($url)) :
      return $url;
    endif;

    $output = '';
    $parse_url = parse_url($url);

    if (isset($parse_url['path'])) :
      $page_path = AFL_WC_UTM_UTIL::get_url_path($url, true);
      $compare_page_path = AFL_WC_UTM_UTIL::get_url_path($url, false);

      if ($page_path !== '' && $page_path !== $compare_page_path) :
        $output .= 'Page:<br>/' . $page_path . '/<br><br>';
      endif;
    endif;

    $output .= AFL_WC_UTM_UTIL::clean_url($url);

    //queries
    if (isset($parse_url['query'])) :

      $queries = array();
      parse_str($parse_url['query'], $queries);

      if (!empty($queries)) :
        foreach ($queries as $param => $param_value) :
          $output .= '<br>' . $param . '=' . $param_value;
        endforeach;
      endif;

    endif;

    return $output;
  }

  public static function plural($singular, $plural, $count){
    return $count > 1 ? $plural : $singular;
  }

  /**
   * @since 2.4.0
   */
  public static function write_log ( $log )  {
		if (defined('WP_DEBUG') && true === WP_DEBUG) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}

  /**
   * @since 2.4.0
   */
  public static function sanitize_url($url){

    $blacklist_paths = array(
      'wp-content',
      'wp-admin',
      'wp-includes',
      'wp-json',
      'rest_route',
      'admin-ajax.php'
    );

    $blacklist_paths = apply_filters('afl_wc_utm_blacklist_url_paths', $blacklist_paths);

    $decoded_url = urldecode(strtolower($url));

    foreach ($blacklist_paths as $b_path) :
      if (strpos($decoded_url, $b_path) !== false) :
        $url = '';
        break;
      endif;
    endforeach;

    return esc_url_raw($url);
	}

  /**
   * @since 2.4.6
   */
  public static function has_merge_tag($merge_tag){

    if (strpos($merge_tag, '{afl_wc_utm:', 0) === 0 || strpos($merge_tag, 'afl_wc_utm:', 0) === 0) :
      return true;
    endif;

    return false;

  }

  /**
   * @since 2.4.6
   */
  public static function get_merge_tag_value($merge_tag, $user_synced_session){

    $value = $merge_tag;
    $merge_formula = $merge_tag;

    if (strpos($merge_formula, '{afl_wc_utm:', 0) === 0) :
      $merge_formula = ltrim($merge_formula, '{');
      $merge_formula = rtrim($merge_formula, '}');
    endif;

    if (strpos($merge_formula, 'afl_wc_utm:', 0) !== 0) :
      return $value;
    endif;

    //reset
    $value = '';

    //check if filter exists
    $segments = explode('--', $merge_formula, 2);

    if (!isset($segments[0])) :
      return $value;
    endif;

    //get meta key
    $meta_split = explode(':', $segments[0], 2);
    $merge_key = isset($meta_split[1]) ? $meta_split[1] : '';

    if ($merge_key === '') :
      return $value;
    endif;

    if (!isset($user_synced_session[$merge_key]['value'])) :
      return $value;
    endif;

    //check if has filter
    if (isset($segments[1])) :

      //filter exists
      $filter_parts = explode(':', $segments[1], 2);

      if (count($filter_parts) !== 2) :
        return $value;
      endif;

      $filter_name = $filter_parts[0];
      $filter_param = $filter_parts[1];

      switch ($filter_name) :

        case 'extract_param':

          /**
           * To extract custom parameter from URL
           *
           * Example usage:
           * afl_wc_utm:utm_url--extract_param:parameter_name
           *
           * @since 2.4.3
           */
          $value = AFL_WC_UTM_UTIL::get_url_query_by_parameter($user_synced_session[$merge_key]['value'], $filter_param);

          break;

      endswitch;

    elseif (isset($segments[0])) :

      $value = $user_synced_session[$merge_key]['value'];

    endif;

    return $value;

  }

  /**
   * @since 2.4.6
   */
   public static function get_tab_css($tab_name, $active_css, $inactive_css){

      if ((empty($tab_name) && !isset($_GET['tab'])) || (isset($_GET['tab']) && $_GET['tab'] === $tab_name)) :
        return $active_css;
      else:
        return $inactive_css;
      endif;

   }

   /**
    * @since 2.4.6
    */
   public static function is_plugin_installed($name){

     switch ($name) {

       case 'fluentforms':

         return function_exists( 'wpFluentForm' ) ? true : false;

         break;

       case 'gravityforms':

         return method_exists( 'GFForms', 'include_addon_framework' ) ? true : false;

         break;

       case 'woocommerce':

         return class_exists( 'woocommerce' ) ? true : false;

         break;

       default:

         break;
     }

     return false;

   }

   /**
    * @since 2.4.6
    */
   public static function is_current_logged_in_user_id($user_id){

     if ($user_id > 0 && is_user_logged_in()) :

       if ($user_id === get_current_user_id()) :
         return true;
       endif;

     endif;

     return false;

   }

  /**
  * @since 2.4.7
  */
  public static function decode_url_path($url){

   $parse_url = parse_url($url);

   $output = '';

   //scheme
   if (isset($parse_url['scheme'])) :
     $output .= $parse_url['scheme'] . '://';
   else:
     //exit early because not a url
     return '';
   endif;

   //host
   if (isset($parse_url['host'])) :
     $output .= $parse_url['host'];

     //port
     if (isset($parse_url['port'])) :
       $output .= ':' . $parse_url['port'];
     endif;

      $output .= '/';
   endif;

   //path
   if (isset($parse_url['path'])) :

     //split path by slash
     $path = '';
     $split_paths = explode('/', $parse_url['path']);

     foreach ($split_paths as $i_path) :
       if ($i_path !== '') :
         $path .= urldecode($i_path) . '/';
       endif;
     endforeach;

     $path = rtrim($path, '/') . '/';

     $output .= $path;
   endif;

   //query
   if (isset($parse_url['query'])) :
     $output .= '?' . $parse_url['query'];
   endif;

   //fragment
   if (isset($parse_url['fragment'])) :
     $output .= '#' . $parse_url['fragment'];
   endif;

   return $output;
  }

  /**
  * @since 2.4.7
  */
  public static function encode_url_path($url){

   $parse_url = parse_url($url);

   $output = '';

   //scheme
   if (isset($parse_url['scheme'])) :
     $output .= $parse_url['scheme'] . '://';
   else:
     //exit early because not a url
     return '';
   endif;

   //host
   if (isset($parse_url['host'])) :
     $output .= $parse_url['host'];

     //port
     if (isset($parse_url['port'])) :
       $output .= ':' . $parse_url['port'];
     endif;

      $output .= '/';
   endif;

   //path
   if (isset($parse_url['path'])) :

     //split path by slash
     $path = '';
     $split_paths = explode('/', $parse_url['path']);

     foreach ($split_paths as $i_path) :
       if ($i_path !== '') :
         $path .= urlencode($i_path) . '/';
       endif;
     endforeach;

     $path = rtrim($path, '/') . '/';

     $output .= $path;
   endif;

   //query
   if (isset($parse_url['query'])) :
     $output .= '?' . $parse_url['query'];
   endif;

   //fragment
   if (isset($parse_url['fragment'])) :
     $output .= '#' . $parse_url['fragment'];
   endif;

   return $output;
  }

  public static function get_url_path($url, $decode = true){

    $parse_url = parse_url($url);

    $path = '';

    //path
    if (isset($parse_url['path']) && $parse_url['path'] !== '/') :

      if ($decode === true) :

        //split path by slash
        $split_paths = explode('/', $parse_url['path']);

        foreach ($split_paths as $i_path) :
          if ($i_path !== '') :
            $path .= urldecode($i_path) . '/';
          endif;
        endforeach;

      else:

        $path = $parse_url['path'];

      endif;

    endif;

    return trim($path, '/');
  }

  /**
   * @since 2.4.7
   */
  public static function json_encode($string){

    return json_encode($string, JSON_UNESCAPED_UNICODE);

  }

  /**
  * @since 2.4.7
  */
  public static function json_decode($string){

   return json_decode($string, true);

  }

  /**
  * @since 2.4.12
  */
  public static function sanitize_meta_value_by_type($meta_value, $type = ''){
    
    switch ($type) :
     case 'url':

       return self::sanitize_url($meta_value);
       break;

     default:

       return sanitize_text_field($meta_value);
       break;

    endswitch;

    return '';

  }

}
