<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_SETTINGS
{
  const META_NAME = 'afl_wc_utm_settings';

  const COOKIE_EXPIRY_DAYS_SHORT = 7;
  const COOKIE_EXPIRY_DAYS_MEDIUM = 30;
  const COOKIE_EXPIRY_DAYS_LONG = 90;

  const COOKIE_LAST_TOUCH_WINDOW = 30;//minutes

  const ACTION_SAVE = 'afl_wc_utm_settings_save';

  public static $default_settings = array(
    'cookie_attribution_window' => self::COOKIE_EXPIRY_DAYS_LONG,
    'cookie_last_touch_window' => self::COOKIE_LAST_TOUCH_WINDOW,
    'cookie_conversion_account' => self::COOKIE_EXPIRY_DAYS_MEDIUM,
    'cookie_conversion_order' => self::COOKIE_EXPIRY_DAYS_SHORT,
    'cookie_domain' => '',
    'cookie_consent_category' => 'statistics',
    'admin_column_conversion_lag' => '1',
    'admin_column_utm_first' => '1',
    'admin_column_utm_last' => '1',
    'admin_column_clid' => '1',
    'admin_column_sess_referer' => '1',
    'export_blank' => '',
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

    add_option(self::META_NAME, self::$default_settings);
  }

  public static function get($key = null){

    $settings = get_option(self::META_NAME, array());
    $settings = AFL_WC_UTM_UTIL::merge_default($settings, self::$default_settings);
    $settings = AFL_WC_UTM_NETWORK_SETTINGS::get_site_override_settings($settings);

    if (!empty($key)) :
      if (isset($settings[$key])) :
        return $settings[$key];
      else:
        throw new \Exception(__('Invalid settings key.', AFL_WC_UTM_TEXTDOMAIN));
      endif;
    else:
      return $settings;
    endif;

  }

  public static function save($settings){

    $settings = self::validate($settings);

    if (is_wp_error($settings)) :
      return $settings;
    endif;

    $settings = AFL_WC_UTM_UTIL::merge_default($settings, self::$default_settings);

    foreach ($settings as $key => $value) :
      $settings[$key] = sanitize_text_field($value);
    endforeach;

    update_option(self::META_NAME, $settings);

    return true;
  }

  public static function validate($settings){

    $permissions = self::get_setting_permissions();

    if (isset($settings['cookie_domain'])) :

      if ($permissions['cookie_domain']) :

        $has_slash_in_url = strpos($settings['cookie_domain'], '/');

        if ($has_slash_in_url !== false) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Cookie domain must not have / or path.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie domain.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_attribution_window'])) :

      if ($permissions['cookies']) :

        $settings['cookie_attribution_window'] = intval($settings['cookie_attribution_window']);

        if ($settings['cookie_attribution_window'] <= 0) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Attribution Window must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_last_touch_window'])) :

      if ($permissions['cookies']) :

        $settings['cookie_last_touch_window'] = intval($settings['cookie_last_touch_window']);

        if ($settings['cookie_last_touch_window'] <= 0) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Last Touch Window must be at least 1 minute.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_conversion_account'])) :

      if ($permissions['cookies']) :

        $settings['cookie_conversion_account'] = intval($settings['cookie_conversion_account']);

        if ($settings['cookie_conversion_account'] <= 0) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('When user registers, reset after must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_conversion_order'])) :

      if ($permissions['cookies']) :

        $settings['cookie_conversion_order'] = intval($settings['cookie_conversion_order']);

        if ($settings['cookie_conversion_order'] <= 0) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('When user placed an order, reset after must be at least 1 day.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_consent_category'])) :

      if ($permissions['cookies']) :

        if (!in_array($settings['cookie_consent_category'], array('statistics', 'marketing'))):
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Cookie Consent Category value.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the cookie settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['admin_column_conversion_lag'])) :

      if ($permissions['admin_table_integration']) :

        if (!in_array($settings['admin_column_conversion_lag'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Admin Table Integration settings.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the admin table integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['admin_column_utm_first'])) :

      if ($permissions['admin_table_integration']) :

        if (!in_array($settings['admin_column_utm_first'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Admin Table Integration settings.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the admin table integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['admin_column_utm_last'])) :

      if ($permissions['admin_table_integration']) :

        if (!in_array($settings['admin_column_utm_last'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Admin Table Integration settings.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the admin table integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['admin_column_clid'])) :

      if ($permissions['admin_table_integration']) :

        if (!in_array($settings['admin_column_clid'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Admin Table Integration settings.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the admin table integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['admin_column_sess_referer'])) :

      if ($permissions['admin_table_integration']) :

        if (!in_array($settings['admin_column_sess_referer'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Admin Table Integration settings.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the admin table integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['export_blank'])) :

      if (!$permissions['export_integration']) :
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the export integration settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['attribution_format'])) :

      if ($permissions['site_performance']) :

        if (!in_array($settings['attribution_format'], array('separate', 'json'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Attribution Data value.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the site performance settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['active_attribution'])) :

      if ($permissions['site_performance']) :

        if (!in_array($settings['active_attribution'], array('1', '0'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Active Attribution value.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the site performance settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    if (isset($settings['cookie_renewal'])) :

      if ($permissions['site_performance']) :

        if (!in_array($settings['cookie_renewal'], array('force', 'update'))) :
          return new WP_Error('afl_wc_utm_settings_invalid_value', __('Invalid Server-side Cookies value.', AFL_WC_UTM_TEXTDOMAIN));
        endif;

      else:
        return new WP_Error('afl_wc_utm_settings_invalid_value', __('You do not have permission to set the site performance settings.', AFL_WC_UTM_TEXTDOMAIN));
      endif;

    endif;

    return $settings;

  }

  /**
   * @since 2.4.0
   */
  public static function get_setting_permissions(){

    $permissions = array(
      'cookies' => true,
      'cookie_domain' => true,
      'admin_table_integration' => true,
      'export_integration' => true,
      'site_performance' => true
    );

    if (is_multisite()) :

      $permissions['site_performance'] = false;

      //get network settings
      $network_cookie_domain = AFL_WC_UTM_NETWORK_SETTINGS::get('cookie_domain');

      if (!empty($network_cookie_domain)) :
        $permissions['cookies'] = false;
        $permissions['cookie_domain'] = false;
      endif;

      //not super admin
      if (!is_super_admin()) :
        $permissions['cookie_domain'] = false;
      endif;

    endif;

    return $permissions;

  }

}
