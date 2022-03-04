<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_WOOCOMMERCE_ORDER_LIST_TABLE extends AFL_WC_UTM_ADMIN_LIST_TABLE
{

  private $c_attributions;
  private $c_per_page = 30;
  private $c_attribution_format;

  public function __construct() {

		parent::__construct(array(
			'singular' => __( 'Order', AFL_WC_UTM_TEXTDOMAIN ),
			'plural'   => __( 'Orders', AFL_WC_UTM_TEXTDOMAIN ),
			'ajax'     => false
		));

    $this->c_attribution_format = AFL_WC_UTM_SETTINGS::get('attribution_format');
    $this->c_attributions = array();

	}

  public function get_columns(){

    $columns = array(
      'order_id' => __( 'Order ID#', AFL_WC_UTM_TEXTDOMAIN ),
      'date_ordered' => __( 'Date Ordered', AFL_WC_UTM_TEXTDOMAIN ),
      'date_first_visit' => __( 'Date First Visit', AFL_WC_UTM_TEXTDOMAIN),
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

    if (!function_exists('wc_get_orders')) :

      $this->set_pagination_args([
        'total_items' => 0,
        'per_page'    => $this->c_per_page
      ]);

      $items = array();

      return $items;
    endif;

    $search_args = $this->c_prepare_search();

    add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array($this, 'handle_custom_query_var'), 10, 2 );

    $query = wc_get_orders($search_args);
    $items = $query->orders;

    remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array($this, 'handle_custom_query_var'), 10, 2 );

    $this->set_pagination_args([
      'total_items' => $query->total,
      'per_page'    => $this->c_per_page
    ]);

    if (!empty($items)) :
      foreach ($items as $item) :

        $tmp_item_id = $item->get_id();

        $this->c_attributions[$tmp_item_id] = AFL_WC_UTM_WOOCOMMERCE_ORDER::get_conversion_attribution($tmp_item_id);

      endforeach;
    endif;


    return $items;
  }

  public function c_prepare_search(){
    global $wpdb;

    $get = wp_unslash($_GET);

    $paged = isset($get['paged']) ? absint($get['paged']) : 1;

    if (empty($paged)) :
      $paged = 1;
    endif;

    $args = array(
      'paginate' => true,
      'posts_per_page' => $this->c_per_page,
      'paged' => $paged,
      'orderby' => 'ID',
      'order' => 'DESC'
    );

    //exit
    if (!empty($get['s_order_id'])) :
      $args['p'] = $get['s_order_id'];
      return $args;
    endif;

    //date registered
    if (!empty($get['s_date_ordered']['from']) || !empty($get['s_date_ordered']['to'])) :

      if (!empty($get['s_date_ordered']['from'])) :
        $get['s_date_ordered']['from'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_ordered']['from'] . ' 00:00:00', 'U');
      else:
        $get['s_date_ordered']['from'] = 0;
      endif;

      if (!empty($get['s_date_ordered']['to'])) :
        $get['s_date_ordered']['to'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_ordered']['to'] . ' 23:59:59', 'U');
      else:
        $get['s_date_ordered']['to'] = time();
      endif;

      //validate
      if ($get['s_date_ordered']['from'] > $get['s_date_ordered']['to']) :
        $this->c_alert->add_error_message(__('Date Ordered From must be earlier than Date Ordered To.'));
      else:
        $args['date_created'] = $get['s_date_ordered']['from'] . '...' . $get['s_date_ordered']['to'];
      endif;

    endif;

    return $args;
  }

  public function handle_custom_query_var( $query, $query_vars ) {

    if ($this->c_attribution_format === 'json') :
      return $query;
    endif;

    $get = wp_unslash($_GET);

    $meta_prefix = AFL_WC_UTM_WOOCOMMERCE_ORDER::META_PREFIX;

    $meta_query = array();

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
        $this->c_alert->add_error_message(__('Date First Visit From must be earlier than Date First Visit To.'));
      else:
        $meta_query[] = array(
          'key' => $meta_prefix . 'sess_visit',
          'value' => array($get['s_sess_visit']['from'], $get['s_sess_visit']['to']),
          'type' => 'NUMERIC',
          'compare' => 'BETWEEN'
        );
      endif;

    endif;

    if (!empty($meta_query)) :
      $query['meta_query'] = $meta_query;
    endif;

    return $query;
  }

  public function column_order_id($item){

    $link = sprintf('<div><a href="%1$s">Order #%2$s</a></div><div>%3$s</div>',
      esc_url(AFL_WC_UTM_WOOCOMMERCE_ORDER::get_admin_url_order($item->get_id(), get_current_blog_id())),
      esc_html($item->get_id()),
      esc_html(ucfirst($item->get_status()))
    );

    return $link;
  }

  public function column_date_ordered($item){

    try {

      $date_time = $item->get_date_created();
      $ts = $date_time->getTimestamp();

      $output = $ts ? AFL_WC_UTM_UTIL::timestamp_to_local_date_human($ts, '<\d\i\v>M j, Y<\/\d\i\v><\d\i\v>g:i a<\/\d\i\v>') : '-';

      return wp_kses($output, array(
        'div' => array(
          'class' => array()
        )
      ));
    } catch (\Exception $e) {

    }

    return '';

  }

  public function column_date_first_visit($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_visit', $this->c_attributions[$item->get_id()]);

  }

  public function column_conversion_lag($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_conversion_lag', $this->c_attributions[$item->get_id()]);

  }

  public function column_utm_first($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_first', $this->c_attributions[$item->get_id()]);

  }

  public function column_utm_last($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_last', $this->c_attributions[$item->get_id()]);

  }

  public function column_click_identifier($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_clid', $this->c_attributions[$item->get_id()]);

  }

  public function column_website_referrer($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_referer', $this->c_attributions[$item->get_id()]);

  }

  protected function c_display_header(){

    $this->c_alert->set_additional_css('mt-3');
    $this->c_alert->display();

    $attribution_format = $this->c_attribution_format;

    if ($attribution_format === 'json') :

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => 'woocommerce',
        's_order_id' => '',
        's_date_ordered' => array(
          'from' => '',
          'to' => ''
        )
      );

    else:

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => 'woocommerce',
        's_order_id' => '',
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
        's_date_ordered' => array(
          'from' => '',
          'to' => ''
        ),
        's_sess_visit' => array(
          'from' => '',
          'to' => ''
        )
      );

    endif;

    if (isset($_GET['afl_wc_utm_form']) && $_GET['afl_wc_utm_form'] === 'afl_wc_utm_admin_search_active_users') :
      $form_values = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_GET), $form_values);
    endif;

    include AFL_WC_UTM_DIR_ADMIN . 'views/reports/table-search-woocommerce.php';
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
