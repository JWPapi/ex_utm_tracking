<?php defined( 'ABSPATH' ) || exit;

return array(

  'cookie_consent' => array(
    'label' => esc_html__('Cookie Consent', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'email', 'export')
  ),

  'cookie_expiry' => array(
    'label' => esc_html__('Cookie Expiry', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'integer',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),

  'conversion_type' => array(
    'label' => esc_html__('Conversion Type', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'conversion_lag_human' => array(
    'label' => esc_html__('Conversion Lag', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'conversion_lag' => array(
    'label' => esc_html__('Conversion Lag (seconds)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'integer',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'conversion_ts' => array(
    'label' => esc_html__('Conversion Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'conversion_date_utc' => array(
    'label' => esc_html__('Conversion Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'conversion_date_local' => array(
    'label' => esc_html__('Conversion Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'sess_visit' => array(
    'label' => esc_html__('Session Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'sess_visit_date_utc' => array(
    'label' => esc_html__('Session Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'sess_visit_date_local' => array(
    'label' => esc_html__('Session Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'sess_landing' => array(
    'label' => esc_html__('Session Landing Page', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'sess_landing_clean' => array(
    'label' => esc_html__('Session Landing Page (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'scope' => array('converted', 'active', 'export')
  ),

  'sess_referer' => array(
    'label' => esc_html__('Session Referer URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'sess_referer_clean' => array(
    'label' => esc_html__('Session Referer URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'sess_ga' => array(
    'label' => esc_html__('Google Analytics Client ID', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'is_cookie' => true,
    'cookie_name' => '_ga',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'utm_1st_url' => array(
    'label' => esc_html__('First UTM URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'utm_1st_url_clean' => array(
    'label' => esc_html__('First UTM URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'utm_1st_visit' => array(
    'label' => esc_html__('First UTM Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_1st_visit_date_utc' => array(
    'label' => esc_html__('First UTM Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_1st_visit_date_local' => array(
    'label' => esc_html__('First UTM Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'utm_source_1st' => array(
    'label' => esc_html__('First UTM Source', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_medium_1st' => array(
    'label' => esc_html__('First UTM Medium', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_campaign_1st' => array(
    'label' => esc_html__('First UTM Campaign', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_term_1st' => array(
    'label' => esc_html__('First UTM Term', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_content_1st' => array(
    'label' => esc_html__('First UTM Content', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'utm_url' => array(
    'label' => esc_html__('Last UTM URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'utm_url_clean' => array(
    'label' => esc_html__('Last UTM URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'utm_visit' => array(
    'label' => esc_html__('Last UTM Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_visit_date_utc' => array(
    'label' => esc_html__('Last UTM Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_visit_date_local' => array(
    'label' => esc_html__('Last UTM Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),

  'utm_source' => array(
    'label' => esc_html__('Last UTM Source', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_medium' => array(
    'label' => esc_html__('Last UTM Medium', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_campaign' => array(
    'label' => esc_html__('Last UTM Campaign', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_term' => array(
    'label' => esc_html__('Last UTM Term', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'utm_content' => array(
    'label' => esc_html__('Last UTM Content', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'gclid_url' => array(
    'label' => esc_html__('GCLID URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'gclid_url_clean' => array(
    'label' => esc_html__('GCLID URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'gclid_visit' => array(
    'label' => esc_html__('GCLID Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'gclid_visit_date_utc' => array(
    'label' => esc_html__('GCLID Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'gclid_visit_date_local' => array(
    'label' => esc_html__('GCLID Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'gclid_value' => array(
    'label' => esc_html__('GCLID Value', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'fbclid_url' => array(
    'label' => esc_html__('FBCLID URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'fbclid_url_clean' => array(
    'label' => esc_html__('FBCLID URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'fbclid_visit' => array(
    'label' => esc_html__('FBCLID Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'fbclid_visit_date_utc' => array(
    'label' => esc_html__('FBCLID Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'fbclid_visit_date_local' => array(
    'label' => esc_html__('FBCLID Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'fbclid_value' => array(
    'label' => esc_html__('FBCLID Value', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),

  'msclkid_url' => array(
    'label' => esc_html__('MSCLKID URL', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'msclkid_url_clean' => array(
    'label' => esc_html__('MSCLKID URL (clean)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'url',
    'value' => '',
    'is_own_url' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'msclkid_visit' => array(
    'label' => esc_html__('MSCLKID Visit Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'timestamp',
    'value' => '',
    'is_cookie' => true,
    'rewrite_cookie' => true,
    'scope' => array('converted', 'active', 'export')
  ),
  'msclkid_visit_date_utc' => array(
    'label' => esc_html__('MSCLKID Visit Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  ),
  'msclkid_visit_date_local' => array(
    'label' => esc_html__('MSCLKID Visit Date (local)', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'email', 'active', 'export')
  ),
  'msclkid_value' => array(
    'label' => esc_html__('MSCLKID Value', AFL_WC_UTM_TEXTDOMAIN),
    'type' => 'text',
    'value' => '',
    'scope' => array('converted', 'active', 'export')
  )

  // 'has_lead' => array(
  //   'label' => esc_html__('Has Lead?', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  // 'has_order' => array(
  //   'label' => esc_html__('Has Order?', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  //
  // 'last_lead_ts' => array(
  //   'label' => esc_html__('Last Lead Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'timestamp',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  // 'last_lead_date_utc' => array(
  //   'label' => esc_html__('Last Lead Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  // 'last_lead_date_local' => array(
  //   'label' => esc_html__('Last Lead Date (local)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  //
  // 'last_order_ts' => array(
  //   'label' => esc_html__('Last Order Date (timestamp)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'timestamp',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  // 'last_order_date_utc' => array(
  //   'label' => esc_html__('Last Order Date (utc)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // ),
  // 'last_order_date_local' => array(
  //   'label' => esc_html__('Last Order Date (local)', AFL_WC_UTM_TEXTDOMAIN),
  //   'type' => 'text',
  //   'value' => '',
  //   'scope' => array('stat')
  // )

);
