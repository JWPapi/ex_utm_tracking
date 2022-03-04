<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.4.6
 */
class AFL_WC_UTM_GRAVITYFORMS_LIST_TABLE extends AFL_WC_UTM_ADMIN_LIST_TABLE
{

  private $c_attributions;
  private $c_per_page = 30;
  private $c_attribution_format;
  private $c_form_names;

  public function __construct() {

		parent::__construct(array(
			'singular' => __( 'AFL UTM Entry', AFL_WC_UTM_TEXTDOMAIN ),
			'plural'   => __( 'AFL UTM Entries', AFL_WC_UTM_TEXTDOMAIN ),
			'ajax'     => false
		));

    $this->c_attribution_format = AFL_WC_UTM_SETTINGS::get('attribution_format');
	}

  public function get_columns(){

    $columns = array(
      'id' => __( 'Entry ID#', AFL_WC_UTM_TEXTDOMAIN ),
      'date_created' => __( 'Date Submitted', AFL_WC_UTM_TEXTDOMAIN ),
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

    $total_items = 0;
    $this->c_attributions = array();

    if (!method_exists(GFAPI::class, 'get_entries') || !method_exists(GFAPI::class, 'get_entry')) :

      $this->set_pagination_args([
        'total_items' => 0,
        'per_page'    => $this->c_per_page
      ]);

      $items = array();

      return $items;
    endif;

    $search_args = $this->c_prepare_search();

    if (!empty($search_args['id'])) :

      //search by entry id
      $entry = GFAPI::get_entry($search_args['id']);

      if (is_wp_error($entry)) :
        $items = array();
      else:
        $items = array($entry);
      endif;

    else:

      $entries = GFAPI::get_entries(
        0,
        $search_args['search'],
        $search_args['sorting'],
        $search_args['paging'],
        $total_items
      );

      if (is_wp_error($entries)) :
        $items = array();
      else:
        $items = $entries;
      endif;

    endif;

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page'    => $this->c_per_page
    ]);

    if (!empty($items)) :
      foreach ($items as $item) :

        //get attribution
        $this->c_attributions[$item['id']] = AFL_WC_UTM_GRAVITYFORMS::get_conversion_attribution($item['id']);

        //get form names
        if (!empty($item['form_id'])) :
          $form = GFAPI::get_form($item['form_id']);

          $this->c_form_names[$item['form_id']] = $form['title'];

          unset($form);
        endif;

      endforeach;
    endif;

    return $items;
  }

  public function c_prepare_search(){

    $get = wp_unslash($_GET);

    $paged = isset($get['paged']) ? absint($get['paged']) : 1;

    if (empty($paged)) :
      $paged = 1;
    endif;

    $output = array(
      'id' => '',
      'search' => array(),
      'sorting' => array(),
      'paging' => array(
        'offset' => ($paged - 1) * $this->c_per_page,
        'page_size' => $this->c_per_page
      )
    );

    $search_args = array(
      'status' => 'active'
    );

    $meta_prefix = AFL_WC_UTM_GRAVITYFORMS::META_PREFIX;

    if (!empty($get['s_id'])) :
      $output['id'] = sanitize_text_field($get['s_id']);
    endif;

    //date registered
    if (!empty($get['s_date_created']['from']) || !empty($get['s_date_created']['to'])) :

      if (!empty($get['s_date_created']['from'])) :
        $get['s_date_created']['from'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_created']['from'] . ' 00:00:00', 'U');
      else:
        $get['s_date_created']['from'] = 0;
      endif;

      if (!empty($get['s_date_created']['to'])) :
        $get['s_date_created']['to'] = AFL_WC_UTM_UTIL::utc_date_format($get['s_date_created']['to'] . ' 23:59:59', 'U');
      else:
        $get['s_date_created']['to'] = time();
      endif;

      //validate
      if ($get['s_date_created']['from'] > $get['s_date_created']['to']) :
        $this->c_alert->add_error_message(__('Date Ordered From must be earlier than Date Ordered To.'));
      else:
        $search_args['field_filters'][] = array(
          'key' => $meta_prefix . 'conversion_ts',
          'value' => sanitize_text_field($get['s_date_created']['from']),
          'operator' => '>='
        );
        $search_args['field_filters'][] = array(
          'key' => $meta_prefix . 'conversion_ts',
          'value' => sanitize_text_field($get['s_date_created']['to']),
          'operator' => '<='
        );
      endif;

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
        $search_args['field_filters'][] = array(
          'key' => $meta_prefix . 'sess_visit',
          'value' => sanitize_text_field($get['s_sess_visit']['from']),
          'operator' => '>='
        );

        $search_args['field_filters'][] = array(
          'key' => $meta_prefix . 'sess_visit',
          'value' => sanitize_text_field($get['s_sess_visit']['to']),
          'operator' => '<='
        );
      endif;

    endif;

    $clid_list = array('gclid', 'fbclid', 'mscklid');

    foreach ($clid_list as $clid) :
      if (!empty($get['s_' . $clid])) :

        if ($get['s_' . $clid] == 'yes') :
          $search_args['field_filters'][] = array(
            'key' => sanitize_key($meta_prefix . $clid . '_visit'),
            'value' => 0,
            'operator' => '>'
          );
        elseif ($get['s_' . $clid] == 'no') :
          $search_args['field_filters'][] = array(
            'key' => sanitize_key($meta_prefix . $clid . '_visit'),
            'value' => '',
            'operator' => '='
          );
          $search_args['field_filters'][] = array(
            'key' => sanitize_key($meta_prefix . $clid . '_visit'),
            'value' => 'null',
            'operator' => 'isnot'
          );
        endif;

      endif;
    endforeach;

    //utm
    if (!empty($get['s_utm'])) :
      $utm_list = array('source', 'medium', 'campaign', 'term', 'content');

      //validate
      foreach($utm_list as $utm_parameter):
        if (!empty($get['s_utm'][$utm_parameter])) :

          $value = sanitize_text_field($get['s_utm'][$utm_parameter]);

          $search_args['field_filters'][] = array(
            'key' => sanitize_key($meta_prefix . 'utm_' . $utm_parameter . '_1st'),
            'value' => sanitize_text_field($value),
            'operator' => '='
          );
          $search_args['field_filters'][] = array(
            'key' => sanitize_key($meta_prefix . 'utm_' . $utm_parameter),
            'value' => sanitize_text_field($value),
            'operator' => '='
          );
        endif;
      endforeach;

    endif;

    $output['search'] = $search_args;

    return $output;
  }

  public function column_id($item){

    $form_name = (!empty($item['form_id']) && !empty($this->c_form_names[$item['form_id']])) ? $this->c_form_names[$item['form_id']] : '';

    $link = sprintf('<div><a href="%1$s">#%2$s</a><div>%3$s</div></div>',
      esc_url(AFL_WC_UTM_GRAVITYFORMS::get_admin_url_entry($item['id'], $item['form_id'], get_current_blog_id())),
      esc_html($item['id']),
      esc_html($form_name)
    );

    return $link;
  }

  public function column_date_created($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_conversion_date', $this->c_attributions[$item['id']]);

  }

  public function column_date_first_visit($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_visit', $this->c_attributions[$item['id']]);

  }

  public function column_conversion_lag($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_conversion_lag', $this->c_attributions[$item['id']]);

  }

  public function column_utm_first($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_first', $this->c_attributions[$item['id']]);

  }

  public function column_utm_last($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_utm_last', $this->c_attributions[$item['id']]);

  }

  public function column_click_identifier($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_clid', $this->c_attributions[$item['id']]);

  }

  public function column_website_referrer($item){

    return AFL_WC_UTM_HTML::get_table_column_value('afl_wc_utm_admin_column_sess_referer', $this->c_attributions[$item['id']]);

  }

  protected function c_display_header(){

    $this->c_alert->set_additional_css('mt-3');
    $this->c_alert->display();

    $attribution_format = $this->c_attribution_format;

    if ($attribution_format === 'json') :

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => 'gravityforms',
        's_id' => '',
        's_date_created' => array(
          'from' => '',
          'to' => ''
        )
      );

    else:

      $form_values = array(
        'page' => 'afl-wc-utm-reports',
        'tab' => 'gravityforms',
        's_id' => '',
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
        's_date_created' => array(
          'from' => '',
          'to' => ''
        ),
        's_sess_visit' => array(
          'from' => '',
          'to' => ''
        )
      );

    endif;

    if (isset($_GET['afl_wc_utm_form']) && $_GET['afl_wc_utm_form'] === 'afl_wc_utm_admin_search_gravityforms') :
      $form_values = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_GET), $form_values);
    endif;

    include AFL_WC_UTM_DIR_ADMIN . 'views/reports/table-search-gravityforms.php';
  }

  /*
  * @since  2.4.6
  */
  protected function c_display_form_start(){

    $output = <<<EOT
    <form method="get">
      <input type="hidden" name="page" value="afl-wc-utm-reports">
      <input type="hidden" name="tab" value="gravityforms">
      <input type="hidden" name="afl_wc_utm_form" value="afl_wc_utm_admin_search_gravityforms">
EOT;

    echo $output;

  }

}
