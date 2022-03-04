<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_UPDATES
{

  public static function update_200_capabilities(){

    AFL_WC_UTM_INSTALL::remove_capabilities();
    AFL_WC_UTM_INSTALL::add_capabilities();

  }

  public static function update_200_db_version(){

    AFL_WC_UTM_INSTALL::update_db_version( '2.0.0' );

  }

}
