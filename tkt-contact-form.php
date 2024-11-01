<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.tukutoi.com/
 * @since             1.0.0
 * @package           Tkt_Contact_Form
 *
 * @wordpress-plugin
 * Plugin Name:       TukuToi Contact Form
 * Plugin URI:        https://www.tukutoi.com/program/tukutoi-contact-form/
 * Description:       A simple Contact Form for WordPress.
 * Version:           2.2.0
 * Author:            bedas
 * Requires at least: 4.9.15
 * Tested up to:      5.8
 * Author URI:        https://www.tukutoi.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tkt-contact-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TKT_CONTACT_FORM_VERSION', '2.2.0' );

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-tkt-contact-form-activator.php
 * Full security checks are performed inside the class.
 */
function tkt_cntct_frm_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tkt-contact-form-activator.php';
	Tkt_Contact_Form_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-tkt-contact-form-deactivator.php
 * Full security checks are performed inside the class.
 */
function tkt_cntct_frm_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tkt-contact-form-deactivator.php';
	Tkt_Contact_Form_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'tkt_cntct_frm_activate' );
register_deactivation_hook( __FILE__, 'tkt_cntct_frm_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tkt-contact-form.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Generally you will want to hook this function, instead of callign it globally.
 * However since the purpose of your plugin is not known until you write it, we include the function globally.
 *
 * @since    1.0.0
 */
function tkt_cntct_frm_run() {

	$plugin = new Tkt_Contact_Form();
	$plugin->run();

}
tkt_cntct_frm_run();
