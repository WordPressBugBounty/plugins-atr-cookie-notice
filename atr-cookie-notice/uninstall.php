<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * @package    Atr_Cookie_Notice
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the plugin's options for the current site.
 *
 * @since 1.3.0
 * @return void
 */
function atr_cookie_notice_uninstall_site() {
	delete_option( 'atr-cookie-notice' );
	delete_option( 'atr_cookie_notice_activation_time' );
	delete_option( 'atr_cookie_notice_dismiss_review' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $atr_cookie_notice_site_id ) {
		switch_to_blog( $atr_cookie_notice_site_id );
		atr_cookie_notice_uninstall_site();
		restore_current_blog();
	}
} else {
	atr_cookie_notice_uninstall_site();
}

// Legacy per-user flag from older versions (user meta is network-wide).
delete_metadata( 'user', 0, 'atr_cookie_notice_notice_dismissed', '', true );
