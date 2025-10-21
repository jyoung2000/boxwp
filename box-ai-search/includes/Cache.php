<?php
/**
 * Cache Class
 *
 * Handles caching of Box AI API responses using WordPress Transients.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

/**
 * Cache class.
 *
 * Provides aggressive caching of API responses using WordPress Transients API.
 * Supports both database and object caching backends.
 *
 * @since 1.0.0
 */
class Cache {

	/**
	 * Default cache expiration time in seconds (1 hour).
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    int
	 */
	private $default_expiration = 3600;

	/**
	 * Cache key prefix.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $prefix = 'bas_cache_';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param int $expiration Optional. Cache expiration time in seconds. Default 3600.
	 */
	public function __construct( $expiration = null ) {
		if ( null !== $expiration && is_int( $expiration ) && $expiration > 0 ) {
			$this->default_expiration = $expiration;
		}
	}

	/**
	 * Generate a cache key for a query.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $query The search query.
	 * @param  array  $args  Optional. Additional arguments to include in key generation.
	 * @return string Cache key.
	 */
	private function generate_key( $query, $args = array() ) {
		$key_data = array(
			'query' => $query,
			'args'  => $args,
		);

		// Create a unique hash for the cache key.
		$hash = md5( wp_json_encode( $key_data ) );

		return $this->prefix . $hash;
	}

	/**
	 * Get cached data.
	 *
	 * @since  1.0.0
	 * @param  string $query The search query.
	 * @param  array  $args  Optional. Additional arguments. Default empty array.
	 * @return mixed|false Cached data or false if not found.
	 */
	public function get( $query, $args = array() ) {
		if ( empty( $query ) ) {
			return false;
		}

		$key = $this->generate_key( $query, $args );

		$cached = get_transient( $key );

		// Validate cached data structure.
		if ( false !== $cached && is_array( $cached ) && isset( $cached['data'] ) ) {
			return $cached['data'];
		}

		return false;
	}

	/**
	 * Set cached data.
	 *
	 * @since  1.0.0
	 * @param  string $query      The search query.
	 * @param  mixed  $data       The data to cache.
	 * @param  array  $args       Optional. Additional arguments. Default empty array.
	 * @param  int    $expiration Optional. Cache expiration time in seconds. Default uses class default.
	 * @return bool True on success, false on failure.
	 */
	public function set( $query, $data, $args = array(), $expiration = null ) {
		if ( empty( $query ) ) {
			return false;
		}

		$key = $this->generate_key( $query, $args );

		if ( null === $expiration ) {
			$expiration = $this->default_expiration;
		}

		// Wrap data with metadata.
		$cache_data = array(
			'data'      => $data,
			'timestamp' => time(),
			'query'     => $query,
		);

		return set_transient( $key, $cache_data, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @since  1.0.0
	 * @param  string $query The search query.
	 * @param  array  $args  Optional. Additional arguments. Default empty array.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $query, $args = array() ) {
		if ( empty( $query ) ) {
			return false;
		}

		$key = $this->generate_key( $query, $args );

		return delete_transient( $key );
	}

	/**
	 * Clear all plugin caches.
	 *
	 * Note: This only works reliably with object cache backends.
	 * For database transients, it's more complex and resource-intensive.
	 *
	 * @since  1.0.0
	 * @return bool True on success, false on failure.
	 */
	public function clear_all() {
		global $wpdb;

		// For object cache, we can use wp_cache_flush, but only for our group.
		// This is a simplified implementation that clears database transients.
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $this->prefix ) . '%',
				$wpdb->esc_like( '_transient_timeout_' . $this->prefix ) . '%'
			)
		);

		// Clear object cache if available.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_delete_group( 'bas_cache' );
		}

		return false !== $result;
	}

	/**
	 * Get cache statistics.
	 *
	 * @since  1.0.0
	 * @return array Array containing cache statistics.
	 */
	public function get_stats() {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $this->prefix ) . '%'
			)
		);

		return array(
			'count'              => (int) $count,
			'prefix'             => $this->prefix,
			'default_expiration' => $this->default_expiration,
		);
	}

	/**
	 * Check if object cache is available.
	 *
	 * @since  1.0.0
	 * @return bool True if object cache is available, false otherwise.
	 */
	public function has_object_cache() {
		return wp_using_ext_object_cache();
	}
}
