<?php
/**
 * Encryption Class
 *
 * Handles secure encryption and decryption of sensitive data using AES-256-CTR.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

/**
 * Encryption class.
 *
 * Provides secure encryption/decryption of Box API credentials using
 * OpenSSL AES-256-CTR encryption. Encryption keys must be stored in
 * wp-config.php as constants.
 *
 * @since 1.0.0
 */
class Encryption {

	/**
	 * Encryption cipher method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $cipher = 'aes-256-ctr';

	/**
	 * Encryption key.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string|null
	 */
	private $key;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->key = $this->get_encryption_key();
	}

	/**
	 * Get encryption key from wp-config.php.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return string|null Encryption key or null if not defined.
	 */
	private function get_encryption_key() {
		if ( defined( 'BAS_ENCRYPTION_KEY' ) ) {
			return BAS_ENCRYPTION_KEY;
		}

		return null;
	}

	/**
	 * Check if encryption is available.
	 *
	 * @since  1.0.0
	 * @return bool True if encryption is available, false otherwise.
	 */
	public function is_encryption_available() {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return false;
		}

		if ( empty( $this->key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Encrypt data.
	 *
	 * @since  1.0.0
	 * @param  string $data Data to encrypt.
	 * @return string|\WP_Error Encrypted data or WP_Error on failure.
	 */
	public function encrypt( $data ) {
		if ( ! $this->is_encryption_available() ) {
			return new \WP_Error(
				'bas_encryption_unavailable',
				__( 'Encryption is not available. Please ensure OpenSSL is installed and BAS_ENCRYPTION_KEY is defined in wp-config.php.', 'box-ai-search' )
			);
		}

		if ( empty( $data ) ) {
			return new \WP_Error(
				'bas_encryption_empty_data',
				__( 'Cannot encrypt empty data.', 'box-ai-search' )
			);
		}

		// Generate a random initialization vector.
		$iv_length = openssl_cipher_iv_length( $this->cipher );
		if ( false === $iv_length ) {
			return new \WP_Error(
				'bas_encryption_iv_error',
				__( 'Failed to determine IV length for cipher.', 'box-ai-search' )
			);
		}

		$iv = openssl_random_pseudo_bytes( $iv_length );

		// Encrypt the data.
		$encrypted = openssl_encrypt(
			$data,
			$this->cipher,
			$this->key,
			OPENSSL_RAW_DATA,
			$iv
		);

		if ( false === $encrypted ) {
			return new \WP_Error(
				'bas_encryption_failed',
				__( 'Failed to encrypt data.', 'box-ai-search' )
			);
		}

		// Combine IV and encrypted data, then base64 encode.
		$result = base64_encode( $iv . $encrypted );

		return $result;
	}

	/**
	 * Decrypt data.
	 *
	 * @since  1.0.0
	 * @param  string $data Encrypted data to decrypt.
	 * @return string|\WP_Error Decrypted data or WP_Error on failure.
	 */
	public function decrypt( $data ) {
		if ( ! $this->is_encryption_available() ) {
			return new \WP_Error(
				'bas_encryption_unavailable',
				__( 'Encryption is not available. Please ensure OpenSSL is installed and BAS_ENCRYPTION_KEY is defined in wp-config.php.', 'box-ai-search' )
			);
		}

		if ( empty( $data ) ) {
			return new \WP_Error(
				'bas_encryption_empty_data',
				__( 'Cannot decrypt empty data.', 'box-ai-search' )
			);
		}

		// Decode the base64 encoded data.
		$decoded = base64_decode( $data, true );
		if ( false === $decoded ) {
			return new \WP_Error(
				'bas_decryption_invalid_data',
				__( 'Invalid encrypted data format.', 'box-ai-search' )
			);
		}

		// Get IV length.
		$iv_length = openssl_cipher_iv_length( $this->cipher );
		if ( false === $iv_length ) {
			return new \WP_Error(
				'bas_decryption_iv_error',
				__( 'Failed to determine IV length for cipher.', 'box-ai-search' )
			);
		}

		// Check if data is long enough to contain IV.
		if ( strlen( $decoded ) < $iv_length ) {
			return new \WP_Error(
				'bas_decryption_invalid_length',
				__( 'Encrypted data is too short.', 'box-ai-search' )
			);
		}

		// Extract IV and encrypted data.
		$iv        = substr( $decoded, 0, $iv_length );
		$encrypted = substr( $decoded, $iv_length );

		// Decrypt the data.
		$decrypted = openssl_decrypt(
			$encrypted,
			$this->cipher,
			$this->key,
			OPENSSL_RAW_DATA,
			$iv
		);

		if ( false === $decrypted ) {
			return new \WP_Error(
				'bas_decryption_failed',
				__( 'Failed to decrypt data.', 'box-ai-search' )
			);
		}

		return $decrypted;
	}

	/**
	 * Generate a secure encryption key.
	 *
	 * This is a helper method to generate a key for wp-config.php.
	 * Should only be used during initial setup.
	 *
	 * @since  1.0.0
	 * @return string Base64 encoded encryption key.
	 */
	public static function generate_key() {
		return base64_encode( openssl_random_pseudo_bytes( 32 ) );
	}
}
