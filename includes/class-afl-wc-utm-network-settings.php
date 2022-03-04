<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_NETWORK_SETTINGS
{
  const META_NAME = 'afl_wc_utm_network_settings';

  const COOKIE_EXPIRY_DAYS_SHORT = 7;
  const COOKIE_EXPIRY_DAYS_MEDIUM = 30;
  const COOKIE_EXPIRY_DAYS_LONG = 90;

  const COOKIE_LAST_TOUCH_WINDOW = 30;//minutes

  const ACTION_SAVE = 'afl_wc_utm_network_settings_save';

  public static $default_settings = array(
    'cookie_domain' => '',
    'cookie_attribution_window' => self::COOKIE_EXPIRY_DAYS_LONG,
    'cookie_last_touch_window' => self::COOKIE_LAST_TOUCH_WINDOW,
    'cookie_conversion_account' => self::COOKIE_EXPIRY_DAYS_MEDIUM,
    'cookie_conversion_order' => self::COOKIE_EXPIRY_DAYS_SHORT,
    'cookie_consent_category' => 'statistics',
    'attribution_format' => 'separate',
    'active_attribution' => '1',
    'cookie_renewal' => 'force'
  );

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

  public static function install(){

    add_site_option(self::META_NAME, self::$default_settings);
  }

  public static function get($key = null){

    $settings = get_site_option(self::META_NAME, array());
    $settings = AFL_WC_UTM_UTIL::merge_default($settings, self::$default_settings);

    if (!empty($key)) {
      if (isset($settings[$key])) {
        return $settings[$key];
      } else {
        throw new \Exception(__('Invalid settings key.', AFL_WC_UTM_TEXTDOMAIN));
      }
    } else {
      return $settings;
    }

  }

  public static function save($settings){

    $settings = AFL_WC_UTM_UTIL::merge_default($settings, self::$default_settings);
    $settings = self::validate($settings);

    if (is_wp_error($settings)) :
      return $settings;
    endif;

    foreach ($settings as $key => $value) :
      $settings[$key] = sanitize_text_field($value);
    endforeach;

    update_site_option(self::META_NAME, $settings);

    return true;
  }

  public static function validate($settings){

    if (isset($settings['cookie_domain'])) :

      $has_slash_in_url = strpos($settings['cookie_domain'], '/');

      if ($has_slash_in_url !== false) :
        return new WP_Error('afl_wc_utm_network_settings_invalid_value', __('Cookie domain must not have / or path.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    endif;

    if (isset($settings['cookie_attribution_window'])) :
      $attribution_window = intval($settings['cookie_attribution_window']);

      if ($attribution_window <= 0) :
        return new WP_Error('afl_wc_utm_network_settings_invalid_value', __('Attribution Window must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    endif;

    if (isset($settings['cookie_last_touch_window'])) :
      $last_touch_window = intval($settings['cookie_last_touch_window']);

      if ($last_touch_window <= 0) :
        return new WP_Error('afl_wc_utm_network_settings_invalid_value', __('Last Touch Window must be at least 1 minute.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    endif;

    if (isset($settings['cookie_conversion_account'])) :
      $conversion_account = intval($settings['cookie_conversion_account']);

      if ($conversion_account <= 0) :
        return new WP_Error('afl_wc_utm_network_settings_invalid_value', __('When user registers, reset after must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    endif;

    if (isset($settings['cookie_conversion_order'])) :
      $conversion_order = intval($settings['cookie_conversion_order']);

      if ($conversion_order <= 0) :
        return new WP_Error('afl_wc_utm_network_settings_invalid_value', __('When user placed an order, reset after must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    endif;

    if (isset($settings['cookie_consent_category']) && !in_array($settings['cookie_consent_category'], array('statistics', 'marketing'))) :
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Cookie Consent Category value.', AFL_WC_UTM_TEXTDOMAIN));
    endif;

    if (isset($settings['attribution_format']) && !in_array($settings['attribution_format'], array('separate', 'json'))) :
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Attribution Data value.', AFL_WC_UTM_TEXTDOMAIN));
    endif;

    if (isset($settings['active_attribution']) && !in_array($settings['active_attribution'], array('1', '0'))) :
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Active Attribution value.', AFL_WC_UTM_TEXTDOMAIN));
    endif;

    if (isset($settings['cookie_renewal']) && !in_array($settings['cookie_renewal'], array('force', 'update'))) :
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Server-side Cookies value.', AFL_WC_UTM_TEXTDOMAIN));
    endif;

    return $settings;

  }

  /**
   * @since 2.4.0
   */
  public static function get_site_override_settings($site_settings){

    //override with network settings
    if (is_multisite()) :

      $network_settings = self::get();
      $override = array();

      if (!empty($network_settings['cookie_domain'])) :
        $override['cookie_domain'] = $network_settings['cookie_domain'];
        $override['cookie_attribution_window'] = $network_settings['cookie_attribution_window'];
        $override['cookie_last_touch_window'] = $network_settings['cookie_last_touch_window'];
        $override['cookie_conversion_account'] = $network_settings['cookie_conversion_account'];
        $override['cookie_conversion_order'] = $network_settings['cookie_conversion_order'];
        $override['cookie_consent_category'] = $network_settings['cookie_consent_category'];
      endif;

      $override['attribution_format'] = $network_settings['attribution_format'];
      $override['active_attribution'] = $network_settings['active_attribution'];
      $override['cookie_renewal'] = $network_settings['cookie_renewal'];

      foreach ($override as $tmp_key => $tmp_value) :
        if (isset($site_settings[$tmp_key])) :
          $site_settings[$tmp_key] = $override[$tmp_key];
        endif;
      endforeach;

    endif;

    return $site_settings;
  }

}
