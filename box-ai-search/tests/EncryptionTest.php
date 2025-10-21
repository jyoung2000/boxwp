<?php
/**
 * Encryption Class Tests
 *
 * @package BoxAISearch
 */

namespace BoxAISearch\Tests;

use BoxAISearch\Encryption;
use PHPUnit\Framework\TestCase;

/**
 * Test Encryption class.
 */
class EncryptionTest extends TestCase {

	/**
	 * Test encryption is available.
	 */
	public function test_encryption_is_available() {
		$encryption = new Encryption();
		$this->assertTrue( $encryption->is_encryption_available() );
	}

	/**
	 * Test basic encryption and decryption.
	 */
	public function test_encrypt_decrypt() {
		$encryption = new Encryption();
		$original   = 'test_secret_data_123';

		$encrypted = $encryption->encrypt( $original );
		$this->assertNotEquals( $original, $encrypted );
		$this->assertIsString( $encrypted );

		$decrypted = $encryption->decrypt( $encrypted );
		$this->assertEquals( $original, $decrypted );
	}

	/**
	 * Test encryption with special characters.
	 */
	public function test_encrypt_special_characters() {
		$encryption = new Encryption();
		$original   = 'test!@#$%^&*()_+-={}[]|\\:";\'<>?,./~`';

		$encrypted = $encryption->encrypt( $original );
		$decrypted = $encryption->decrypt( $encrypted );

		$this->assertEquals( $original, $decrypted );
	}

	/**
	 * Test encryption with empty data.
	 */
	public function test_encrypt_empty_data() {
		$encryption = new Encryption();
		$result     = $encryption->encrypt( '' );

		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test decryption with invalid data.
	 */
	public function test_decrypt_invalid_data() {
		$encryption = new Encryption();
		$result     = $encryption->decrypt( 'invalid_base64_data' );

		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test key generation.
	 */
	public function test_generate_key() {
		$key = Encryption::generate_key();

		$this->assertIsString( $key );
		$this->assertNotEmpty( $key );

		// Verify it's valid base64.
		$decoded = base64_decode( $key, true );
		$this->assertNotFalse( $decoded );
		$this->assertEquals( 32, strlen( $decoded ) );
	}

	/**
	 * Test multiple encryptions produce different results.
	 */
	public function test_multiple_encryptions_differ() {
		$encryption = new Encryption();
		$original   = 'test_data';

		$encrypted1 = $encryption->encrypt( $original );
		$encrypted2 = $encryption->encrypt( $original );

		// Due to random IV, encrypted values should differ.
		$this->assertNotEquals( $encrypted1, $encrypted2 );

		// But both should decrypt to the same value.
		$this->assertEquals( $original, $encryption->decrypt( $encrypted1 ) );
		$this->assertEquals( $original, $encryption->decrypt( $encrypted2 ) );
	}
}
