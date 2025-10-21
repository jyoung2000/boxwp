<?php
/**
 * Core Plugin Class
 *
 * Initializes and runs the plugin.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/includes
 * @since      1.0.0
 */

namespace BoxAISearch;

use BoxAISearch\Admin\Settings;
use BoxAISearch\PublicInterface\Shortcode;

/**
 * Core plugin class.
 *
 * Defines internationalization, admin hooks, and public hooks.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'box-ai-search';
		$this->version     = BAS_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		// Core classes are auto-loaded via the autoloader in the main plugin file.
		// Additional dependencies can be required here if needed.
	}

	/**
	 * Define locale for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'box-ai-search',
			false,
			dirname( BAS_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Define admin hooks.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Initialize settings page.
		new Settings();

		// Add settings link to plugins page.
		add_filter(
			'plugin_action_links_' . BAS_PLUGIN_BASENAME,
			array( $this, 'add_action_links' )
		);
	}

	/**
	 * Define public-facing hooks.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Initialize shortcode.
		new Shortcode();
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * @since  1.0.0
	 * @param  array $links Existing action links.
	 * @return array Modified action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=box-ai-search' ) ),
			esc_html__( 'Settings', 'box-ai-search' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Run the plugin.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		// Plugin is now running.
		// All hooks are registered via constructor.
	}

	/**
	 * Get plugin name.
	 *
	 * @since  1.0.0
	 * @return string Plugin name.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get plugin version.
	 *
	 * @since  1.0.0
	 * @return string Plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
