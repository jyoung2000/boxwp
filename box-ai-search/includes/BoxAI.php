<?php
/**
 * Box AI Client Class
 *
 * Handles Box AI API integration with JWT authentication.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

/**
 * Box AI client class.
 *
 * Provides integration with Box AI API for semantic document search.
 * Handles JWT authentication with automatic token refresh and caching.
 *
 * @since 1.0.0
 */
class BoxAI {

	/**
	 * Box API base URL.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $api_base_url = 'https://api.box.com/2.0';

	/**
	 * Box authentication URL.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $auth_url = 'https://api.box.com/oauth2/token';

	/**
	 * Cache instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Cache
	 */
	private $cache;

	/**
	 * Encryption instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Encryption
	 */
	private $encryption;

	/**
	 * Access token.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string|null
	 */
	private $access_token;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param Cache      $cache      Optional. Cache instance.
	 * @param Encryption $encryption Optional. Encryption instance.
	 */
	public function __construct( $cache = null, $encryption = null ) {
		$this->cache      = $cache ?? new Cache();
		$this->encryption = $encryption ?? new Encryption();
	}

	/**
	 * Get Box API credentials.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return array|\WP_Error Array of credentials or WP_Error on failure.
	 */
	private function get_credentials() {
		$encrypted_client_id     = get_option( 'bas_box_client_id' );
		$encrypted_client_secret = get_option( 'bas_box_client_secret' );
		$encrypted_enterprise_id = get_option( 'bas_box_enterprise_id' );
		$encrypted_private_key   = get_option( 'bas_box_private_key' );
		$encrypted_passphrase    = get_option( 'bas_box_passphrase' );
		$public_key_id           = get_option( 'bas_box_public_key_id' );

		if ( empty( $encrypted_client_id ) || empty( $encrypted_client_secret ) ) {
			return new \WP_Error(
				'bas_missing_credentials',
				__( 'Box API credentials are not configured.', 'box-ai-search' )
			);
		}

		// Decrypt credentials.
		$client_id     = $this->encryption->decrypt( $encrypted_client_id );
		$client_secret = $this->encryption->decrypt( $encrypted_client_secret );
		$enterprise_id = ! empty( $encrypted_enterprise_id ) ? $this->encryption->decrypt( $encrypted_enterprise_id ) : '';
		$private_key   = ! empty( $encrypted_private_key ) ? $this->encryption->decrypt( $encrypted_private_key ) : '';
		$passphrase    = ! empty( $encrypted_passphrase ) ? $this->encryption->decrypt( $encrypted_passphrase ) : '';

		// Check for decryption errors.
		if ( is_wp_error( $client_id ) || is_wp_error( $client_secret ) ) {
			return new \WP_Error(
				'bas_decryption_error',
				__( 'Failed to decrypt Box API credentials.', 'box-ai-search' )
			);
		}

		return array(
			'client_id'      => $client_id,
			'client_secret'  => $client_secret,
			'enterprise_id'  => $enterprise_id,
			'private_key'    => $private_key,
			'passphrase'     => $passphrase,
			'public_key_id'  => $public_key_id,
		);
	}

	/**
	 * Generate JWT assertion for authentication.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  array $credentials Box API credentials.
	 * @return string|\WP_Error JWT assertion or WP_Error on failure.
	 */
	private function generate_jwt_assertion( $credentials ) {
		// For JWT generation, we would typically use a library like firebase/php-jwt.
		// For simplicity, we'll use a basic implementation or indicate that an external library is needed.
		// In production, you should use a proper JWT library.

		// Check if required JWT fields are present.
		if ( empty( $credentials['enterprise_id'] ) || empty( $credentials['private_key'] ) ) {
			return new \WP_Error(
				'bas_jwt_missing_data',
				__( 'JWT authentication requires enterprise ID and private key.', 'box-ai-search' )
			);
		}

		// Note: In a real implementation, use firebase/php-jwt or similar library.
		// This is a placeholder that assumes you have JWT support.
		return new \WP_Error(
			'bas_jwt_not_implemented',
			__( 'JWT generation requires firebase/php-jwt library. Please install it via Composer.', 'box-ai-search' )
		);
	}

	/**
	 * Get access token with automatic refresh.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return string|\WP_Error Access token or WP_Error on failure.
	 */
	private function get_access_token() {
		// Check if we already have a valid token in memory.
		if ( ! empty( $this->access_token ) ) {
			return $this->access_token;
		}

		// Check cache for stored token.
		$cached_token = get_transient( 'bas_box_access_token' );
		if ( false !== $cached_token && ! empty( $cached_token ) ) {
			$this->access_token = $cached_token;
			return $this->access_token;
		}

		// Get credentials.
		$credentials = $this->get_credentials();
		if ( is_wp_error( $credentials ) ) {
			return $credentials;
		}

		// Authenticate using JWT or client credentials.
		$token = $this->authenticate( $credentials );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Cache the token (Box tokens typically expire in 60 minutes).
		set_transient( 'bas_box_access_token', $token, 55 * MINUTE_IN_SECONDS );

		$this->access_token = $token;

		return $this->access_token;
	}

	/**
	 * Authenticate with Box API.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  array $credentials Box API credentials.
	 * @return string|\WP_Error Access token or WP_Error on failure.
	 */
	private function authenticate( $credentials ) {
		// Determine authentication method (JWT vs Client Credentials).
		if ( ! empty( $credentials['enterprise_id'] ) && ! empty( $credentials['private_key'] ) ) {
			// Use JWT authentication.
			$assertion = $this->generate_jwt_assertion( $credentials );
			if ( is_wp_error( $assertion ) ) {
				return $assertion;
			}

			$body = array(
				'grant_type'            => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'             => $assertion,
				'client_id'             => $credentials['client_id'],
				'client_secret'         => $credentials['client_secret'],
			);
		} else {
			// Use client credentials flow.
			$body = array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $credentials['client_id'],
				'client_secret' => $credentials['client_secret'],
				'box_subject_type' => 'enterprise',
				'box_subject_id'   => $credentials['enterprise_id'],
			);
		}

		$response = wp_remote_post(
			$this->auth_url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'bas_auth_request_failed',
				sprintf(
					/* translators: %s: Error message */
					__( 'Box authentication request failed: %s', 'box-ai-search' ),
					$response->get_error_message()
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'bas_auth_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Box authentication failed with status code: %d', 'box-ai-search' ),
					$response_code
				)
			);
		}

		$data = json_decode( $response_body, true );

		if ( empty( $data['access_token'] ) ) {
			return new \WP_Error(
				'bas_auth_invalid_response',
				__( 'Box authentication response did not contain an access token.', 'box-ai-search' )
			);
		}

		return $data['access_token'];
	}

	/**
	 * Perform semantic search using Box AI.
	 *
	 * @since  1.0.0
	 * @param  string $query The search query.
	 * @param  array  $args  Optional. Additional search arguments. Default empty array.
	 * @return array|\WP_Error Search results or WP_Error on failure.
	 */
	public function search( $query, $args = array() ) {
		// Validate query.
		if ( empty( $query ) || ! is_string( $query ) ) {
			return new \WP_Error(
				'bas_invalid_query',
				__( 'Search query must be a non-empty string.', 'box-ai-search' )
			);
		}

		// Check cache first.
		$cached_results = $this->cache->get( $query, $args );
		if ( false !== $cached_results ) {
			return $cached_results;
		}

		// Get access token.
		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Prepare request body.
		$request_body = array(
			'mode'   => 'single_item_qa',
			'prompt' => sanitize_text_field( $query ),
			'items'  => isset( $args['items'] ) ? $args['items'] : array(),
		);

		// If no items specified, use a default search across accessible content.
		if ( empty( $request_body['items'] ) ) {
			$request_body['mode'] = 'multiple_item_qa';
		}

		// Make API request.
		$response = wp_remote_post(
			$this->api_base_url . '/ai/ask',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $request_body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'bas_search_request_failed',
				sprintf(
					/* translators: %s: Error message */
					__( 'Box AI search request failed: %s', 'box-ai-search' ),
					$response->get_error_message()
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Handle authentication errors by clearing token cache.
		if ( 401 === $response_code ) {
			delete_transient( 'bas_box_access_token' );
			$this->access_token = null;

			return new \WP_Error(
				'bas_search_unauthorized',
				__( 'Box AI search request was unauthorized. Please check your credentials.', 'box-ai-search' )
			);
		}

		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'bas_search_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Box AI search failed with status code: %d', 'box-ai-search' ),
					$response_code
				)
			);
		}

		$results = json_decode( $response_body, true );

		if ( null === $results ) {
			return new \WP_Error(
				'bas_search_invalid_response',
				__( 'Box AI search returned invalid JSON response.', 'box-ai-search' )
			);
		}

		// Cache the results.
		$this->cache->set( $query, $results, $args );

		return $results;
	}

	/**
	 * Test API connection.
	 *
	 * @since  1.0.0
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function test_connection() {
		$token = $this->get_access_token();

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Simple API call to verify connection.
		$response = wp_remote_get(
			$this->api_base_url . '/users/me',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'bas_connection_test_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Connection test failed with status code: %d', 'box-ai-search' ),
					$response_code
				)
			);
		}

		return true;
	}
}
