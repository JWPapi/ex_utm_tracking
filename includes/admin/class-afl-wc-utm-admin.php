<?php defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.appfromlab.com
 * @since      1.0.0
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/admin
 * @author     Appfromlab <hello@appfromlab.com>
 */
class AFL_WC_UTM_ADMIN {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {

	}

	public static function register_hooks(){

		add_action( 'admin_menu', array(__CLASS__, 'menu_register') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts') );

		add_filter( 'plugin_action_links_' . AFL_WC_UTM_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ), 10, 1 );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ), 10, 1 );
		add_action( 'in_admin_header', array( __CLASS__, 'embed_nav_header' ) );
		add_action( 'afl_wc_utm_admin_page_notice', array( __CLASS__, 'admin_page_notice') );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_scripts($hook) {

		if (self::is_our_admin_page()) :
			wp_enqueue_style( 'afl-wc-utm-admin', plugin_dir_url(AFL_WC_UTM_PLUGIN_FILE) . 'admin/css/style-admin.min.css', false, AFL_WC_UTM::VERSION );
		endif;

	}

	public static function plugin_action_links( $actions ) {

		if (AFL_WC_UTM_LICENSE_MANAGER::is_license_active()) :
			$links = array(
				'settings' => sprintf('<a href="%1$s" aria-label="%2$s">%2$s</a>',
					self::get_url('settings'),
					esc_attr__( 'Settings', AFL_WC_UTM_TEXTDOMAIN )
				)
			);
		else:
			$links = array(
				'license' => sprintf('<a href="%1$s" aria-label="%2$s">%2$s</a>',
					self::get_url('license'),
					esc_attr__( 'Register License', AFL_WC_UTM_TEXTDOMAIN )
				)
			);
		endif;

    return array_merge( $links, $actions );
	}

	public static function plugin_row_meta( $links, $file ) {

		if ( AFL_WC_UTM_PLUGIN_BASENAME === $file ) {

			$row_meta = array(
				'changelog' => sprintf('<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>',
					esc_url( 'https://www.appfromlab.com/woocommerce-utm-tracker-changelog/?utm_source=afl_wc_utm_plugin&utm_medium=wp_admin_plugins' ),
					esc_attr__( 'Changelog', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'Changelog', AFL_WC_UTM_TEXTDOMAIN )
				),
				'docs' => sprintf('<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>',
					esc_url( 'https://www.appfromlab.com/docs/afl-utm-tracker-documentation/?utm_source=afl_wc_utm_plugin&utm_medium=wp_admin_plugins' ),
					esc_attr__( 'Docs', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'Docs', AFL_WC_UTM_TEXTDOMAIN )
				),
				'support' => sprintf('<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>',
					esc_url( 'https://www.appfromlab.com/contact/?utm_source=afl_wc_utm_plugin&utm_medium=wp_admin_plugins' ),
					esc_attr__( 'Support', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'Support', AFL_WC_UTM_TEXTDOMAIN )
				)
			);

			if (AFL_WC_UTM_LICENSE_MANAGER::is_license_active()) :

				$row_meta['license_status'] = sprintf('<span aria-label="%1$s">%2$s</span>',
					esc_attr__( 'License: Active', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'License: Active', AFL_WC_UTM_TEXTDOMAIN )
				);

				$row_meta['update_checker'] = sprintf('<span aria-label="%1$s">%2$s</span>',
					esc_attr__( 'Check Updates: Enabled', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'Check Updates: Enabled', AFL_WC_UTM_TEXTDOMAIN )
				);

			else:

				$row_meta['license_status'] = sprintf('<span aria-label="%1$s">%2$s</span>',
					esc_attr__( 'License: Inactive', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'License: Inactive', AFL_WC_UTM_TEXTDOMAIN )
				);

				$row_meta['update_checker'] = sprintf('<span aria-label="%1$s">%2$s</span>',
					esc_attr__( 'Check Updates: Disabled', AFL_WC_UTM_TEXTDOMAIN ),
					esc_html__( 'Check Updates: Disabled', AFL_WC_UTM_TEXTDOMAIN )
				);

			endif;

			return array_merge($links, $row_meta);
		}

		return (array) $links;
	}

	/**
	 * @since    2.0.0
	 */
	public static function menu_register(){

		$icon = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMTIwIDEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgc3Ryb2tlLW1pdGVybGltaXQ9IjIiPjxwYXRoIGQ9Ik04Mi4yMjUgOTYuNDJsMTAuMjU2IDEyLjgyOWMtOS4zMTYgNi4xNTctMjAuNDc4IDkuNzQ0LTMyLjQ4MSA5Ljc0NC0zMi41ODEgMC01OC45OTMtMjYuNDEyLTU4Ljk5My01OC45OTNTMjcuNDE5IDEuMDA3IDYwIDEuMDA3IDExOC45OTMgMjcuNDE5IDExOC45OTMgNjBjMCAxOC44ODMtOC44NzIgMzUuNjkyLTIyLjY3NSA0Ni40OUw4Ni4xMTcgOTMuNzMxYzEwLjA2OS03LjgxIDE2LjU1My0yMC4wMjQgMTYuNTUzLTMzLjc0MSAwLTIzLjU1Ni0xOS4xMjQtNDIuNjgtNDIuNjgtNDIuNjgtMjMuNTU1IDAtNDIuNjggMTkuMTI0LTQyLjY4IDQyLjY4czE5LjEyNSA0Mi42OCA0Mi42OCA0Mi42OGM4LjE0MyAwIDE1Ljc1Ny0yLjI4NSAyMi4yMzUtNi4yNXpNOTguOTI5IDYwQzk4LjkyOSA4MS41IDgxLjUgOTguOTI5IDYwIDk4LjkyOSAzOC41IDk4LjkyOSAyMS4wNzEgODEuNSAyMS4wNzEgNjAgMjEuMDcxIDM4LjUgMzguNSAyMS4wNzEgNjAgMjEuMDcxIDgxLjUgMjEuMDcxIDk4LjkyOSAzOC41IDk4LjkyOSA2MHptLTI2LjEyOS42ODdsMS42MzkuNTExIDEuNDM0Ljc3IDEuNjE2IDEuMzQ2LjI2MS4zMTUuOTM5IDEuMjgzLjMxLjU2OS41NDQgMS4zNDguMjIzLjcxNS4wNy4zNTMuMTY0IDEuNjE2VjgzLjM2SDQwVjY5LjUxM2MwLS42Mi4wNjMtMS4yMjUuMTg0LTEuODFsLjY0OC0xLjkxMi4yOTgtLjU0Ni43NDctMS4xNjQuNDM5LS41MzEuOTk3LS45NTEuNDc1LS4zOTMgMS43MjMtLjk5MiAxLjY4OS0uNTI3IDEuODA5LS4xODRoMjEuOTgybDEuODA5LjE4NHpNNjEuOTY5IDMxLjA5MmwyLjA2My43MTEuNjEzLjM0MiAxLjIzMS43NTIuNjQ0LjUyOS45ODQuOTEzLjU4MS42NjkuNzk2IDEuMDg1LjQ3NS43NzIuNjEzIDEuMjY5LjM0MS44MjQuNDIyIDEuNTI5LjE4Mi43Ni4yMiAyLjQ0NS0uMjIgMi40NDUtLjE4Mi43NjEtLjQyMiAxLjUyOS0uMzQxLjgyMy0uNjEzIDEuMjY5LS40NzUuNzcyLS43OTYgMS4wODUtLjU4MS42NjktLjk4NC45MTQtLjY0NC41MjgtMS4yMzEuNzUyLS42MTMuMzQyLTIuMDI0LjcwNy0yLjMwMy4yNjEtMi4wNzktLjIzNi0uMzQ4LS4wNjgtMS42MTktLjU2Ni0uODk0LS40NC0xLjc4OC0xLjE5OS0uMjUtLjIzMy0xLjQwMi0xLjQzMS0xLjA5Ny0xLjQ5NS0uMzgtLjc4OWMtLjMyMy0uNTk0LS42LTEuMjIxLS44MDgtMS44ODNsLS40NDYtMS42MTYtLjExMy0uNjItLjIwNC0yLjI4MS4yMDQtMi4yNzYuMzA3LTEuMzI4LjI2MS0uOTQ1LjQzNS0xLjA5Ni40NjItLjk1OC41ODMtLjk5Ni41MjYtLjcxNy44NTEtMS4wMzcgMS41OTMtMS40MjEuNjg4LS40MTkgMS4xNzUtLjY5IDEuMjU0LS40MzkuODM0LS4yNzkgMi4yNTUtLjI1NiAyLjI2NC4yNTd6IiBmaWxsPSIjYTVkNmE3Ii8+PC9zdmc+';

		add_menu_page(
			'AFL UTM Tracker',
			'AFL UTM Tracker',
			'afl_wc_utm_admin_view',
			'afl-wc-utm',
			array('AFL_WC_UTM_ADMIN_MAIN_CONTROLLER', 'page'),
			$icon,
			58
		);

		add_submenu_page(
			'afl-wc-utm',
			'Reports',
			'Reports',
			'afl_wc_utm_admin_view_reports',
			'afl-wc-utm-reports',
			array('AFL_WC_UTM_ADMIN_REPORTS_CONTROLLER', 'page_reports')
		);

		add_submenu_page(
			'afl-wc-utm',
			'Settings',
			'Settings',
			'afl_wc_utm_admin_manage_settings',
			'afl-wc-utm-settings',
			array('AFL_WC_UTM_ADMIN_SETTINGS_CONTROLLER', 'page_settings')
		);

		if (is_multisite() && is_main_site()) :

			add_submenu_page(
				'afl-wc-utm',
				'Network Settings',
				'Network Settings',
				'manage_network_options',
				'afl-wc-utm-network-settings',
				array('AFL_WC_UTM_ADMIN_NETWORK_CONTROLLER', 'page_settings')
			);

		endif;

		add_submenu_page(
			'afl-wc-utm',
			'License',
			'License',
			'manage_options',
			'afl-wc-utm-license',
			array('AFL_WC_UTM_ADMIN_SETTINGS_CONTROLLER', 'page_license')
		);

	}

	/**
	 * @since    2.0.0
	 */
	public static function get_url($page, $query = array()){

		switch ($page) :
			case 'no_permission':

				$query = AFL_WC_UTM_UTIL::merge_default_without_blank($query, array(
					'page' => 'afl-wc-utm-no-permission'
				));

				$url = add_query_arg($query, admin_url('admin.php'));
				break;

			case 'reports':

				$query = AFL_WC_UTM_UTIL::merge_default_without_blank($query, array(
					'page' => 'afl-wc-utm-reports',
					'tab' => '',
					'user_id' => ''
				));

				$url = add_query_arg($query, admin_url('admin.php'));
				break;

			case 'settings':

				$query = AFL_WC_UTM_UTIL::merge_default_without_blank($query, array(
					'page' => 'afl-wc-utm-settings'
				));

				$url = add_query_arg($query, admin_url('admin.php'));
				break;

			case 'license':

				$query = AFL_WC_UTM_UTIL::merge_default_without_blank($query, array(
					'page' => 'afl-wc-utm-license'
				));

				$url = add_query_arg($query, admin_url('admin.php'));
				break;

			case 'license-purchase':

				$url = add_query_arg(
					array(
						'utm_source' => 'afl_wc_utm_plugin',
						'utm_medium' => 'plugin_admin'
					),
					'https://www.appfromlab.com/product/woocommerce-utm-tracker-plugin/'
				);
				break;

			case 'network-settings':

				$query = AFL_WC_UTM_UTIL::merge_default_without_blank($query, array(
					'page' => 'afl-wc-utm-network-settings'
				));

				$url = add_query_arg($query, network_home_url('wp-admin/admin.php'));
				break;

			default:
				$url = '';
				break;
		endswitch;

		return esc_url($url);
	}

	/**
	 * @since    2.0.0
	 */
	public static function get_img_url($file_name){
		return esc_url(AFL_WC_UTM_URL_ADMIN . 'img/' . $file_name);
	}

	/**
	 * @since    2.3.0
	 */
	public static function is_our_admin_page(){
		$screen = get_current_screen();

		if ( strpos($screen->id, 'afl-utm-tracker_page_') === 0 ) {
			return true;
		} elseif ( strpos($screen->id, 'toplevel_page_afl-wc-utm') === 0 ) {
			return true;
		} elseif ( strpos($screen->id, 'settings_page_afl-wc-utm_') === 0 ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * @since    2.3.0
	 */
	public static function admin_body_class($body_class){
    if (self::is_our_admin_page()) {
    	$body_class .= ' afl-wc-utm-admin-page';
    }

		return $body_class;
  }

	/**
	 * @since    2.3.0
	 */
	public static function embed_nav_header(){
		if (self::is_our_admin_page()) {
			remove_all_actions( 'admin_notices' );
    	include AFL_WC_UTM_DIR_ADMIN . 'views/admin-nav-header.php';
		}
  }

	/**
	 * @since    2.3.0
	 */
	public static function admin_page_notice(){

		if (!AFL_WC_UTM_LICENSE_MANAGER::is_license_active()) :

			$html = <<<'EOT'
<div class="tw-border tw-border-solid tw-border-red-600 tw-bg-white tw-p-5">
	<div class="tw-px-4 tw-py-2 tw-bg-red-600 tw-inline-block"><h3 class="tw-text-white tw-my-0">%1$s</h3></div>
	<div class="tw-mt-4">Please <a href="%2$s">activate your license</a> or <a href="%3$s" target="_blank" rel="noreferrer noopener">purchase a license</a> from our website to access features, plugin updates and security fixes.</div>
</div>
EOT;

			printf($html,
				esc_html(AFL_WC_UTM_LICENSE_MANAGER::is_license_activated() ? 'License Inactive / Expired' : 'Unlicensed Plugin'),
				self::get_url('license'),
				self::get_url('license-purchase')
			);

		endif;

  }

}
