<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://shipsy.io/
 * @since             1.0.0
 * @package           Jeebly_Shipping
 *
 * @wordpress-plugin
 * Plugin Name:       Jeebly Shipment Delivery
 * Plugin URI:        
 * Description:       Jeebly connects your store to help you with, syncing orders to Jeebly application, print AWB labels and track your packages.
 * Version:           1.0.3
 * Author:            shipsyplugins
 * Author URI:        https://shipsy.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jeebly-shipping
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SHIPSY_ECONNECT_VERSION', '1.0.3' );
define( 'SHIPSY_ECONNECT_URL', plugin_dir_url( __FILE__ ) );
define( 'SHIPSY_ECONNECT_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shipsy-econnect-activator.php
 */
function activate_shipsy_econnect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipsy-econnect-activator.php';
	$activator = new Shipsy_Econnect_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shipsy-econnect-deactivator.php
 */
function deactivate_shipsy_econnect() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipsy-econnect-activator.php';
	$activator = new Shipsy_Econnect_Activator();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipsy-econnect-deactivator.php';
	$deactivator = new Shipsy_Econnect_Deactivator( $activator );
	$deactivator->deactivate();

}

register_activation_hook( __FILE__, 'activate_shipsy_econnect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shipsy-econnect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_shipsy_econnect() {
	$plugin = new Shipsy_Econnect();
	$plugin->run();

}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	run_shipsy_econnect();
}
