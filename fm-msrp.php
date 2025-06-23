<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://geoffcordner.net
 * @since             1.0.0
 * @package           Fm_Msrp
 *
 * @wordpress-plugin
 * Plugin Name:       Former Model MSRP
 * Plugin URI:        https://github.com/gcordner/former-model-msrp
 * Description:       A woocommerce plugin to display MSRP on product pages for simple and variant products
 * Version:           1.0.0
 * Author:            Geoff Cordner
 * Author URI:        https://geoffcordner.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fm-msrp
 * Domain Path:       /languages
 */

use FormerModel\MSRP\Fm_Msrp;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FM_MSRP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fm-msrp-activator.php
 */
function activate_fm_msrp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fm-msrp-activator.php';
	Fm_Msrp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fm-msrp-deactivator.php
 */
function deactivate_fm_msrp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fm-msrp-deactivator.php';
	Fm_Msrp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fm_msrp' );
register_deactivation_hook( __FILE__, 'deactivate_fm_msrp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fm-msrp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fm_msrp() {
	FormerModel\MSRP\Fm_Msrp::init();
}
add_action( 'plugins_loaded', 'run_fm_msrp', 20 );
