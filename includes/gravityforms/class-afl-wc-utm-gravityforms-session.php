<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_GRAVITYFORMS_SESSION
{

  const DEFAULT_SESSION = array(
    'form_id' => '',
    'entry_id' => '',
    'user_id' => '',
    'date_created_timestamp' => '',
    'user_synced_session' => '',
    'hidden_fields' => ''
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

  public function setup($form, $entry){

    $form_id = rgar($form, 'id');
    $entry_id = rgar($entry, 'id');
    $user_id = rgar($entry, 'created_by');

    //check if session already exists
    if (
      isset($this->session['form_id']) && $form_id > 0 && $form_id == $this->session['form_id']
      && isset($this->session['entry_id']) && $entry_id > 0 && $entry_id == $this->session['entry_id']
      && isset($this->session['user_id']) && $user_id == $this->session['user_id']
      ) :
      return true;
    endif;

    try {

      $date_created = rgar($entry, 'date_created');
      $date_created_timestamp = $date_created ? AFL_WC_UTM_UTIL::utc_date_database_to_timestamp($date_created) : time();

    } catch (\Exception $e) {
      $date_created_timestamp = time();
    }

    //prepare session
    $instance_session = AFL_WC_UTM_SESSION::instance();
    $instance_session->setup($user_id);

    $user_synced_session = $instance_session->get('user_synced_session');
    $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_lag($user_synced_session, $date_created_timestamp);
    $user_synced_session = AFL_WC_UTM_SERVICE::prepare_conversion_type($user_synced_session, AFL_WC_UTM_GRAVITYFORMS::get_form_conversion_type($form));

    $hidden_fields = AFL_WC_UTM_GRAVITYFORMS::prepare_form_hidden_fields($form, $user_synced_session);

    $this->session = AFL_WC_UTM_UTIL::merge_default(array(
      'form_id' => $form_id,
      'entry_id' => $entry_id,
      'user_id' => $user_id,
      'date_created_timestamp' => $date_created_timestamp,
      'user_synced_session' => $user_synced_session,
      'hidden_fields' => $hidden_fields
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
