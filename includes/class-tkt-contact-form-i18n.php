<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Contact_Form
 * @subpackage Tkt_Contact_Form/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @todo Justify why we need this or remove it. AFAIK nothing can be done with textdomains else than loading it.
 *       This, if true, makes this class a total waste of code.
 *
 * @since      1.0.0
 * @package    Tkt_Contact_Form
 * @subpackage Tkt_Contact_Form/includes
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Contact_Form_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tkt-contact-form',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
