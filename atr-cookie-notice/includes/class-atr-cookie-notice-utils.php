<?php

/**
 * Utilities for the plugin
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 * @package    Atr_Cookie_Notice
 * @subpackage Atr_Cookie_Notice/includes
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */

// Security: Abort if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility class for the plugin.
 * Contains helper functions that can be reused across the plugin.
 */
class Atr_Cookie_Notice_Utils {
	/**
	 * Plugin slug / option name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get all plugin options as an array (empty array if none).
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_all_options() {
		$options = get_option( $this->plugin_name, array() );
		return is_array( $options ) ? $options : array();
	}

	/**
	 * Get a specific setting value with a default fallback.
	 *
	 * @since 1.0.0
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_setting( $key, $default = null ) {
		$options = $this->get_all_options();
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Simple debug logger (honors WP_DEBUG).
	 *
	 * @since 1.0.0
	 * @param string $message Message to log.
	 * @param mixed  $data    Optional data to append.
	 * @return void
	 */
	public function log_debug( $message, $data = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( null !== $data ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Debug logging only when WP_DEBUG is true
				$message .= ' ' . print_r( $data, true );
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is true
			error_log( '[ATR Cookie Notice] ' . $message );
		}
	}
}
