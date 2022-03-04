<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_WORDPRESS_USER_MIGRATE
{

  private static $highest_version = '2.3.0';

  private static $migrations = array(
    '2.0.0' => 'version_2_0_0',
    '2.3.0' => 'version_2_3_0',
  );

  /**
   * @since 2.3.0
  */
  public static function migrate($user_id){

    if (empty($user_id)) :
      return;
    endif;

    $user_version = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'version');

    if (empty($user_version)) :
      $user_version = '0.0.0';
    endif;

    if (version_compare($user_version, self::$highest_version, '<')) :
      foreach (self::$migrations as $migration_version => $migration_function) :

        $user_version = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'version');

        if (version_compare($user_version, $migration_version, '<')) :

          if (method_exists(__CLASS__, $migration_function)) :
            self::$migration_function($user_id);
          endif;
        endif;

      endforeach;
    endif;

  }

  /**
   * @since 2.0.0
  */
  public static function version_2_0_0($user_id){

    try {

      AFL_WC_UTM_WORDPRESS_USER::calculate_conversion_lag($user_id);

      //populate date time
      foreach (array('sess_visit', 'utm_1st_visit', 'utm_visit', 'fbclid_visit', 'gclid_visit') as $meta_key) :

        $ts = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key);

        if (empty($ts)) :
          continue;
        endif;

        $date_utc = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key . '_date_utc');
        $date_local = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key . '_date_local');

        if (empty($date_utc)) :
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, $meta_key . '_date_utc', sanitize_text_field(AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($ts)));
        endif;

        if (empty($date_local)) :
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, $meta_key . '_date_local', sanitize_text_field(AFL_WC_UTM_UTIL::timestamp_to_local_date_database($ts)));
        endif;

      endforeach;

      //populate clean url
      foreach (array('sess_landing', 'sess_referer', 'utm_1st_url', 'utm_url', 'fbclid_url', 'gclid_url') as $meta_key) :

        $url = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key);

        if (empty($url)) :
          continue;
        endif;

        $url_clean = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key . '_clean');

        if (empty($url_clean)) :
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, $meta_key . '_clean', esc_url_raw(AFL_WC_UTM_UTIL::clean_url($url)));
        endif;

      endforeach;

      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'version', '2.0.0');

    } catch (\Exception $e) {

    }

  }

  /**
   * @since 2.3.0
  */
  private static function version_2_3_0($user_id){

    try {

      //populate last touch UTM if empty
      $first_touch_utm_url = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_1st_url');
      $last_touch_utm_url = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_url');

      if (!empty($first_touch_utm_url) && empty($last_touch_utm_url)) :
        // paste from first

        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_url', esc_url_raw($first_touch_utm_url));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_url_clean', esc_url_raw(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_1st_url_clean')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_visit', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_1st_visit')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_visit_date_utc', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_1st_visit_date_utc')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_visit_date_local', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_1st_visit_date_local')));

        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_source', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_source_1st')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_medium', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_medium_1st')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_campaign', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_campaign_1st')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_term', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_term_1st')));
        AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'utm_content', sanitize_text_field(AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, 'utm_content_1st')));

      endif;

    } catch (\Exception $e) {

    }

    try {

      //populate click identifier value
      foreach (array('gclid', 'fbclid') as $meta_key) :

        $click_identifier_value = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key . '_value');

        if (empty($click_identifier_value) && $click_identifier_value !== 0) :

          $click_identifier_url = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key . '_url');

          if (!empty($click_identifier_url)) :
            $extracted_clid = AFL_WC_UTM_UTIL::get_url_query_by_parameter($click_identifier_url, $meta_key);

            AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, $meta_key . '_value', sanitize_text_field($extracted_clid));
          endif;
        endif;

      endforeach;

    } catch (\Exception $e) {

    }

    try {

      //populate msclkid
      $meta_list = array(
        'sess_landing' => 'sess_visit',
        'utm_1st_url' => 'utm_1st_visit',
        'utm_url' => 'utm_visit',
        'gclid_url' => 'gclid_visit',
        'fbclid_url' => 'fbclid_visit',
      );

      $msclkid_list = array();

      foreach ($meta_list as $meta_key_url => $meta_key_visit) :

        $tmp_url = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key_url);
        $tmp_timestamp = AFL_WC_UTM_WORDPRESS_USER::get_user_option($user_id, $meta_key_visit);

        if (empty($tmp_url)) :
          continue;
        endif;

        //sanitize
        $msclkid_value = AFL_WC_UTM_UTIL::get_url_query_by_parameter($tmp_url, 'msclkid');

        if (!empty($msclkid_value) || $msclkid_value === 0) :

          $msclkid_list[$tmp_timestamp] = array(
            'mscklid_url' => $tmp_url,
            'mscklid_visit' => $tmp_timestamp,
            'mscklid_value' => $msclkid_value
          );
        endif;

      endforeach;

      if (count($msclkid_list)) :

        //sort
        ksort($msclkid_list, SORT_NUMERIC);

        $last_touch_msclkid = end($msclkid_list);

        if (!empty($last_touch_msclkid)) :
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_url', esc_url_raw($last_touch_msclkid['mscklid_url']));
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_url_clean', esc_url_raw(AFL_WC_UTM_UTIL::clean_url($last_touch_msclkid['mscklid_url'])));
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_visit', sanitize_text_field($last_touch_msclkid['mscklid_visit']));
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_visit_date_local', sanitize_text_field(AFL_WC_UTM_UTIL::timestamp_to_local_date_database($last_touch_msclkid['mscklid_visit'])));
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_visit_date_utc', sanitize_text_field(AFL_WC_UTM_UTIL::timestamp_to_utc_date_database($last_touch_msclkid['mscklid_visit'])));
          AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'msclkid_value', sanitize_text_field($last_touch_msclkid['mscklid_value']));
        endif;

      endif;

      AFL_WC_UTM_WORDPRESS_USER::update_user_option($user_id, 'version', '2.3.0');

    } catch (\Exception $e) {

    }

  }

}
