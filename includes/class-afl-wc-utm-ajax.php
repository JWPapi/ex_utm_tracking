<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 1.2.6
 */
class AFL_WC_UTM_AJAX
{

  const ACTION_VIEW = 'afl_wc_utm_view';

  public static function get_instance(){
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct(){

  }

  public static function register_hooks(){

    add_action('wp_ajax_afl_wc_utm_view', array(__CLASS__, 'ajax_afl_wc_utm_view_login'));
    add_action('wp_ajax_nopriv_afl_wc_utm_view', array(__CLASS__, 'ajax_afl_wc_utm_view'));

  }

  public static function get_ajax_url(){
    return admin_url( 'admin-ajax.php' );
  }

  public static function create_nonce(){
    return wp_create_nonce(self::ACTION_VIEW);
  }

  public static function verify_nonce($nonce){
    return wp_verify_nonce($nonce, self::ACTION_VIEW);
  }

  public static function ajax_afl_wc_utm_view_login(){

    $post_data = AFL_WC_UTM_UTIL::merge_default(wp_unslash($_POST), array(
      'action' => '',
      'nonce' => ''
    ));

    if (!self::verify_nonce($post_data['nonce'])) {
      wp_send_json(array(
        'code' => 'ERROR',
        'message' => 'Invalid nonce.'
      ), 403);
    }

    self::ajax_afl_wc_utm_view();

  }

  public static function ajax_afl_wc_utm_view(){

    $user_id = get_current_user_id();

    $user_synced_session = AFL_WC_UTM_SERVICE::get_user_synced_session($user_id);

    //check if feature enabled
    if (AFL_WC_UTM_SETTINGS::get('active_attribution')) :
      AFL_WC_UTM_WORDPRESS_USER::update_active_session($user_id, $user_synced_session);
    else:
      AFL_WC_UTM_WORDPRESS_USER::delete_active_session($user_id);
    endif;

    AFL_WC_UTM_SERVICE::set_cookies($user_synced_session);

    wp_send_json(array(
      'code' => 'SUCCESS',
      'message' => 'AFL UTM Tracker'
    ), 200);

  }

}
