<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.4.6
 */
class AFL_WC_UTM_SESSION
{

  const DEFAULT_SESSION = array(
    'user_id' => '',
    'blog_id' => '',
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

  public function setup($user_id = 0){

    $blog_id = get_current_blog_id();

    //check if session already exists
    if (
      !empty($this->session['user_synced_session'])
      && isset($this->session['user_id']) && $user_id === $this->session['user_id']
      && isset($this->session['blog_id']) && $blog_id === $this->session['blog_id']
      ) :
      return true;
    endif;
    
    $this->session = AFL_WC_UTM_UTIL::merge_default(array(
      'user_id' => $user_id,
      'blog_id' => $blog_id,
      'user_synced_session' => AFL_WC_UTM_SERVICE::get_user_synced_session($user_id)
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
