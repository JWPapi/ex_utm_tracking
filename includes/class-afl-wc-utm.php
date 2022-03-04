<?php defined( 'ABSPATH' ) || exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.appfromlab.com
 * @since      1.0.0
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/includes
 * @author     Appfromlab <hello@appfromlab.com>
 */
class AFL_WC_UTM {

	const VERSION = '2.4.12';

	private static $instance;

	public static function get_instance(){

    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	private function __construct() {

	}

	/**
	 * @since 2.0.0
	 */
	private function define_constants(){

		define( 'AFL_WC_UTM_VERSION', AFL_WC_UTM::VERSION );
		define( 'AFL_WC_UTM_TEXTDOMAIN', 'afl-wc-utm' );
		define( 'AFL_WC_UTM_PLUGIN_BASENAME', plugin_basename(AFL_WC_UTM_PLUGIN_FILE) );
		define( 'AFL_WC_UTM_DIR_PLUGIN', dirname(AFL_WC_UTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR );
		define( 'AFL_WC_UTM_DIR_INCLUDES', AFL_WC_UTM_DIR_PLUGIN . 'includes' . DIRECTORY_SEPARATOR );
		define( 'AFL_WC_UTM_DIR_ADMIN', AFL_WC_UTM_DIR_PLUGIN . 'admin' . DIRECTORY_SEPARATOR );
		define( 'AFL_WC_UTM_DIR_PUBLIC', AFL_WC_UTM_DIR_PLUGIN . 'public' . DIRECTORY_SEPARATOR );

		define( 'AFL_WC_UTM_URL_PLUGIN', plugin_dir_url(AFL_WC_UTM_PLUGIN_FILE) );
		define( 'AFL_WC_UTM_URL_ADMIN', AFL_WC_UTM_URL_PLUGIN . 'admin/' );

	}

	/**
	 * Run the this plugin
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$this->define_constants();
		$this->register_hooks();

	}

	/**
	 * @since 2.3.0
	 */
	private function register_hooks(){

		register_activation_hook( AFL_WC_UTM_PLUGIN_FILE, 'AFL_WC_UTM_INSTALL::install' );
		register_deactivation_hook( AFL_WC_UTM_PLUGIN_FILE, 'AFL_WC_UTM_DEACTIVATOR::deactivate' );

		add_action('plugins_loaded', 'AFL_WC_UTM_LICENSE_MANAGER::register_hooks');
		add_action('plugins_loaded', 'AFL_WC_UTM_WORDPRESS_USER::register_hooks');
		add_action('plugins_loaded', 'AFL_WC_UTM_ADMIN::register_hooks');
		add_action('plugins_loaded', 'AFL_WC_UTM_PUBLIC::register_hooks');
		add_action('plugins_loaded', 'AFL_WC_UTM_AJAX::register_hooks');
		add_action('plugins_loaded', 'AFL_WC_UTM::on_loaded');

		add_action('woocommerce_loaded', 'AFL_WC_UTM_WOOCOMMERCE_ORDER::register_hooks');
		add_action('gform_loaded', 'AFL_WC_UTM_GRAVITYFORMS::register_hooks', 5);
		add_action('fluentform_loaded', 'AFL_WC_UTM_FLUENTFORM::register_hooks');

		add_action('init', 'AFL_WC_UTM::init');

		add_filter('wp_consent_api_registered_afl-wc-utm', '__return_true');

	}

	/**
	 * @since 2.0.0
	 */
	public static function init(){

		AFL_WC_UTM_INSTALL::check_version();

		$network_settings = is_multisite() ? AFL_WC_UTM_NETWORK_SETTINGS::get() : array();

		AFL_WC_UTM_WORDPRESS_USER::init(array(
			'global_user_option' => !empty($network_settings['cookie_domain']) ? true : false
		));

		$site_settings = AFL_WC_UTM_SETTINGS::get();

		AFL_WC_UTM_SERVICE::init(array(
			'site_settings' => $site_settings
		));

		AFL_WC_UTM_LICENSE_MANAGER::init(array(
			'software_type' => 'plugin',
			'software_slug' => 'afl-wc-utm',
			'software_version' => AFL_WC_UTM_VERSION,
			'software_basename' => AFL_WC_UTM_PLUGIN_BASENAME,
			'api_base_url' => 'https://www.appfromlab.com/wp-json/appfromlab/license-manager/v1/'
			//'api_base_url' => 'http://afl-wc-utm.test/wp-json/appfromlab/license-manager/v1/'
		));

	}

	public static function on_loaded(){
		do_action('afl_wc_utm_loaded');
	}

}
