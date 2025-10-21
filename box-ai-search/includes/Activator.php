<?php
/**
 * Plugin Activation Class
 *
 * Handles plugin activation.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

/**
 * Activator class.
 *
 * Defines all code to run during plugin activation.
 *
 * @since 1.0.0
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), '6.4', '<' ) ) {
			deactivate_plugins( BAS_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Box AI Search requires WordPress 6.4 or higher. Please update WordPress.', 'box-ai-search' ),
				esc_html__( 'Plugin Activation Error', 'box-ai-search' ),
				array( 'back_link' => true )
			);
		}

		// Check PHP version.
		if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
			deactivate_plugins( BAS_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Box AI Search requires PHP 8.0 or higher. Please upgrade PHP.', 'box-ai-search' ),
				esc_html__( 'Plugin Activation Error', 'box-ai-search' ),
				array( 'back_link' => true )
			);
		}

		// Check if OpenSSL is available.
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			deactivate_plugins( BAS_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Box AI Search requires OpenSSL PHP extension for encryption. Please install OpenSSL.', 'box-ai-search' ),
				esc_html__( 'Plugin Activation Error', 'box-ai-search' ),
				array( 'back_link' => true )
			);
		}

		// Set default options.
		if ( false === get_option( 'bas_cache_expiration' ) ) {
			add_option( 'bas_cache_expiration', 3600 );
		}

		// Create a flag to show the setup notice.
		set_transient( 'bas_activation_notice', true, 30 );
	}
}
