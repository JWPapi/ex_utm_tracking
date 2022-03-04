<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_FLUENTFORM_SESSION
{

  const DEFAULT_SESSION = array(
    'form_id' => '',
    'user_id' => '',
    'date_created_timestamp' => '',
    'user_synced_session' => ''
  );

  private static $instance;
  private $session = self::DEFAULT_SESSION;

  protected function __construct()
  {

  }

  public static function instance()
  {
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function setup($form_id){

    $user_id = get_current_user_id();

    //already cache
    if (
      isset($this->session['form_id']) && $form_id > 0 && $form_id == $this->session['form_id']
      && isset($this->session['user_id']) && $user_id === $this->session['user_id']
      ) :
      return true;
    endif;

    $date_created_timestamp = time();

    //prepare session
    $instance_session = AFL_WC_UTM_SESSION::instance();
    $instance_session->setup($user_id);

    $user_synced_session = $instance_session->get('user_synced_session');
    $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_lag($user_synced_session, $date_created_timestamp);
    $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_type($user_synced_session, AFL_WC_UTM_FLUENTFORM::get_form_conversion_type($form_id));

    $this->session = AFL_WC_UTM_UTIL::merge_default(array(
      'form_id' => $form_id,
      'user_id' => $user_id,
      'date_created_timestamp' => $date_created_timestamp,
      'user_synced_session' => $user_synced_session
    ), self::DEFAULT_SESSION);

    return true;
  }

  public function get($key){

    if (isset($this->session[$key])) :
      return $this->session[$key];
    else:
      return null;
    endif;

  }

  public function clear(){
    $this->session = self::DEFAULT_SESSION;
  }

}
