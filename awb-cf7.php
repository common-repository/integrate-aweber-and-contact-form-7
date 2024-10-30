<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://darpankulkarni.in
 * @since             1.0.0
 * @package           Awb_Cf7
 *
 * @wordpress-plugin
 * Plugin Name:       Integrate AWeber and Contact Form 7
 * Plugin URI:        https://darpankulkarni.in/products/integrate-aweber-and-contact-form-7
 * Description:       Integrate AWeber and Contact Form 7. Connect your forms to lists and save submitted data directly to your AWeber account.
 * Version:           1.0.1
 * Author:            Darpan Kulkarni
 * Author URI:        https://darpankulkarni.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       awb-cf7
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'AWB_CF7_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_awb_cf7() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-awb-cf7-activator.php';
	Awb_Cf7_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_awb_cf7() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-awb-cf7-deactivator.php';
	Awb_Cf7_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_awb_cf7' );
register_deactivation_hook( __FILE__, 'deactivate_awb_cf7' );

/**
 * The core plugin class that is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-awb-cf7.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_awb_cf7() {

	$plugin = new Awb_Cf7();
	$plugin->run();

}
run_awb_cf7();
