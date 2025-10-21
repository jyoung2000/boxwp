<?php
/**
 * Plugin Uninstall Handler
 *
 * Handles cleanup when the plugin is uninstalled.
 *
 * @package BoxAISearch
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall.
 */
function bas_uninstall_cleanup() {
	global $wpdb;

	// Delete plugin options.
	delete_option( 'bas_box_client_id' );
	delete_option( 'bas_box_client_secret' );
	delete_option( 'bas_box_enterprise_id' );
	delete_option( 'bas_box_public_key_id' );
	delete_option( 'bas_box_private_key' );
	delete_option( 'bas_box_passphrase' );
	delete_option( 'bas_cache_expiration' );

	// Delete transients (access token).
	delete_transient( 'bas_box_access_token' );

	// Delete all cached search results.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_bas_cache_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_bas_cache_' ) . '%'
		)
	);

	// Clear object cache.
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_delete_group( 'bas_cache' );
	}
}

// Run cleanup.
bas_uninstall_cleanup();
