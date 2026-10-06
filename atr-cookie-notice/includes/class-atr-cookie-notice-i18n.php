<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/includes
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Cookie_Notice_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * WordPress 4.6+ automatically loads translations for plugins hosted on
	 * WordPress.org, so we intentionally avoid calling load_plugin_textdomain()
	 * to satisfy Plugin Check. Keeping a no-op method for backward compatibility
	 * with the loader hooks.
	 *
	 * @since    1.0.0
	 */
	public function atr_cookie_notice_load_plugin_textdomain() {
		// No-op: WP >= 4.6 auto-loads translations from the languages directory.
	}



}
