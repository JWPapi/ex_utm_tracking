<?php defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 */
class AFL_WC_UTM_AUTOLOADER {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = trailingslashit(dirname(__FILE__));
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class Class name.
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path File path.
	 * @return bool Successful or not.
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load WC classes on demand to reduce memory consumption.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'afl_wc_utm' ) ) {
			return;
		}

		$file = $this->get_file_name_from_class( $class );
		$path = '';

		if ( 0 === strpos( $class, 'afl_wc_utm_admin' ) ) {
			$path = $this->include_path . 'admin/';
		} elseif ( 0 === strpos( $class, 'afl_wc_utm_public' ) ) {
			$path = $this->include_path . 'public/';
		} elseif ( 0 === strpos( $class, 'afl_wc_utm_wordpress' ) ) {
			$path = $this->include_path . 'wordpress/';
		} elseif ( 0 === strpos( $class, 'afl_wc_utm_woocommerce' ) ) {
			$path = $this->include_path . 'woocommerce/';
		} elseif ( 0 === strpos( $class, 'afl_wc_utm_gravityforms' ) ) {
			$path = $this->include_path . 'gravityforms/';
		} elseif ( 0 === strpos( $class, 'afl_wc_utm_fluentform' ) ) {
      $path = $this->include_path . 'fluentform/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new AFL_WC_UTM_AUTOLOADER();
