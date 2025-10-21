<?php
/**
 * Box AI Search Plugin
 *
 * @package           BoxAISearch
 * @author            Box AI Search Team
 * @copyright         2025 Box AI Search
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Box AI Search
 * Plugin URI:        https://github.com/jyoung2000/boxwp
 * Description:       Integrate Box AI's semantic document search with WordPress via shortcode-based interface with real-time AJAX results.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Box AI Search Team
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       box-ai-search
 * Domain Path:       /languages
 */

namespace BoxAISearch;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'BAS_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'BAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'BAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'BAS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
function bas_autoloader( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'BoxAISearch\\';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( 0 !== strncmp( $prefix, $class, $len ) ) {
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Namespace to directory mapping.
	$namespace_map = array(
		'Admin\\'          => 'admin/',
		'PublicInterface\\' => 'public/',
	);

	// Check if class is in a mapped namespace.
	$file = null;
	foreach ( $namespace_map as $namespace => $dir ) {
		if ( 0 === strncmp( $namespace, $relative_class, strlen( $namespace ) ) ) {
			// Remove the sub-namespace prefix.
			$class_file = substr( $relative_class, strlen( $namespace ) );
			$file       = BAS_PLUGIN_DIR . $dir . str_replace( '\\', '/', $class_file ) . '.php';
			break;
		}
	}

	// If not in mapped namespace, assume it's in includes/.
	if ( null === $file ) {
		$file = BAS_PLUGIN_DIR . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';
	}

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
}

spl_autoload_register( __NAMESPACE__ . '\\bas_autoloader' );

/**
 * The code that runs during plugin activation.
 */
function bas_activate() {
	require_once BAS_PLUGIN_DIR . 'includes/Activator.php';
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function bas_deactivate() {
	require_once BAS_PLUGIN_DIR . 'includes/Deactivator.php';
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\bas_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\bas_deactivate' );

/**
 * Begins execution of the plugin.
 */
function bas_run() {
	require_once BAS_PLUGIN_DIR . 'includes/Plugin.php';
	$plugin = new Plugin();
	$plugin->run();
}

bas_run();
