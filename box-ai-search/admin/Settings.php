<?php
/**
 * Admin Settings Class
 *
 * Handles the admin settings page for Box AI Search.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/admin
 * @since      1.0.0
 */

namespace BoxAISearch\Admin;

use BoxAISearch\Encryption;
use BoxAISearch\BoxAI;
use BoxAISearch\Cache;

/**
 * Admin settings class.
 *
 * Provides the admin interface for configuring Box API credentials
 * and plugin settings with proper security measures.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Encryption instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Encryption
	 */
	private $encryption;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->encryption = new Encryption();
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_bas_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_bas_clear_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_bas_upload_json', array( $this, 'ajax_upload_json' ) );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Box AI Search Settings', 'box-ai-search' ),
			__( 'Box AI Search', 'box-ai-search' ),
			'manage_options',
			'box-ai-search',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		// Register settings.
		register_setting(
			'bas_settings',
			'bas_box_client_id',
			array(
				'sanitize_callback' => array( $this, 'sanitize_encrypted_field' ),
			)
		);

		register_setting(
			'bas_settings',
			'bas_box_client_secret',
			array(
				'sanitize_callback' => array( $this, 'sanitize_encrypted_field' ),
			)
		);

		register_setting(
			'bas_settings',
			'bas_box_enterprise_id',
			array(
				'sanitize_callback' => array( $this, 'sanitize_encrypted_field' ),
			)
		);

		register_setting(
			'bas_settings',
			'bas_box_public_key_id',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'bas_settings',
			'bas_box_private_key',
			array(
				'sanitize_callback' => array( $this, 'sanitize_encrypted_field' ),
			)
		);

		register_setting(
			'bas_settings',
			'bas_box_passphrase',
			array(
				'sanitize_callback' => array( $this, 'sanitize_encrypted_field' ),
			)
		);

		register_setting(
			'bas_settings',
			'bas_cache_expiration',
			array(
				'type'              => 'integer',
				'default'           => 3600,
				'sanitize_callback' => 'absint',
			)
		);

		// Add settings sections.
		add_settings_section(
			'bas_api_credentials',
			__( 'Box API Credentials', 'box-ai-search' ),
			array( $this, 'render_api_credentials_section' ),
			'box-ai-search'
		);

		add_settings_section(
			'bas_cache_settings',
			__( 'Cache Settings', 'box-ai-search' ),
			array( $this, 'render_cache_settings_section' ),
			'box-ai-search'
		);

		// Add settings fields - API Credentials.
		add_settings_field(
			'bas_box_client_id',
			__( 'Client ID', 'box-ai-search' ),
			array( $this, 'render_text_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_client_id',
				'name'        => 'bas_box_client_id',
				'type'        => 'text',
				'description' => __( 'Your Box application Client ID.', 'box-ai-search' ),
			)
		);

		add_settings_field(
			'bas_box_client_secret',
			__( 'Client Secret', 'box-ai-search' ),
			array( $this, 'render_text_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_client_secret',
				'name'        => 'bas_box_client_secret',
				'type'        => 'password',
				'description' => __( 'Your Box application Client Secret.', 'box-ai-search' ),
			)
		);

		add_settings_field(
			'bas_box_enterprise_id',
			__( 'Enterprise ID', 'box-ai-search' ),
			array( $this, 'render_text_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_enterprise_id',
				'name'        => 'bas_box_enterprise_id',
				'type'        => 'text',
				'description' => __( 'Your Box Enterprise ID (for JWT authentication).', 'box-ai-search' ),
			)
		);

		add_settings_field(
			'bas_box_public_key_id',
			__( 'Public Key ID', 'box-ai-search' ),
			array( $this, 'render_text_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_public_key_id',
				'name'        => 'bas_box_public_key_id',
				'type'        => 'text',
				'description' => __( 'Your Box Public Key ID (for JWT authentication).', 'box-ai-search' ),
			)
		);

		add_settings_field(
			'bas_box_private_key',
			__( 'Private Key', 'box-ai-search' ),
			array( $this, 'render_textarea_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_private_key',
				'name'        => 'bas_box_private_key',
				'description' => __( 'Your Box Private Key (for JWT authentication).', 'box-ai-search' ),
			)
		);

		add_settings_field(
			'bas_box_passphrase',
			__( 'Private Key Passphrase', 'box-ai-search' ),
			array( $this, 'render_text_field' ),
			'box-ai-search',
			'bas_api_credentials',
			array(
				'label_for'   => 'bas_box_passphrase',
				'name'        => 'bas_box_passphrase',
				'type'        => 'password',
				'description' => __( 'Your Private Key Passphrase (if applicable).', 'box-ai-search' ),
			)
		);

		// Add settings fields - Cache Settings.
		add_settings_field(
			'bas_cache_expiration',
			__( 'Cache Expiration', 'box-ai-search' ),
			array( $this, 'render_number_field' ),
			'box-ai-search',
			'bas_cache_settings',
			array(
				'label_for'   => 'bas_cache_expiration',
				'name'        => 'bas_cache_expiration',
				'min'         => 60,
				'max'         => 86400,
				'step'        => 60,
				'description' => __( 'Cache expiration time in seconds (60-86400). Default: 3600 (1 hour).', 'box-ai-search' ),
			)
		);
	}

	/**
	 * Sanitize and encrypt field value.
	 *
	 * @since  1.0.0
	 * @param  string $value Field value.
	 * @return string Encrypted value or empty string on error.
	 */
	public function sanitize_encrypted_field( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		// Sanitize first.
		$sanitized = sanitize_text_field( $value );

		// Encrypt the value.
		$encrypted = $this->encryption->encrypt( $sanitized );

		if ( is_wp_error( $encrypted ) ) {
			add_settings_error(
				'bas_settings',
				'encryption_error',
				$encrypted->get_error_message(),
				'error'
			);
			return '';
		}

		return $encrypted;
	}

	/**
	 * Render API credentials section.
	 *
	 * @since 1.0.0
	 */
	public function render_api_credentials_section() {
		echo '<p>';
		echo esc_html__( 'Enter your Box API credentials. All sensitive data is encrypted using AES-256-CTR before storage.', 'box-ai-search' );
		echo '</p>';

		if ( ! $this->encryption->is_encryption_available() ) {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Warning: Encryption is not available. Please ensure OpenSSL is installed and BAS_ENCRYPTION_KEY is defined in wp-config.php.', 'box-ai-search' );
			echo '</p></div>';
		}

		// JSON Upload Section.
		?>
		<div class="bas-json-upload-section" style="margin: 20px 0; padding: 15px; background: #f0f7ff; border-left: 4px solid #0073aa;">
			<h4 style="margin-top: 0;"><?php esc_html_e( 'Quick Setup: Upload Box Configuration JSON', 'box-ai-search' ); ?></h4>
			<p><?php esc_html_e( 'Upload the JSON configuration file downloaded from Box Developer Console to automatically populate all credentials.', 'box-ai-search' ); ?></p>
			<div class="bas-json-upload-form">
				<input type="file" id="bas-json-file" accept=".json" style="margin-right: 10px;" />
				<button type="button" id="bas-upload-json" class="button button-secondary">
					<?php esc_html_e( 'Upload & Import Credentials', 'box-ai-search' ); ?>
				</button>
				<span class="spinner" id="bas-json-spinner" style="float: none; margin: 0 10px;"></span>
			</div>
			<div id="bas-json-result" style="margin-top: 10px;"></div>
			<p class="description">
				<?php esc_html_e( 'This will automatically fill in Client ID, Client Secret, Enterprise ID, and JWT credentials (if present in the file).', 'box-ai-search' ); ?>
			</p>
		</div>

		<p><strong><?php esc_html_e( 'Or enter credentials manually:', 'box-ai-search' ); ?></strong></p>
		<?php
	}

	/**
	 * Render cache settings section.
	 *
	 * @since 1.0.0
	 */
	public function render_cache_settings_section() {
		$cache = new Cache();
		$stats = $cache->get_stats();

		echo '<p>';
		echo esc_html__( 'Configure caching behavior for Box AI search results.', 'box-ai-search' );
		echo '</p>';

		echo '<p>';
		printf(
			/* translators: %d: Number of cached items */
			esc_html__( 'Current cached items: %d', 'box-ai-search' ),
			(int) $stats['count']
		);
		echo ' | ';
		if ( $cache->has_object_cache() ) {
			echo '<span style="color: green;">' . esc_html__( 'Object cache: Active', 'box-ai-search' ) . '</span>';
		} else {
			echo '<span style="color: orange;">' . esc_html__( 'Object cache: Not active (using database transients)', 'box-ai-search' ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Render text field.
	 *
	 * @since 1.0.0
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$name  = $args['name'];
		$type  = isset( $args['type'] ) ? $args['type'] : 'text';
		$value = get_option( $name );

		// For encrypted fields, show a placeholder if value exists.
		if ( ! empty( $value ) && 'password' === $type ) {
			$value = '********';
		} elseif ( ! empty( $value ) ) {
			$decrypted = $this->encryption->decrypt( $value );
			if ( ! is_wp_error( $decrypted ) ) {
				$value = $decrypted;
			}
		}

		printf(
			'<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr( $type ),
			esc_attr( $args['label_for'] ),
			esc_attr( $name ),
			esc_attr( $value )
		);

		if ( isset( $args['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Render textarea field.
	 *
	 * @since 1.0.0
	 * @param array $args Field arguments.
	 */
	public function render_textarea_field( $args ) {
		$name  = $args['name'];
		$value = get_option( $name );

		// For encrypted fields, decrypt for display.
		if ( ! empty( $value ) ) {
			$decrypted = $this->encryption->decrypt( $value );
			if ( ! is_wp_error( $decrypted ) ) {
				$value = $decrypted;
			}
		}

		printf(
			'<textarea id="%s" name="%s" rows="10" class="large-text code">%s</textarea>',
			esc_attr( $args['label_for'] ),
			esc_attr( $name ),
			esc_textarea( $value )
		);

		if ( isset( $args['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Render number field.
	 *
	 * @since 1.0.0
	 * @param array $args Field arguments.
	 */
	public function render_number_field( $args ) {
		$name  = $args['name'];
		$value = get_option( $name, 3600 );
		$min   = isset( $args['min'] ) ? $args['min'] : 0;
		$max   = isset( $args['max'] ) ? $args['max'] : 999999;
		$step  = isset( $args['step'] ) ? $args['step'] : 1;

		printf(
			'<input type="number" id="%s" name="%s" value="%s" min="%s" max="%s" step="%s" class="small-text" />',
			esc_attr( $args['label_for'] ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $min ),
			esc_attr( $max ),
			esc_attr( $step )
		);

		if ( isset( $args['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php
			// Show prominent warning if encryption is not available.
			if ( ! $this->encryption->is_encryption_available() ) :
				?>
				<div class="notice notice-error is-dismissible">
					<h2><?php esc_html_e( 'Encryption Not Configured', 'box-ai-search' ); ?></h2>
					<p><strong><?php esc_html_e( 'The plugin cannot function without encryption being configured.', 'box-ai-search' ); ?></strong></p>
					<p><?php esc_html_e( 'You must set up the encryption key before you can upload credentials, test connections, or use any plugin features.', 'box-ai-search' ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=box-ai-search-setup' ) ); ?>" class="button button-primary button-large">
							<?php esc_html_e( 'Launch Setup Wizard to Configure Encryption', 'box-ai-search' ); ?>
						</a>
					</p>
					<p><?php esc_html_e( 'Or manually add this to your wp-config.php file:', 'box-ai-search' ); ?></p>
					<pre style="background: #f0f0f0; padding: 10px; border-left: 4px solid #dc3232;">define( 'BAS_ENCRYPTION_KEY', '<?php echo esc_html( \BoxAISearch\Encryption::generate_key() ); ?>' );</pre>
				</div>
				<?php
			endif;
			?>

			<?php settings_errors( 'bas_settings' ); ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'bas_settings' );
				do_settings_sections( 'box-ai-search' );
				submit_button( __( 'Save Settings', 'box-ai-search' ) );
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Tools', 'box-ai-search' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Test Connection', 'box-ai-search' ); ?></th>
					<td>
						<button type="button" id="bas-test-connection" class="button button-secondary">
							<?php esc_html_e( 'Test Box API Connection', 'box-ai-search' ); ?>
						</button>
						<p class="description">
							<?php esc_html_e( 'Test your Box API credentials to ensure they are working correctly.', 'box-ai-search' ); ?>
						</p>
						<div id="bas-test-result" style="margin-top: 10px;"></div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Clear Cache', 'box-ai-search' ); ?></th>
					<td>
						<button type="button" id="bas-clear-cache" class="button button-secondary">
							<?php esc_html_e( 'Clear All Cached Results', 'box-ai-search' ); ?>
						</button>
						<p class="description">
							<?php esc_html_e( 'Clear all cached search results. Use this after making changes to Box content.', 'box-ai-search' ); ?>
						</p>
						<div id="bas-clear-cache-result" style="margin-top: 10px;"></div>
					</td>
				</tr>
			</table>

			<hr>

			<h2><?php esc_html_e( 'Usage', 'box-ai-search' ); ?></h2>
			<p><?php esc_html_e( 'Use the following shortcode to add Box AI search to any page or post:', 'box-ai-search' ); ?></p>
			<code>[box_ai_search]</code>

			<h3><?php esc_html_e( 'Setup Instructions', 'box-ai-search' ); ?></h3>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=box-ai-search-setup' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Launch Setup Wizard', 'box-ai-search' ); ?>
				</a>
			</p>

			<p><?php esc_html_e( 'Or follow these manual setup steps:', 'box-ai-search' ); ?></p>

			<ol>
				<li><?php esc_html_e( 'Add the following to your wp-config.php file:', 'box-ai-search' ); ?>
					<pre>define( 'BAS_ENCRYPTION_KEY', '<?php echo esc_html( \BoxAISearch\Encryption::generate_key() ); ?>' );</pre>
				</li>
				<li><?php esc_html_e( 'Configure your Box API credentials above.', 'box-ai-search' ); ?></li>
				<li><?php esc_html_e( 'Test the connection using the "Test Box API Connection" button.', 'box-ai-search' ); ?></li>
				<li><?php esc_html_e( 'Add the [box_ai_search] shortcode to any page or post.', 'box-ai-search' ); ?></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_box-ai-search' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'bas-admin',
			BAS_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			BAS_VERSION,
			true
		);

		wp_localize_script(
			'bas-admin',
			'basAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bas_admin_nonce' ),
			)
		);
	}

	/**
	 * AJAX handler for testing Box API connection.
	 *
	 * @since 1.0.0
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'bas_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', 'box-ai-search' ),
			) );
		}

		$box_ai = new BoxAI();
		$result = $box_ai->test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => $result->get_error_message(),
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Connection successful! Your Box API credentials are working correctly.', 'box-ai-search' ),
		) );
	}

	/**
	 * AJAX handler for clearing cache.
	 *
	 * @since 1.0.0
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'bas_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', 'box-ai-search' ),
			) );
		}

		$cache  = new Cache();
		$result = $cache->clear_all();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => $result->get_error_message(),
			) );
		}

		$count = isset( $result['count'] ) ? $result['count'] : 0;

		if ( 0 === $count ) {
			wp_send_json_success( array(
				'message' => __( 'Cache is already empty. Nothing to clear.', 'box-ai-search' ),
			) );
		}

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: Number of cache items cleared */
				_n( 'Successfully cleared %d cache item.', 'Successfully cleared %d cache items.', $count, 'box-ai-search' ),
				$count
			),
		) );
	}

	/**
	 * AJAX handler for uploading Box JSON configuration.
	 *
	 * @since 1.0.0
	 */
	public function ajax_upload_json() {
		check_ajax_referer( 'bas_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', 'box-ai-search' ),
			) );
		}

		// Check if file was uploaded.
		if ( empty( $_FILES['json_file'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'No file uploaded.', 'box-ai-search' ),
			) );
		}

		$file = $_FILES['json_file'];

		// Validate file type.
		$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( 'json' !== $file_ext ) {
			wp_send_json_error( array(
				'message' => __( 'Please upload a JSON file.', 'box-ai-search' ),
			) );
		}

		// Validate file size (max 1MB).
		if ( $file['size'] > 1048576 ) {
			wp_send_json_error( array(
				'message' => __( 'File is too large. Maximum size is 1MB.', 'box-ai-search' ),
			) );
		}

		// Read file content.
		$json_content = file_get_contents( $file['tmp_name'] );
		if ( false === $json_content ) {
			wp_send_json_error( array(
				'message' => __( 'Failed to read file.', 'box-ai-search' ),
			) );
		}

		// Parse JSON.
		$config = json_decode( $json_content, true );
		if ( null === $config ) {
			$json_error = json_last_error_msg();
			wp_send_json_error( array(
				'message' => sprintf(
					/* translators: %s: JSON error message */
					__( 'Invalid JSON format: %s', 'box-ai-search' ),
					$json_error
				),
			) );
		}

		// Validate Box JSON structure.
		if ( ! isset( $config['boxAppSettings'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid Box configuration file. Missing "boxAppSettings" section.', 'box-ai-search' ),
			) );
		}

		// Extract and encrypt credentials.
		$credentials = array();
		$errors      = array();

		// Client ID.
		if ( isset( $config['boxAppSettings']['clientID'] ) && ! empty( $config['boxAppSettings']['clientID'] ) ) {
			$client_id = trim( $config['boxAppSettings']['clientID'] );
			$encrypted = $this->encryption->encrypt( $client_id );
			if ( ! is_wp_error( $encrypted ) ) {
				update_option( 'bas_box_client_id', $encrypted );
				$credentials['client_id'] = $client_id;
			} else {
				$errors[] = 'Client ID encryption failed';
			}
		}

		// Client Secret.
		if ( isset( $config['boxAppSettings']['clientSecret'] ) && ! empty( $config['boxAppSettings']['clientSecret'] ) ) {
			$client_secret = trim( $config['boxAppSettings']['clientSecret'] );
			$encrypted     = $this->encryption->encrypt( $client_secret );
			if ( ! is_wp_error( $encrypted ) ) {
				update_option( 'bas_box_client_secret', $encrypted );
				$credentials['client_secret'] = $client_secret;
			} else {
				$errors[] = 'Client Secret encryption failed';
			}
		}

		// Enterprise ID.
		if ( isset( $config['enterpriseID'] ) && ! empty( $config['enterpriseID'] ) ) {
			$enterprise_id = trim( $config['enterpriseID'] );
			$encrypted     = $this->encryption->encrypt( $enterprise_id );
			if ( ! is_wp_error( $encrypted ) ) {
				update_option( 'bas_box_enterprise_id', $encrypted );
				$credentials['enterprise_id'] = $enterprise_id;
			} else {
				$errors[] = 'Enterprise ID encryption failed';
			}
		}

		// Public Key ID (for JWT).
		if ( isset( $config['boxAppSettings']['appAuth']['publicKeyID'] ) && ! empty( $config['boxAppSettings']['appAuth']['publicKeyID'] ) ) {
			$public_key_id = trim( $config['boxAppSettings']['appAuth']['publicKeyID'] );
			update_option( 'bas_box_public_key_id', sanitize_text_field( $public_key_id ) );
			$credentials['public_key_id'] = $public_key_id;
		}

		// Private Key (for JWT).
		if ( isset( $config['boxAppSettings']['appAuth']['privateKey'] ) && ! empty( $config['boxAppSettings']['appAuth']['privateKey'] ) ) {
			$private_key = trim( $config['boxAppSettings']['appAuth']['privateKey'] );
			$encrypted   = $this->encryption->encrypt( $private_key );
			if ( ! is_wp_error( $encrypted ) ) {
				update_option( 'bas_box_private_key', $encrypted );
				$credentials['private_key'] = 'present';
			} else {
				$errors[] = 'Private Key encryption failed';
			}
		}

		// Passphrase (for JWT).
		if ( isset( $config['boxAppSettings']['appAuth']['passphrase'] ) && ! empty( $config['boxAppSettings']['appAuth']['passphrase'] ) ) {
			$passphrase = trim( $config['boxAppSettings']['appAuth']['passphrase'] );
			$encrypted  = $this->encryption->encrypt( $passphrase );
			if ( ! is_wp_error( $encrypted ) ) {
				update_option( 'bas_box_passphrase', $encrypted );
				$credentials['passphrase'] = 'present';
			} else {
				$errors[] = 'Passphrase encryption failed';
			}
		}

		// Check if any credentials were saved.
		if ( empty( $credentials ) ) {
			$error_message = __( 'No valid credentials found in JSON file.', 'box-ai-search' );
			if ( ! empty( $errors ) ) {
				$error_message .= ' ' . __( 'Errors:', 'box-ai-search' ) . ' ' . implode( ', ', $errors );
			}
			wp_send_json_error( array(
				'message' => $error_message,
			) );
		}

		// Build success message.
		$imported = array();
		if ( isset( $credentials['client_id'] ) ) {
			$imported[] = __( 'Client ID', 'box-ai-search' );
		}
		if ( isset( $credentials['client_secret'] ) ) {
			$imported[] = __( 'Client Secret', 'box-ai-search' );
		}
		if ( isset( $credentials['enterprise_id'] ) ) {
			$imported[] = __( 'Enterprise ID', 'box-ai-search' );
		}
		if ( isset( $credentials['public_key_id'] ) ) {
			$imported[] = __( 'Public Key ID', 'box-ai-search' );
		}
		if ( isset( $credentials['private_key'] ) ) {
			$imported[] = __( 'Private Key', 'box-ai-search' );
		}
		if ( isset( $credentials['passphrase'] ) ) {
			$imported[] = __( 'Passphrase', 'box-ai-search' );
		}

		$success_message = sprintf(
			/* translators: %s: List of imported credentials */
			__( 'Successfully imported: %s', 'box-ai-search' ),
			implode( ', ', $imported )
		);

		// Add warning about encryption errors if any occurred.
		if ( ! empty( $errors ) ) {
			$success_message .= ' ' . sprintf(
				/* translators: %s: List of errors */
				__( 'Warning - Some items failed: %s', 'box-ai-search' ),
				implode( ', ', $errors )
			);
		}

		wp_send_json_success( array(
			'message'  => $success_message,
			'imported' => $credentials,
			'errors'   => $errors,
		) );
	}
}
