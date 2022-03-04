<?php defined( 'ABSPATH' ) || exit;

class AFL_WC_UTM_ADMIN_REPORTS_CONTROLLER
{

  public static function page_reports(){

		try {

      if (!current_user_can('afl_wc_utm_admin_view_reports')) :
        include AFL_WC_UTM_DIR_ADMIN . 'views/admin-no-permission.php';
        return;
      endif;

			if (isset($_GET['tab'])) :
				switch($_GET['tab']):
          case 'registered':

            self::tab_registered();
            break;

          case 'gravityforms':

            self::tab_gravityforms();
            break;

					case 'woocommerce':

						self::tab_woocommerce();
						break;

					case 'user-report':

						self::tab_user_report();
						break;

					default:

						self::tab_active();
						break;

				endswitch;
			else:

				self::tab_active();

			endif;

		} catch (\Exception $e) {

		}

	}

	public static function tab_active(){

    $active_attribution_setting = AFL_WC_UTM_SERVICE::get_site_settings('active_attribution');

    if (!empty($active_attribution_setting)) :
      $table = new AFL_WC_UTM_WORDPRESS_USER_ACTIVE_LIST_TABLE();
  		$table->prepare_items();
    endif;

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
    include AFL_WC_UTM_DIR_ADMIN . 'views/reports/active.php';

	}

	public static function tab_registered(){

		$table = new AFL_WC_UTM_WORDPRESS_USER_REGISTERED_LIST_TABLE();
		$table->prepare_items();

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
		include AFL_WC_UTM_DIR_ADMIN . 'views/reports/registered.php';

	}

	public static function tab_user_report(){

		if (isset($_GET['user_id'])) :
			$user_id = absint(wp_unslash($_GET['user_id']));

      if (!is_user_member_of_blog($user_id)) :
        include AFL_WC_UTM_DIR_ADMIN . 'views/admin-no-permission.php';
        return;
      endif;

      $user = get_user_by('ID', $user_id);

      include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
			include AFL_WC_UTM_DIR_ADMIN . 'views/reports/user-report.php';
		endif;

	}

  /**
	 * @since    2.4.0
	 */
  public static function tab_woocommerce(){

    if (!AFL_WC_UTM_UTIL::is_plugin_installed('woocommerce')) :
      return;
    endif;

		$table = new AFL_WC_UTM_WOOCOMMERCE_ORDER_LIST_TABLE();
		$table->prepare_items();

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
		include AFL_WC_UTM_DIR_ADMIN . 'views/reports/woocommerce.php';

	}

  /**
	 * @since    2.4.6
	 */
  public static function tab_gravityforms(){

    if (!AFL_WC_UTM_UTIL::is_plugin_installed('gravityforms')) :
      return;
    endif;

		$table = new AFL_WC_UTM_GRAVITYFORMS_LIST_TABLE();
		$table->prepare_items();

    include AFL_WC_UTM_DIR_ADMIN . 'views/admin-notice.php';
		include AFL_WC_UTM_DIR_ADMIN . 'views/reports/gravityforms.php';

	}

}
