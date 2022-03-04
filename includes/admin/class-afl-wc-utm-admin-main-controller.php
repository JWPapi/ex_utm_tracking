<?php defined( 'ABSPATH' ) || exit;

class AFL_WC_UTM_ADMIN_MAIN_CONTROLLER
{

  public static function page(){

		try {

      if (!current_user_can('afl_wc_utm_admin_view')) :
        include AFL_WC_UTM_DIR_ADMIN . 'views/admin-no-permission.php';
        return;
      endif;

		} catch (AFL_WC_UTM_ERROR $e) {

			$afl_alert = new AFL_WC_UTM_ALERT;
			$afl_alert->add_error_message($e->getMessage());

		} catch (\Exception $e) {

			$afl_alert = new AFL_WC_UTM_ALERT;
			$afl_alert->add_error_message(__('Unknown error.', AFL_WC_UTM_TEXTDOMAIN));

		}

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
		include AFL_WC_UTM_DIR_ADMIN . 'views/admin-main.php';

	}

}
