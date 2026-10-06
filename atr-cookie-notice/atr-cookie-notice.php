<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://atarimtr.co.il
 * @since             1.0.0
 * @package           Atr_Cookie_Notice
 *
 * @wordpress-plugin
 * Plugin Name:       ATR Cookie Notice
 * Plugin URI:        https://atarimtr.co.il
 * Description:       Cookie consent banner. Handles Essential, Analytics, and Marketing cookies with consent management.
 * Version:           1.2.0
 * Author:            Yehuda Tiram
 * Author URI:        https://atarimtr.co.il/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       atr-cookie-notice
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ATR_COOKIE_NOTICE_VERSION', '1.2.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-atr-cookie-notice-activator.php
 */
function atr_cookie_notice_activate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-atr-cookie-notice-activator.php';
	Atr_Cookie_Notice_Activator::atr_cookie_notice_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-atr-cookie-notice-deactivator.php
 */
function atr_cookie_notice_deactivate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-atr-cookie-notice-deactivator.php';
	Atr_Cookie_Notice_Deactivator::atr_cookie_notice_deactivate();
}

register_activation_hook( __FILE__, 'atr_cookie_notice_activate_plugin' );
register_deactivation_hook( __FILE__, 'atr_cookie_notice_deactivate_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-atr-cookie-notice.php';

/**
 * Targeted cache purge + asset buster handler (admin-post).
 * Global function to avoid class loading/version mismatches.
 */
function atr_cookie_notice_purge_caches_handler() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions.', 'atr-cookie-notice' ) );
    }

    // Nonce verification
    if ( function_exists( 'check_admin_referer' ) ) {
        check_admin_referer( 'atr_cookie_notice_purge_caches' );
    } else {
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'atr_cookie_notice_purge_caches' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'atr-cookie-notice' ) );
        }
    }

    // Bump asset buster
    $slug    = 'atr-cookie-notice';
    $options = get_option( $slug, array() );
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    $options['asset_buster'] = time();
    update_option( $slug, $options );

    // Flush caches where available (guarded)
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { call_user_func( 'w3tc_flush_all' ); }
    if ( function_exists( 'rocket_clean_domain' ) ) { call_user_func( 'rocket_clean_domain' ); }
    if ( function_exists( 'wp_cache_clear_cache' ) ) { @call_user_func( 'wp_cache_clear_cache' ); }
    if ( class_exists( 'autoptimizeCache' ) ) { try { call_user_func( array( 'autoptimizeCache', 'clearall' ) ); } catch ( \Throwable $e ) {} }

    // Broad action hooks
    do_action( 'litespeed_purge_all' );
    do_action( 'sg_cachepress_purge_cache' );
    do_action( 'cloudflare_purge_everything' );
    do_action( 'hummingbird_clear_cache' );
    do_action( 'swift_performance_clear_all_cache' );
    do_action( 'wp_fastest_cache_clear_all' );
    do_action( 'nginx_helper_purge_all' );
    do_action( 'comet_cache\\clear' );
    do_action( 'cache_enabler_clear_complete_cache' );
    do_action( 'kinsta_cache_purge_site' );
    do_action( 'autoptimize_action_cachepurged' );

    // Redirect back to plugin settings
    $redirect = add_query_arg( array( 'page' => 'atr-cookie-notice', 'atr_cnpurged' => '1' ), admin_url( 'admin.php' ) );
    wp_safe_redirect( $redirect );
    exit;
}

add_action( 'admin_post_atr_cookie_notice_purge_caches', 'atr_cookie_notice_purge_caches_handler' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function atr_cookie_notice_run_plugin() {

	$plugin = new Atr_Cookie_Notice();
	$plugin->atr_cookie_notice_run();

}
atr_cookie_notice_run_plugin();
