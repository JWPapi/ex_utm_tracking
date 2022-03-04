<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_WORDPRESS_USER_ACTIVE_LIST_TABLE extends AFL_WC_UTM_ADMIN_LIST_TABLE
{

  private $c_attribution_format;
  private $c_conversion_events;
  private $c_attributions;
  private $c_per_page = 30;

  public function __construct() {

		parent::__construct(array(
			'singular' => __( 'User', AFL_WC_UTM_TEXTDOMAIN ),
			'plural'   => __( 'Users', AFL_WC_UTM_TEXTDOMAIN ),
			'ajax'     => false
		));

    $this->c_conversion_events = AFL_WC_UTM_CONVERSION::get_registered_events();
    $this->c_attribution_format = AFL_WC_UTM_SETTINGS::get('attribution_format');
    $this->c_attributions = array();

	}

  public function get_columns(){

    $columns = array(
      'date_updated' => __( 'Date Updated', AFL_WC_UTM_TEXTDOMAIN ),
      'date_first_visit' => __( 'Date First Visit', AFL_WC_UTM_TEXTDOMAIN ),
      'user' => __( 'User', AFL_WC_UTM_TEXTDOMAIN ),
      'conversion' => __( 'Recent Conversions', AFL_WC_UTM_TEXTDOMAIN ),
      'conversion_lag' => __( 'Conversion Lag', AFL_WC_UTM_TEXTDOMAIN),
      'utm_first' => __( 'UTM (First)', AFL_WC_UTM_TEXTDOMAIN ),
      'utm_last' => __( 'UTM (Last)', AFL_WC_UTM_TEXTDOMAIN ),
      'website_referrer' => __('Website Referrer', AFL_WC_UTM_TEXTDOMAIN),
      'click_identifier' => __( 'Click Identifier', AFL_WC_UTM_TEXTDOMAIN )
    );

    return $columns;
  }

  public function prepare_items(){

    $this->c_check_admin_referer();

    $this->_column_headers = array(
    	 $this->get_columns(),
    	 [],
    	 $this->get_sortable_columns(),
    );

    $this->items = $this->get_items();

  }

  public function get_items(){

    if (!class_exists('WP_User_Query')) :

      $this->set_pagination_args([
        'total_items' => 0,
        'per_page'    => $this->c_per_page
      ]);

      return array();
    endif;

    $search_args = $this->c_prepare_search();

    $user_query = new WP_User_Query($search_args);
    $user_list = $user_query->get_results();

    $this->set_pagination_args([
      'total_items' => $user_query->get_total(),
      'per_page'    => $this->c_per_page
    ]);

    unset($user_query);

    if (!empty($user_list)) :
      foreach ($user_list as $user) :
        $this->c_attributions[$user->ID] = AFL_WC_UTM_WORDPRESS_USER::get_active_session($user->ID);
      endforeach;
    endif;

    return $user_list;
  }

  public function c_prepare_search(){
    global $wpdb;

    $get = wp_unslash($_GET);

    $paged = isset($get['paged']) ? absint($get['paged']) : 1;

    if (empty($paged)) {
      $paged = 1;
    }

    $meta_prefix = AFL_WC_UTM_WORDPRESS_USER::get_meta_prefix_active();

    $args = array(
      'number' => $this->c_per_page,
      'paged' => $paged,
      'orderby' => 'meta_value',
      'meta_key' => $meta_prefix . 'updated_ts',
      'order' => 'DESC',
      'count_total' => true,
      'meta_query' => ''
    );

    $meta_query = array();
    $meta_query[] = array(
      'key' => $meta_prefix . 'updated_ts',
      'value' => 0,
      'compare' => '>'
    );

    //search by user
    if (!empty($get['s_user'])) :
      $args['search'] = $get['s_user'];
      $args['search_columns'] = array('ID', 'user_email');
    endif;

    //date last seen
    if (!empty($get['s_date_updated']['from']) || !empty($get['s_date_updated']['to'])) :

      if (!empty($get['s_date_updated']['from'])) :
        $get['s_date_updated']['from'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_updated']['from'] . ' 00:00:00', 'U');
      else:
        $get['s_date_updated']['from'] = 0;
      endif;

      if (!empty($get['s_date_updated']['to'])) :
        $get['s_date_updated']['to'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_updated']['to'] . ' 23:59:59', 'U');
      else:
        $get['s_date_updated']['to'] = time();
      endif;

      //validate
      if ($get['s_date_updated']['from'] > $get['s_date_updated']['to']) :
        $this->c_alert->add_error_message(__('Date Updated (From) must be earlier than Date Updated (To).'));
      else:
        $meta_query[] = array(
          'key' => $meta_prefix . 'updated_ts',
          'value' => array($get['s_date_updated']['from'], $get['s_date_updated']['to']),
          'type' => 'NUMERIC',
          'compare' => 'BETWEEN'
        );
      endif;

    endif;

    //conversion
    if (!empty($get['s_conversion'])) :

      switch($get['s_conversion']):

        case 'any':

          $meta_query[] = array(
            'relation' => 'OR',
            array(
              'key' => $meta_prefix . 'has_lead',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            ),
            array(
              'key' => $meta_prefix . 'has_order',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            )
          );

          break;

        case 'no':

          $meta_query[] = array(
            'relation' => 'AND',
            array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'has_lead',
                'value' => 0,
                'type' => 'NUMERIC',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'has_lead',
                'type' => 'NUMERIC',
                'compare' => 'NOT EXISTS'
              )
            ),
            array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'has_order',
                'value' => 0,
                'type' => 'NUMERIC',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'has_order',
                'type' => 'NUMERIC',
                'compare' => 'NOT EXISTS'
              )
            )
          );

          break;

        case 'lead_and_order':

          $meta_query[] = array(
            'relation' => 'AND',
            array(
              'key' => $meta_prefix . 'has_lead',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            ),
            array(
              'key' => $meta_prefix . 'has_order',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            )
          );

          break;

        case 'lead':

          $meta_query[] = array(
            'key' => $meta_prefix . 'has_lead',
            'value' => 1,
            'type' => 'NUMERIC',
            'compare' => '>='
          );

          break;

        case 'lead_only':

          $meta_query[] = array(
            'relation' => 'AND',
            array(
              'key' => $meta_prefix . 'has_lead',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            ),
            array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'has_order',
                'value' => 0,
                'type' => 'NUMERIC',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'has_order',
                'type' => 'NUMERIC',
                'compare' => 'NOT EXISTS'
              )
            )
          );

          break;

        case 'order':

          $meta_query[] = array(
            'key' => $meta_prefix . 'has_order',
            'value' => 1,
            'type' => 'NUMERIC',
            'compare' => '>='
          );

          break;

        case 'order_only':

          $meta_query[] = array(
            'relation' => 'AND',
            array(
              'key' => $meta_prefix . 'has_order',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '>='
            ),
            array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'has_lead',
                'value' => 0,
                'type' => 'NUMERIC',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'has_lead',
                'type' => 'NUMERIC',
                'compare' => 'NOT EXISTS'
              )
            )
          );

          break;

      endswitch;

    endif;

    if ($this->c_attribution_format === 'separate') :

        //gclid
        if (!empty($get['s_gclid'])) :

          if ($get['s_gclid'] == 'yes') :
            $meta_query[] = array(
              'key' => $meta_prefix . 'gclid_visit',
              'value' => 0,
              'compare' => '>'
            );
          elseif ($get['s_gclid'] == 'no') :
            $meta_query[] = array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'gclid_visit',
                'value' => '',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'gclid_visit',
                'value' => '',
                'compare' => 'NOT EXISTS'
              )
            );
          endif;

        endif;

        //fbclid
        if (!empty($get['s_fbclid'])) :

          if ($get['s_fbclid'] == 'yes') :
            $meta_query[] = array(
              'key' => $meta_prefix . 'fbclid_visit',
              'value' => 0,
              'compare' => '>'
            );
          elseif ($get['s_fbclid'] == 'no') :
            $meta_query[] = array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'fbclid_visit',
                'value' => '',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'fbclid_visit',
                'value' => '',
                'compare' => 'NOT EXISTS'
              )
            );
          endif;

        endif;

        //msclkid
        if (!empty($get['s_msclkid'])) :

          if ($get['s_msclkid'] == 'yes') :
            $meta_query[] = array(
              'key' => $meta_prefix . 'msclkid_visit',
              'value' => 0,
              'compare' => '>'
            );
          elseif ($get['s_msclkid'] == 'no') :
            $meta_query[] = array(
              'relation' => 'OR',
              array(
                'key' => $meta_prefix . 'msclkid_visit',
                'value' => '',
                'compare' => '='
              ),
              array(
                'key' => $meta_prefix . 'msclkid_visit',
                'value' => '',
                'compare' => 'NOT EXISTS'
              )
            );
          endif;

        endif;

        //utm
        if (!empty($get['s_utm'])) :
          $utm = ($get['s_utm']);

          //validate
          foreach($utm as $key => $value):
            if (!empty($value)) :
              $meta_query[] = array(
                'relation' => 'OR',
                array(
                  'key' => $meta_prefix . 'utm_' . $key . '_1st',
                  'value' => $value
                ),
                array(
                  'key' => $meta_prefix . 'utm_' . $key,
                  'value' => $value
                )
              );
            endif;
          endforeach;

        endif;

        //date first seen
        if (!empty($get['s_sess_visit']['from']) || !empty($get['s_sess_visit']['to'])) :

          if (!empty($get['s_sess_visit']['from'])) :
            $get['s_sess_visit']['from'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_sess_visit']['from'] . ' 00:00:00', 'U');
          else:
            $get['s_sess_visit']['from'] = 0;
          endif;

          if (!empty($get['s_sess_visit']['to'])) :
            $get['s_sess_visit']['to'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_sess_visit']['to'] . ' 23:59:59', 'U');
          else:
            $get['s_sess_visit']['to'] = time();
          endif;

          //validate
          if ($get['s_sess_visit']['from'] > $get['s_sess_visit']['to']) :
            $this->c_alert->add_error_message(__('Date First Visit (From) must be earlier than Date First Visit (To).'));
          else:
            $meta_query[] = array(
              'key' => $meta_prefix . 'sess_visit',
              'value' => array($get['s_sess_visit']['from'], $get['s_sess_visit']['to']),
              'type' => 'NUMERIC',
              'compare' => 'BETWEEN'
            );
          endif;

        endif;

    endif;//separate

    if (!empty($meta_query)) :
      $args['meta_query'] = $meta_query;
    endif;

    return $args;
  }

  public function column_user($user){

    $html = sprintf('<div class="tw-mb-1"><span class="value-email">%1$s</b></div><div><a href="%2$s">%3$s</a></div>',
      esc_html($user->user_email),
      esc_url(AFL_WC_UTM_ADMIN::get_url('reports', array('tab' => 'user-report', 'user_id' => $user->ID))),
      __('View Report', AFL_WC_UTM_TEXTDOMAIN)
    );

    return $html;
  }

  public function column_conversion($user){

    try {

      $conversions = AFL_WC_UTM_WORDPRESS_USER::get_active_user_option($user->ID, 'conversions');

      $html = '';

      if (empty($conversions)) :
        return '-';
      endif;

      foreach ($conversions as $key => $conversion) :

        if (!isset($conversion['event'])) :
          continue;
        endif;

        $event = AFL_WC_UTM_CONVERSION::get_registered_event($conversion['event']);

        $conversion = array_merge($event, $conversion);
        $conversion = apply_filters('afl_wc_utm_filter_admin_reports_active_conversion', $conversion);

        if (!empty($conversion['css']) && !empty($conversion['label'])) :

          if (!empty($conversion['data']['url'])) :
            $button = sprintf('<a href="%1$s" class="tw-inline-block tw-rounded-md tw-py-1 tw-px-3 tw-no-underline %2$s">%3$s</a>',
              esc_url($conversion['data']['url']),
              esc_attr($conversion['css']),
              esc_html($conversion['label'])
            );
          else:
            $button = sprintf('<div class="tw-inline-block tw-rounded-md tw-py-1 tw-px-3 %1$s">%2$s</div>',
              esc_attr($conversion['css']),
              esc_html($conversion['label'])
            );
          endif;

          $html .= '<div class="tw-mb-1">' . $button . '</div>';

        endif;

      endforeach;

      return $html;

    } catch (\Exception $e) {

    }

  }

  public function column_conversion_lag($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_conversion_lag', $this->c_attributions[$user->ID]);

  }

  public function column_utm_first($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_first', $this->c_attributions[$user->ID]);

  }

  public function column_utm_last($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_last', $this->c_attributions[$user->ID]);

  }

  public function column_click_identifier($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_clid', $this->c_attributions[$user->ID]);

  }

  public function column_date_first_visit($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_visit', $this->c_attributions[$user->ID]);

  }

  public function column_date_updated($user){

    try {

      $ts = AFL_WC_UTM_WORDPRESS_USER::get_active_user_option($user->ID, 'updated_ts');

      $output = $ts ? AFL_WC_UTM_UTIL::timestamp_to_local_date_human($ts, '<\d\i\v>M j, Y<\/\d\i\v><\d\i\v>g:i a<\/\d\i\v>') : '-';

      return wp_kses($output, array(
        'div' => array(
          'class' => array()
        )
      ));

    } catch (\Exception $e) {

    }

  }

  /**
   * @since 2.4.0
   */
  public function column_website_referrer($user){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_referer', $this->c_attributions[$user->ID]);

  }

  protected function c_display_header(){

    $this->c_alert->set_additional_css('mt-3');
    $this->c_alert->display();

    $attribution_format = $this->c_attribution_format;

    if ($attribution_format === 'json') :

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => '',
        's_user' => '',
        's_date_updated' => array(
          'from' => '',
          'to' => ''
        ),
        's_conversion' => ''
      );

    else:

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => '',
        's_user' => '',
        's_gclid' => '',
        's_fbclid' => '',
        's_msclkid' => '',
        's_utm' => array(
          'source' => '',
          'medium' => '',
          'campaign' => '',
          'term' => '',
          'content' => ''
        ),
        's_sess_visit' => array(
          'from' => '',
          'to' => ''
        ),
        's_date_updated' => array(
          'from' => '',
          'to' => ''
        ),
        's_conversion' => ''
      );

    endif;

    if (isset($_GET['afl_wc_utm_form']) && $_GET['afl_wc_utm_form'] === 'afl_wc_utm_admin_search_active_users') :
      $form_values = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_GET), $form_values);
    endif;

    include AFL_WC_UTM_DIR_ADMIN . 'views/reports/table-search-active.php';
  }

  /*
  * @since  2.4.6
  */
  protected function c_display_form_start(){

    $output = <<<EOT
    <form method="get">
      <input type="hidden" name="page" value="afl-wc-utm-reports">
      <input type="hidden" name="afl_wc_utm_form" value="afl_wc_utm_admin_search_active_users">
EOT;

    echo $output;

  }

}
