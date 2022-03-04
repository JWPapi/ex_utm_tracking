<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_HTML
{

  private static $html_variables = array(
    'session' => array(
      'title' => 'Session',
      'show' => true,
      'list' => array(
        'conversion_lag_human' => array(
          'label' => 'Conversion Lag',
          'value' => '',
          'type' => 'text'
        ),
        'conversion_ts' => array(
          'label' => 'Conversion Date',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human'
        ),
        'sess_visit' => array(
          'label' => 'First Visit Date',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human'
         ),
        'sess_landing' => array(
          'label' => 'First Landing Page',
          'value' => '',
          'type' => 'textarea',
          'show_page_path' => true
         ),
        'sess_referer' => array(
          'label' => 'First Website Referrer',
          'value' => '',
          'type' => 'textarea'
         ),
        'sess_ga' => array(
          'label' => 'Google Analytics Client ID',
          'value' => ''
        ),
        'cookie_consent' => array(
          'label' => 'Cookie Consent',
          'value' => ''
        )
      )
    ),
    'utm_1st' => array(
      'title' => 'UTM <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color: #0288d1; font-size: 0.9rem">First Touch</span>',
      'description' => '',
      'show' => true,
      'minimize_if_empty' => true,
      'list' => array(
        'utm_1st_url' => array(
          'label' => 'UTM URL',
          'value' => '',
          'type' => 'textarea',
          'export_label' => 'UTM URL (First)',
          'show_page_path' => true
        ),
        'utm_1st_visit' => array(
          'label' => 'UTM Visited',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human',
          'export_label' => 'UTM Visited (First)'
        ),
        'utm_source_1st' => array(
          'label' => 'UTM Source',
          'value' => '',
          'export_label' => 'UTM Source (First)'
        ),
        'utm_medium_1st' => array(
          'label' => 'UTM Medium',
          'value' => '',
          'export_label' => 'UTM Medium (First)'
        ),
        'utm_campaign_1st' => array(
          'label' => 'UTM Campaign',
          'value' => '',
          'export_label' => 'UTM Campaign (First)'
        ),
        'utm_term_1st' => array(
          'label' => 'UTM Term',
          'value' => '',
          'export_label' => 'UTM Term (First)'
        ),
        'utm_content_1st' => array(
          'label' => 'UTM Content',
          'value' => '',
          'export_label' => 'UTM Content (First)'
        )
      )
    ),
    'utm' => array(
      'title' => 'UTM <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color:#43a047; font-size: 0.9rem">Last Touch</span>',
      'description' => '',
      'show' => true,
      'minimize_if_empty' => true,
      'list' => array(
        'utm_url' => array(
          'label' => 'UTM URL',
          'value' => '',
          'type' => 'textarea',
          'export_label' => 'UTM URL (Last)',
          'show_page_path' => true
        ),
        'utm_visit' => array(
          'label' => 'UTM Visited',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human',
          'export_label' => 'UTM Visited (Last)'
        ),
        'utm_source' => array(
          'label' => 'UTM Source',
          'value' => '',
          'export_label' => 'UTM Source (Last)'
        ),
        'utm_medium' => array(
          'label' => 'UTM Medium',
          'value' => '',
          'export_label' => 'UTM Medium (Last)'
        ),
        'utm_campaign' => array(
          'label' => 'UTM Campaign',
          'value' => '',
          'export_label' => 'UTM Campaign (Last)'
        ),
        'utm_term' => array(
          'label' => 'UTM Term',
          'value' => '',
          'export_label' => 'UTM Term (Last)'
        ),
        'utm_content' => array(
          'label' => 'UTM Content',
          'value' => '',
          'export_label' => 'UTM Content (Last)'
        ),
      )
    ),
    'gclid' => array(
      'title' => 'Google (gclid) <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color:#43a047; font-size: 0.9rem">Last Touch</span>',
      'description' => '',
      'show' => true,
      'minimize_if_empty' => true,
      'list' => array(
        'gclid_url' => array(
          'label' => 'URL with gclid',
          'value' => '',
          'type' => 'textarea',
          'show_page_path' => true
        ),
        'gclid_visit' => array(
          'label' => 'Visited with gclid',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human'
        ),
        'gclid_value' => array(
          'label' => 'Gclid value',
          'value' => '',
          'type' => 'text'
        )
      )
    ),
    'fbclid' => array(
      'title' => 'Facebook (fbclid) <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color:#43a047; font-size: 0.9rem">Last Touch</span>',
      'description' => '',
      'show' => true,
      'minimize_if_empty' => true,
      'list' => array(
        'fbclid_url' => array(
          'label' => 'URL with fbclid',
          'value' => '',
          'type' => 'textarea',
          'show_page_path' => true
        ),
        'fbclid_visit' => array(
          'label' => 'Visited with fbclid',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human'
        ),
        'fbclid_value' => array(
          'label' => 'Fbclid value',
          'value' => '',
          'type' => 'text'
        )
      )
    ),
    'msclkid' => array(
      'title' => 'Microsoft (msclkid) <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color:#43a047; font-size: 0.9rem">Last Touch</span>',
      'description' => '',
      'show' => true,
      'minimize_if_empty' => true,
      'list' => array(
        'msclkid_url' => array(
          'label' => 'URL with msclkid',
          'value' => '',
          'type' => 'textarea',
          'show_page_path' => true
        ),
        'msclkid_visit' => array(
          'label' => 'Visited with msclkid',
          'value' => '',
          'type' => 'text',
          'formatter' => 'timestamp_to_local_date_human'
        ),
        'msclkid_value' => array(
          'label' => 'Msclkid value',
          'value' => '',
          'type' => 'text'
        )
      )
    )
  );

  public function __construct()
  {
    // code...
  }

  public static function get_html_variables(){
    return self::$html_variables;
  }

  public static function render_metabox_content($html_variables = array(), $data = array()){
    echo AFL_WC_UTM_HTML::get_metabox_content($html_variables, $data);
  }

  public static function get_metabox_content($html_variables = array(), $data = array()){

    $html = '';

    if (!AFL_WC_UTM_LICENSE_MANAGER::is_license_active()) :

      $is_activated = AFL_WC_UTM_LICENSE_MANAGER::is_license_activated();

      $html .= sprintf('<div style="text-align: center; border: 1px solid %1$s; border-radius: 10px; margin-bottom: 10px"><div style="padding: 10px 10px; background: %1$s; color: white; font-size: 16px; font-weight: bold; border-top-right-radius: 8px; border-top-left-radius: 8px;">%2$s</div>',
        esc_attr($is_activated ? '#9e9e9e' : '#212121'),
        esc_html($is_activated ? 'License Inactive / Expired' : 'License Not Activated')
      );
      $html .= '<img src="' . esc_url(AFL_WC_UTM_URL_ADMIN . 'img/nav_icon.png') . '" style="max-width:60px; display: inline-block; margin: 10px;">';
      $html .= '<div style="padding: 0px 5px 10px">Please activate or renew your license to access features, plugin updates and security fixes.</div></div>';

    endif;

    if (isset($data['cookie_consent']['value']) && $data['cookie_consent']['value'] === 'deny') :

      $html .= '<div><b>Cookie Consent</b><input type="text" value="' . esc_attr($data['cookie_consent']['value']) . '" style="width:100%; max-width:100%; margin: 4px 0 8px" readonly>';
      $html .= '<p style="margin-top: 4px">' . __('User did not consent to cookie tracking.') . '</p></div>';
      return $html;

    endif;

    if (isset($html_variables['utm_1st']['list']['utm_1st_url']['value']) && isset($html_variables['utm']['list']['utm_url']['value'])) :

      if (!empty($data['utm_1st_url']['value']) && !empty($data['utm_url']['value']) && $data['utm_1st_url']['value'] == $data['utm_url']['value']
        && !empty($data['utm_1st_visit']['value']) && !empty($data['utm_visit']['value']) && $data['utm_1st_visit']['value'] == $data['utm_visit']['value']
      ) :
        $html_variables['utm_1st']['title'] = 'UTM <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color:#43a047; font-size: 0.9rem">Last Touch</span> = <span style="display:inline-block; padding: 2px 4px; border-radius: 2px; color: #fff; background-color: #0288d1; font-size: 0.9rem">First Touch</span>';
        unset($html_variables['utm']);
      endif;
    endif;

    foreach ($html_variables as $group_key => $group) :

      if (isset($group['show']) && $group['show'] === false) {
        continue;
      }

      if (isset($group['title'])) {
        $html .= '<h3>' . wp_kses($group['title'],
          array(
            'span' => array('style' => array())
          )
        ) . '</h3>';
      }

      if (isset($group['description'])) {
        $html .= '<p style="margin-top: 0">' . esc_html($group['description']) . '</p>';
      }

      if (!empty($group['list'])) :
        $i = 0;
        $is_empty = false;

        foreach($group['list'] as $row_key => $row):

          if (isset($data[$row_key]['value'])) :
            $row['value'] = $data[$row_key]['value'];
          endif;

          if (!empty($row['formatter']) && method_exists(__CLASS__, 'formatter_' . $row['formatter'])) {
            $row['value'] = call_user_func(array(__CLASS__, 'formatter_' . $row['formatter']), $row['value']);
          }

          if ($i == 0 && !empty($group['minimize_if_empty']) && ($row['value'] === false || $row['value'] === '')) :
            $is_empty = true;
            break;
          endif;

          $html .= '<div><b>' . esc_html($row['label']) . '</b></div>';

          if (isset($row['description'])) :
            $html .= sprintf('<div style="margin: 0 0 8px"><i>%1$s</i></div>',
              esc_html($row['description'])
            );
          endif;

          if(isset($row['type']) && $row['type'] === 'textarea' && !empty($row['value'])):
            $html .= sprintf('<textarea rows="3" style="width:100%%; max-width:100%%; margin: 4px 0 4px; pointer-events:auto!important; cursor:auto!important" readonly>%1$s</textarea>',
              esc_textarea($row['value'])
            );
          else:
            $html .= sprintf('<input type="text" value="%1$s" style="width:100%%; max-width:100%%; margin: 4px 0 8px" readonly>',
              esc_attr($row['value'])
            );
          endif;

          if (isset($row['show_page_path']) && $row['show_page_path'] === true && !empty($row['value'])) :
            $page_path = AFL_WC_UTM_UTIL::get_url_path($row['value']);

            $html .= sprintf('<div style="margin: 0 0 10px"><b>Page:</b> <a href="%1$s" target="_blank" rel="noreferrer noopener">%2$s</a></div>',
              esc_url(AFL_WC_UTM_UTIL::clean_url($row['value'])),
              esc_html(($page_path !== '' ? '/' . $page_path . '/' : 'Home'), true)
            );
          endif;

          $i++;
        endforeach;

        if ($is_empty) :
          $html .= '<div>No value recorded.</div>';
        endif;

        $html .= '<hr>';

      endif;
    endforeach;

    return $html;
  }

  public static function get_conversion_report_metabox_for_integration($integration_title, $entry_id, $entry_url, $conversion_attribution, $integration_color = 'black'){

    $utm_html_variables = AFL_WC_UTM_HTML::get_html_variables();

    foreach ($utm_html_variables as $tmp_key => $tmp_row) :
      $utm_html_variables[$tmp_key]['minimize_if_empty'] = false;
    endforeach;

    $html = <<<'EOT'
<div class="tw-bg-white tw-border-solid tw-border tw-border-%1$s-600">
  <div class="tw-p-3 tw-bg-%1$s-600 tw-border-solid tw-border-l-0 tw-border-r-0 tw-border-t-0 tw-border-b tw-border-%1$s-600 tw-flex tw-flex-row">
    <div class="tw-flex-grow"><h3 class="tw-inline-block tw-my-0 tw-text-white">%2$s #%3$s</h3></div>
    <div class="tw-flex-none tw-text-right"><a href="%4$s" class="tw-text-white hover:tw-text-gray-200">View</a></div>
  </div>
  <div class="tw-p-3">%5$s</div>
</div>
EOT;

    return sprintf($html,
      esc_attr($integration_color),
      esc_html($integration_title),
      esc_html($entry_id),
      esc_url($entry_url),
      AFL_WC_UTM_HTML::get_metabox_content($utm_html_variables, $conversion_attribution)
    );

  }

  public static function get_conversion_report_table_for_email($meta_whitelist){

    $html = <<<'EOT'
<table width="100%%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
  <tr><td style="padding:0">&nbsp;</td></tr>
  %1$s
  <tr><td style="padding:0">&nbsp;</td></tr>
</table>
EOT;

    if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :

        $tr = sprintf('<tr bgcolor="%3$s">
              <td style="padding:6px 4px">
                  <font style="font-family: sans-serif; font-size:12px;"><strong>%1$s</strong></font>
              </td>
           </tr>
           <tr bgcolor="%4$s">
              <td style="padding:6px 20px">
                  <font style="font-family: sans-serif; font-size:12px;">%2$s<br>%5$s</font>
              </td>
           </tr>',
           esc_html(__('Cookie Consent')),
           esc_html(strtoupper($meta_whitelist['cookie_consent']['value'])),
           esc_attr( '#EAF2FA' ),
           esc_attr( '#FFFFFF' ),
           esc_html(__('User did not consent to cookie tracking.'))
        );

        return sprintf($html, $tr);

    endif;

    $tr = '';
    foreach($meta_whitelist as $meta) :

      if (empty( $meta['value'] ) && strlen( $meta['value'] ) == 0) :

        $meta_value = '&nbsp;';

      elseif (isset($meta['type'])) :

        switch($meta['type']):

          case 'url':

            $meta_value = wp_kses(AFL_WC_UTM_UTIL::pretty_url_with_break($meta['value']), array('br' => array()));
            break;

          default:

            $meta_value = esc_html($meta['value']);

        endswitch;

      else:

        $meta_value = esc_html($meta['value']);

      endif;

      $tr .= sprintf(
        '<tr bgcolor="%3$s">
            <td style="padding:6px 4px">
                <font style="font-family: sans-serif; font-size:12px;"><strong>%1$s</strong></font>
            </td>
         </tr>
         <tr bgcolor="%4$s">
            <td style="padding:6px 20px">
                <font style="font-family: sans-serif; font-size:12px;">%2$s</font>
            </td>
         </tr>',
         esc_html($meta['label']),
         $meta_value,
         esc_attr( '#EAF2FA' ),
         esc_attr( '#FFFFFF' )
      );
    endforeach;

    return sprintf($html, $tr);

  }

  public static function formatter_duration($seconds){

    return AFL_WC_UTM_UTIL::seconds_to_duration($seconds);
  }

  public static function formatter_timestamp_to_local_date_human($timestamp){

    try {

      if ($timestamp) {
        $timestamp = AFL_WC_UTM_UTIL::timestamp_to_local_date_human($timestamp);
      }

    } catch (\Exception $e) {

    }

    return $timestamp;
  }

  public static function get_clid_tag_html($clid){

    switch ($clid) :
      case 'gclid':

        $html = '<div style="display: inline-block; padding: 2px 10px; border-radius:9999px; background-color: #c53030; color: #ffffff">Google</div>';
        break;

      case 'fbclid':

        $html = '<div style="display: inline-block; padding: 2px 10px; border-radius:9999px; background-color: #2b6cb0; color: #ffffff">Facebook</div>';
        break;

      case 'msclkid':

        $html = '<div style="display: inline-block; padding: 2px 10px; border-radius:9999px; background-color: #039be5; color: #ffffff">Microsoft</div>';
        break;

      default:

        $html = '';
        break;

    endswitch;

    return $html;
  }

  /**
   * @since 2.4.0
   */
  public static function get_cookie_consent_html($consent_value){

    switch($consent_value):
      case 'deny':

        $html = '<span style="color: #999;"><i>' . __('Cookie Consent') . ':' .'</i><br><i>' . __('DENY') . '</i></span>';
        break;

      case 'allow':

        $html = '<span style="color: #999;"><i>' . __('Cookie Consent') . ':' .'</i><br><i>' . __('ALLOW') . '</i></span>';
        break;

      default:

        $html = '<span style="color: #999;"><i>' . __('Cookie Consent') . ':' .'</i><br><i>' . __('N/A') . '</i></span>';
        break;

    endswitch;

    return $html;

  }

  /**
   * @since 2.4.0
   */
  public static function get_table_column_value($column_slug, $meta_whitelist){

    if ($column_slug === 'afl_wc_utm_admin_column_conversion_lag') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      return esc_html(!empty($meta_whitelist['conversion_lag_human']['value']) ? $meta_whitelist['conversion_lag_human']['value'] : '-');

    elseif ($column_slug === 'afl_wc_utm_admin_column_utm_first') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      return sprintf('%1$s<br>%2$s<br>%3$s',
        esc_html(!empty($meta_whitelist['utm_source_1st']['value']) ? $meta_whitelist['utm_source_1st']['value'] : '-'),
        esc_html(!empty($meta_whitelist['utm_medium_1st']['value']) ? $meta_whitelist['utm_medium_1st']['value'] : '-'),
        esc_html(!empty($meta_whitelist['utm_campaign_1st']['value']) ? $meta_whitelist['utm_campaign_1st']['value'] : '-')
      );

    elseif ($column_slug === 'afl_wc_utm_admin_column_utm_last') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      return sprintf('%1$s<br>%2$s<br>%3$s',
        esc_html(!empty($meta_whitelist['utm_source']['value']) ? $meta_whitelist['utm_source']['value'] : '-'),
        esc_html(!empty($meta_whitelist['utm_medium']['value']) ? $meta_whitelist['utm_medium']['value'] : '-'),
        esc_html(!empty($meta_whitelist['utm_campaign']['value']) ? $meta_whitelist['utm_campaign']['value'] : '-')
      );

    elseif ($column_slug === 'afl_wc_utm_admin_column_clid') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      $gclid = !empty($meta_whitelist['gclid_visit']['value']) ? $meta_whitelist['gclid_visit']['value'] : '';
      $fbclid = !empty($meta_whitelist['fbclid_visit']['value']) ? $meta_whitelist['fbclid_visit']['value'] : '';
      $msclkid = !empty($meta_whitelist['msclkid_visit']['value']) ? $meta_whitelist['msclkid_visit']['value'] : '';

      $output = '';
      $count_margin = 0;

      if (!empty($gclid)) :
        $output .= ($count_margin ? '<div style="margin-top:6px">' : '<div>') . self::get_clid_tag_html('gclid') . '</div>';
        $count_margin++;
      endif;

      if (!empty($fbclid)) :
        $output .= ($count_margin ? '<div style="margin-top:6px">' : '<div>') . self::get_clid_tag_html('fbclid') . '</div>';
        $count_margin++;
      endif;

      if (!empty($msclkid)) :
        $output .= ($count_margin ? '<div style="margin-top:6px">' : '<div>') . self::get_clid_tag_html('msclkid') . '</div>';
        $count_margin++;
      endif;

      return empty($output) ? '-' : $output;

    elseif ($column_slug === 'afl_wc_utm_admin_column_sess_referer') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      $referrer_url = !empty($meta_whitelist['sess_referer']['value']) ? $meta_whitelist['sess_referer']['value'] : '';

      $host = parse_url($referrer_url, PHP_URL_HOST);

      return esc_html(!empty($host) ? $host : '-');

    elseif ($column_slug === 'afl_wc_utm_admin_column_sess_visit') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      $ts = !empty($meta_whitelist['sess_visit']['value']) ? $meta_whitelist['sess_visit']['value'] : 0;

      $output = $ts ? AFL_WC_UTM_UTIL::timestamp_to_local_date_human($ts, '<\d\i\v>M j, Y<\/\d\i\v><\d\i\v>g:i a<\/\d\i\v>') : '-';

      return wp_kses($output, array(
        'div' => array(
          'class' => array()
        )
      ));

    elseif ($column_slug === 'afl_wc_utm_admin_column_conversion_date') :

      if (isset($meta_whitelist['cookie_consent']['value']) && $meta_whitelist['cookie_consent']['value'] === 'deny') :
        return self::get_cookie_consent_html($meta_whitelist['cookie_consent']['value']);
      endif;

      $ts = !empty($meta_whitelist['conversion_ts']['value']) ? $meta_whitelist['conversion_ts']['value'] : 0;

      $output = $ts ? AFL_WC_UTM_UTIL::timestamp_to_local_date_human($ts, '<\d\i\v>M j, Y<\/\d\i\v><\d\i\v>g:i a<\/\d\i\v>') : '-';

      return wp_kses($output, array(
        'div' => array(
          'class' => array()
        )
      ));

    endif;

    return '';
  }

}
