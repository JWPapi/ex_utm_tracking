<?php defined( 'ABSPATH' ) || exit;

class AFL_WC_UTM_ALERT
{

  public $alerts = [];
  protected $display_first_message_only = true;
  protected $additional_css;

  public function add_message($message, $status){
    $this->alerts[] = ['message' => $message, 'status' => $status];
  }

  public function add_success_message($message){
    $this->add_message($message, 'success');
  }

  public function add_error_message($message){
    $this->add_message($message, 'error');
  }

  public function set_display_all_messages($bool = true){
    $this->display_first_message_only = !$bool;
  }

  public function set_additional_css($css){
    $this->additional_css = $css;
  }

  public function get_first_message(){

    if (!empty($this->alerts)) {
      $first_message = array_shift($this->alerts);

      return $first_message['message'];
    }

    return '';
  }

  public function get_last_message(){

    if (!empty($this->alerts)) {
      $last_message = array_pop($this->alerts);

      return $last_message['message'];
    }

    return '';

  }

  public function display($echo = true){
    $html = '';

    if (count($this->alerts)) :
      foreach($this->alerts as $key => $alert):

        switch($alert['status']):
          case 'success':
            $css = 'tw-bg-green-600 tw-border-green-800 tw-text-white';
            break;
          case 'error':
            $css = 'tw-bg-red-600 tw-border-red-800 tw-text-white';
            break;
          default:
            $css = 'tw-bg-blue-600 tw-border-blue-800 tw-text-white';
        endswitch;

        $html .= sprintf('<div class="tw-p-3 tw-border-solid tw-border %1$s %2$s"><b>%3$s:</b> %4$s</div>',
          esc_attr($css),
          esc_attr($this->additional_css),
          esc_html(strtoupper($alert['status'])),
          wp_kses($alert['message'], 'post')
        );

        if ($this->display_first_message_only) {
          break;
        }

      endforeach;
    endif;

    if ($echo) {
      echo $html;
    } else {
      return $html;
    }

  }

}
