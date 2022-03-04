<?php defined( 'ABSPATH' ) || exit;

/**
 * @since 2.0.0
 */
class AFL_WC_UTM_INSTALL {

  private static $db_updates = array();

  public static function register_hooks(){

  }

  public static function check_version(){
    if ( version_compare( get_option( 'afl_wc_utm_version', 0 ), AFL_WC_UTM::VERSION, '<' ) ) {
      self::install();
    }
  }

 public static function install(){

   if ( ! is_blog_installed() ) {
     return;
   }

   // Check if we are not already running this routine.
   if ( 'yes' === get_transient( 'afl_wc_utm_installing' ) ) {
     return;
   }

   //10 minutes
   set_transient( 'afl_wc_utm_installing', 'yes', 600 );

   self::create_options();
   self::add_capabilities();
   self::maybe_update_db_version();
   self::update_plugin_version();

   delete_transient( 'afl_wc_utm_installing' );

 }

 private static function update() {
   $current_db_version = get_option( 'afl_wc_utm_db_version' );

   foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) :
     if ( version_compare( $current_db_version, $version, '<' ) ) :
       foreach ( $update_callbacks as $callback ) :
         AFL_WC_UTM_UPDATE::$callback();
       endforeach;
     endif;
   endforeach;

 }

 public static function create_options(){

   //create settings
   AFL_WC_UTM_SETTINGS::install();

 }

 private static function update_plugin_version() {
   delete_option( 'afl_wc_utm_version' );
   add_option( 'afl_wc_utm_version', AFL_WC_UTM::VERSION );
 }

 public static function get_db_update_callbacks() {
   return self::$db_updates;
 }

 public static function needs_db_update() {

   $current_db_version = get_option( 'afl_wc_utm_db_version', null );
   $updates            = self::get_db_update_callbacks();
   $update_versions    = array_keys( $updates );
   usort( $update_versions, 'version_compare' );

   return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
 }

 public static function maybe_update_db_version() {

   if ( self::needs_db_update() ) {
     self::update();
   } else {
     self::update_db_version();
   }
 }

 public static function update_db_version( $version = null ) {
   delete_option( 'afl_wc_utm_db_version' );
   add_option( 'afl_wc_utm_db_version', is_null( $version ) ? AFL_WC_UTM::VERSION : $version );
 }

 public static function add_capabilities(){
   global $wp_roles;

   if ( ! class_exists( 'WP_Roles' ) ) {
     return;
   }

   if ( ! isset( $wp_roles ) ) {
     $wp_roles = new WP_Roles();
   }

   $capabilities = self::get_core_capabilities();

   foreach ($capabilities as $cap) {
     $wp_roles->add_cap('administrator', $cap);
   }

 }

 public static function remove_capabilities(){
   global $wp_roles;

   if ( ! class_exists( 'WP_Roles' ) ) {
     return;
   }

   if ( ! isset( $wp_roles ) ) {
     $wp_roles = new WP_Roles();
   }

   $capabilities = self::get_core_capabilities();

   foreach ($capabilities as $cap) {
     $wp_roles->remove_cap('administrator', $cap);
   }

 }

 public static function get_core_capabilities(){

   $capabilities = array(
     'afl_wc_utm_admin_view',
     'afl_wc_utm_admin_view_reports',
     'afl_wc_utm_admin_manage_settings'
   );

   return $capabilities;
 }

}
