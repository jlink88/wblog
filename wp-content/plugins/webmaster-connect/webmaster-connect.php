<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wc.relj.in/
 * @since             1.0.0
 * @package           Webmaster_Connect
 *
 * @wordpress-plugin
 * Plugin Name:       Webmaster Connect
 * Plugin URI:        http://wc.relj.in/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            rel
 * Author URI:        http://wc.relj.in/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webmaster-connect
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webmaster-connect-activator.php
 */
function activate_webmaster_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webmaster-connect-activator.php';
	Webmaster_Connect_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webmaster-connect-deactivator.php
 */
function deactivate_webmaster_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webmaster-connect-deactivator.php';
	Webmaster_Connect_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_webmaster_connect' );
register_deactivation_hook( __FILE__, 'deactivate_webmaster_connect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-webmaster-connect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webmaster_connect() {

	$plugin = new Webmaster_Connect();
	$plugin->run();

}
run_webmaster_connect();
