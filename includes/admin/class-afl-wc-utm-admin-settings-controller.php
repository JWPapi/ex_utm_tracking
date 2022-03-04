<?php defined( 'ABSPATH' ) || exit;

class AFL_WC_UTM_ADMIN_SETTINGS_CONTROLLER
{

  public static function page_settings(){

		try {

      //check permission
      if (!current_user_can('afl_wc_utm_admin_manage_settings')) :
        include AFL_WC_UTM_DIR_ADMIN . 'views/admin-no-permission.php';
        return;
      endif;

			$afl_alert = new AFL_WC_UTM_ALERT;

			if (isset($_POST['form']) && $_POST['form'] === 'afl_wc_utm_admin_form_save_settings') :

				$afl_form_values = wp_unslash($_POST);

				//check nonce
				if (!wp_verify_nonce($_POST['_wpnonce'], Afl_WC_UTM_SETTINGS::ACTION_SAVE)) :
					throw new AFL_WC_UTM_ERROR(__('Your session has expired. Please refresh page.', AFL_WC_UTM_TEXTDOMAIN));
				endif;

				$afl_save_result = AFL_WC_UTM_SETTINGS::save($afl_form_values);

				if (is_wp_error($afl_save_result)) :
					throw new AFL_WC_UTM_ERROR($afl_save_result->get_error_message());
				endif;

				$afl_alert->add_success_message(__('Settings has been saved. Please clear your page cache.', AFL_WC_UTM_TEXTDOMAIN));

			endif;

			$afl_form_values = AFL_WC_UTM_SETTINGS::get();

		} catch (AFL_WC_UTM_ERROR $e) {

			$afl_alert = new AFL_WC_UTM_ALERT;
			$afl_alert->add_error_message($e->getMessage());

		} catch (\Exception $e) {

			$afl_alert = new AFL_WC_UTM_ALERT;
			$afl_alert->add_error_message(__('Unknown error.', AFL_WC_UTM_TEXTDOMAIN));

		}

    $setting_permissions = AFL_WC_UTM_SETTINGS::get_setting_permissions();

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
		include AFL_WC_UTM_DIR_ADMIN . 'views/admin-settings.php';

	}

  public static function page_license(){

    //check permission
    if (!current_user_can('manage_options')) :
      include AFL_WC_UTM_DIR_ADMIN . 'views/admin-no-permission.php';
      return;
    endif;

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-license.php';

  }

}
