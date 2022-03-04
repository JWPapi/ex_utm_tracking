<?php

/*
Plugin Name: Ex Utm Tracking
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0.1
Author: julian
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

if ( ! defined( 'AFL_WC_UTM_PLUGIN_FILE' ) ) {
  define( 'AFL_WC_UTM_PLUGIN_FILE', __FILE__ );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-afl-wc-utm-autoloader.php';

/**s
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
AFL_WC_UTM::get_instance()->run();

include('utm_api.php'); // API

