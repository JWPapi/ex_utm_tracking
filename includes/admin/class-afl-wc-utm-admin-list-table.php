<?php defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_ADMIN_LIST_TABLE extends WP_List_Table
{

  public $c_alert;

  public function __construct($settings = [])
  {
    parent::__construct($settings);

    $this->c_alert = new AFL_WC_UTM_ALERT();

  }

  public function get_offset(){

    if ($this->get_pagenum() > 1) {
      $offset = (($this->get_pagenum()-1) * $this->_pagination_args['per_page']) + 1;
    } else {
      $offset = 0;
    }

    return $offset;
  }

  public function get_per_page(){
    return $this->_pagination_args['per_page'];
  }

  public function display() {
		$singular = $this->_args['singular'];

    echo '<div class="afl-wc-utm-list-table">';
    $this->c_display_form_start();

    echo '<header>';
    $this->c_display_header();
    echo '</header>';

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
<div class="tw-w-full tw-overflow-x-auto">
<table class="tw-table-auto tw-w-full striped">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody
		<?php
		if ( $singular ) {
			echo " data-wp-lists='list:$singular'";
		}
		?>
		>
		<?php $this->display_rows_or_placeholder(); ?>
	</tbody>

	<tfoot>
	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>
	</tfoot>

</table>
</div>
		<?php
		$this->display_tablenav( 'bottom' );

    echo '<footer>';
    $this->c_display_footer();
    echo '</footer>';

    $this->c_display_form_end();
    echo '</div><!--/.afl-wc-utm-list-table-->';

	}

  protected function c_display_header(){}
  protected function c_display_footer(){}

  /**
   * @since 2.4.6
   */
  protected function c_display_form_start(){

    $output = <<<EOT
    <form method="get">
      <input type="hidden" name="page" value="">
      <input type="hidden" name="afl_wc_utm_form" value="">
EOT;

    echo $output;

  }

  /**
   * @since 2.4.6
   */
  protected function c_display_form_end(){
    echo '</form>';
  }

  /**
   * @since 2.4.6
   */
  protected function c_check_admin_referer(){

    if (!empty($_REQUEST['_wp_http_referer'])) :
      check_admin_referer('bulk-' . $this->_args['plural']);
      wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    	exit;
    endif;

  }
}
