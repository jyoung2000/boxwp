<?php
/**
 * Plugin Deactivation Class
 *
 * Handles plugin deactivation.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

/**
 * Deactivator class.
 *
 * Defines all code to run during plugin deactivation.
 *
 * @since 1.0.0
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear cached access token.
		delete_transient( 'bas_box_access_token' );

		// Optionally clear all cached search results.
		// Uncomment the following if you want to clear cache on deactivation:
		// $cache = new Cache();
		// $cache->clear_all();
	}
}
