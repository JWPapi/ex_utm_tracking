<?php defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.appfromlab.com
 * @since      1.0.0
 *
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Afl_Wc_Utm
 * @subpackage Afl_Wc_Utm/includes
 * @author     Appfromlab <hello@appfromlab.com>
 */
class AFL_WC_UTM_DEACTIVATOR {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		AFL_WC_UTM_LICENSE_MANAGER::delete_schedule_check_license_status();
		
	}

}
