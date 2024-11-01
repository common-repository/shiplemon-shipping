<?php
/**
 * Plugin Name: Shiplemon shipping
 * Plugin URI: https://wordpress.org/plugins/shiplemon-shipping/
 * Description: Shipping made easy. Just install the plugin and let us do the rest.
 * Version: 1.0.0
 * Author: shiplemon
 * Author URI: https://www.shiplemon.com/
 * Text Domain: shiplemon-shipping
 * Requires at least: 5.3
 * Requires PHP: 7.0
 * License: GPLV3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * @package Shiplemon
 * @version 1.0.0
 * @since  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Not a place for you!' );
}

/**
 * Checks if WooCommerce is active and bailout early
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Define plugin specific constants
 */
define( 'SHIPLEMON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHIPLEMON_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHIPLEMON_PLUGIN_ROOT', __FILE__ );

require_once 'includes/shipping-method-hooks.php';
require_once 'includes/shiplemon-helpers.php';

// Load plugin textdomain.
add_action( 'plugins_loaded', 'shiplemon_load_textdomain' );
/**
 *  Load plugin translation
 *
 *  @return void
 */
function shiplemon_load_textdomain() {
	load_plugin_textdomain( 'shiplemon_shippping', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
