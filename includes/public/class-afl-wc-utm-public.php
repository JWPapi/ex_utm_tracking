<?php defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.appfromlab.com
 * @since      1.0.0
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/public
 * @author     Appfromlab <hello@appfromlab.com>
 */
class AFL_WC_UTM_PUBLIC {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {

	}

	public static function register_hooks(){

		add_action( 'wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts') );
		add_action( 'login_enqueue_scripts', array(__CLASS__, 'enqueue_scripts') );

    //add_action( 'wp', 'AFL_WC_UTM_PUBLIC::check_cookie_consent' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_scripts() {

		try {
			
			$plugin_dir_url = plugin_dir_url( AFL_WC_UTM_PLUGIN_FILE ) . 'public/';

			$dependancies = array(
				'jquery'
			);

			$is_wp_consent_api_installed = AFL_WC_UTM_SERVICE::is_wp_consent_api_installed();

			if ($is_wp_consent_api_installed) :
				$dependancies[] = 'wp-consent-api';
			endif;

			$dependancies = apply_filters('afl_wc_utm_public_js_dependencies', $dependancies);

			wp_enqueue_script( 'afl-wc-utm-public', $plugin_dir_url . 'js/afl-wc-utm-public.min.js', $dependancies, AFL_WC_UTM_VERSION, true);
			wp_localize_script( 'afl-wc-utm-public', 'afl_wc_utm_public', array(
				'ajax_url' => AFL_WC_UTM_AJAX::get_ajax_url(),
				'action' => AFL_WC_UTM_AJAX::ACTION_VIEW,
				'nonce' => is_user_logged_in() ? AFL_WC_UTM_AJAX::create_nonce() : '',
				'cookie_prefix' => AFL_WC_UTM_SERVICE::get_cookie_prefix(),
				'cookie_expiry' => array(
					'days' => AFL_WC_UTM_SERVICE::get_site_settings('cookie_attribution_window')
				),
				'cookie_renewal' => AFL_WC_UTM_SERVICE::get_site_settings('cookie_renewal'),
				'cookie_consent_category' => AFL_WC_UTM_SERVICE::get_site_settings('cookie_consent_category'),
				'domain_info' => AFL_WC_UTM_SERVICE::get_domain_info(),
				'last_touch_window' => intval(AFL_WC_UTM_SERVICE::get_site_settings('cookie_last_touch_window')) * 60,
				'wp_consent_api_enabled' => $is_wp_consent_api_installed,
				'user_has_active_attribution' => AFL_WC_UTM_WORDPRESS_USER::has_active_attribution(get_current_user_id()) ? 1 : 0
			));

		} catch (\Exception $e) {

		}

	}

	public static function check_cookie_consent(){
		global $current_user;

		//no consent
		if (isset($_COOKIE['wp_consent_' . AFL_WC_UTM_SERVICE::get_site_settings('cookie_consent_category')]) && !AFL_WC_UTM_SERVICE::has_cookie_consent()) :

				if (!empty($current_user->ID)) :
					AFL_WC_UTM_WORDPRESS_USER::delete_active_session($current_user->ID);
				endif;

	      //delete cookies
	      AFL_WC_UTM_SERVICE::delete_cookies();
		endif;

	}

}
