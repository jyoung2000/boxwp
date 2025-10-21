<?php
/**
 * PHPUnit Bootstrap File
 *
 * @package BoxAISearch
 */

// Define test constants.
define( 'BAS_TESTS_DIR', __DIR__ );
define( 'BAS_PLUGIN_DIR', dirname( BAS_TESTS_DIR ) . '/' );

// Define encryption key for testing.
if ( ! defined( 'BAS_ENCRYPTION_KEY' ) ) {
	define( 'BAS_ENCRYPTION_KEY', base64_encode( openssl_random_pseudo_bytes( 32 ) ) );
}

// Load WordPress test environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, skipping WordPress test environment.\n";
	echo "Note: WordPress tests require the WordPress test suite to be installed.\n";
}

// Load plugin.
require_once BAS_PLUGIN_DIR . 'box-ai-search.php';
