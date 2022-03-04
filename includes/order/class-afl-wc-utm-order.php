<?php defined( 'ABSPATH' ) || exit;

if (!class_exists('AFL_WC_UTM_ORDER', false)) {

  /**
   * @deprecated
   */
  class AFL_WC_UTM_ORDER
  {

    private static $instance;

    public static function get_instance()
    {
      _deprecated_function('AFL_WC_UTM_ORDER::get_instance', '2.0.0');

      if ( is_null( self::$instance ) )
      {
        self::$instance = new self();
      }

      return self::$instance;
    }

  }

}
