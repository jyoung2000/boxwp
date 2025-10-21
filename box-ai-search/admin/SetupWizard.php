<?php
/**
 * Setup Wizard Class
 *
 * Handles the initial setup wizard for encryption key and Box credentials.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/admin
 * @since      1.0.0
 */

namespace BoxAISearch\Admin;

use BoxAISearch\Encryption;

/**
 * Setup wizard class.
 *
 * Provides a user-friendly setup wizard for configuring the plugin.
 *
 * @since 1.0.0
 */
class SetupWizard {

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

		add_action( 'admin_menu', array( $this, 'add_wizard_page' ) );
		add_action( 'admin_notices', array( $this, 'show_setup_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_wizard_assets' ) );
		add_action( 'wp_ajax_bas_generate_key', array( $this, 'ajax_generate_key' ) );
		add_action( 'wp_ajax_bas_auto_configure_key', array( $this, 'ajax_auto_configure_key' ) );
		add_action( 'wp_ajax_bas_dismiss_setup_notice', array( $this, 'ajax_dismiss_notice' ) );
		add_action( 'wp_ajax_bas_skip_wizard', array( $this, 'ajax_skip_wizard' ) );
	}

	/**
	 * Add setup wizard page to admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_wizard_page() {
		add_submenu_page(
			null, // Hidden from menu
			__( 'Box AI Search Setup', 'box-ai-search' ),
			__( 'Box AI Search Setup', 'box-ai-search' ),
			'manage_options',
			'box-ai-search-setup',
			array( $this, 'render_wizard_page' )
		);
	}

	/**
	 * Show setup notice if encryption is not configured.
	 *
	 * @since 1.0.0
	 */
	public function show_setup_notice() {
		// Don't show if already dismissed or on setup page.
		if ( get_option( 'bas_setup_notice_dismissed' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( isset( $screen->id ) && 'admin_page_box-ai-search-setup' === $screen->id ) {
			return;
		}

		// Check if encryption is available.
		if ( $this->encryption->is_encryption_available() ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible bas-setup-notice">
			<p>
				<strong><?php esc_html_e( 'Box AI Search Setup Required', 'box-ai-search' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'Welcome! To get started with Box AI Search, you need to complete the quick setup.', 'box-ai-search' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=box-ai-search-setup' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Start Setup Wizard', 'box-ai-search' ); ?>
				</a>
				<button type="button" class="button button-secondary bas-skip-setup">
					<?php esc_html_e( 'Skip Setup', 'box-ai-search' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Enqueue wizard assets.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_wizard_assets( $hook ) {
		if ( 'admin_page_box-ai-search-setup' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'bas-wizard',
			BAS_PLUGIN_URL . 'admin/css/wizard.css',
			array(),
			BAS_VERSION
		);

		wp_enqueue_script(
			'bas-wizard',
			BAS_PLUGIN_URL . 'admin/js/wizard.js',
			array( 'jquery' ),
			BAS_VERSION,
			true
		);

		wp_localize_script(
			'bas-wizard',
			'basWizard',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bas_wizard_nonce' ),
				'i18n'    => array(
					'generating'      => __( 'Generating...', 'box-ai-search' ),
					'configuring'     => __( 'Configuring...', 'box-ai-search' ),
					'copied'          => __( 'Copied!', 'box-ai-search' ),
					'copyFailed'      => __( 'Failed to copy. Please copy manually.', 'box-ai-search' ),
					'autoConfigError' => __( 'Auto-configuration failed. Please configure manually.', 'box-ai-search' ),
				),
			)
		);
	}

	/**
	 * Render setup wizard page.
	 *
	 * @since 1.0.0
	 */
	public function render_wizard_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$encryption_available = $this->encryption->is_encryption_available();
		$has_openssl          = function_exists( 'openssl_encrypt' );
		$wp_config_path       = $this->get_wp_config_path();
		$wp_config_writable   = $wp_config_path && is_writable( $wp_config_path );

		?>
		<div class="wrap bas-wizard-wrap">
			<h1><?php esc_html_e( 'Box AI Search Setup Wizard', 'box-ai-search' ); ?></h1>

			<div class="bas-wizard-container">
				<!-- Progress Steps -->
				<div class="bas-wizard-steps">
					<div class="bas-wizard-step active" data-step="1">
						<span class="bas-step-number">1</span>
						<span class="bas-step-label"><?php esc_html_e( 'Encryption', 'box-ai-search' ); ?></span>
					</div>
					<div class="bas-wizard-step" data-step="2">
						<span class="bas-step-number">2</span>
						<span class="bas-step-label"><?php esc_html_e( 'Box Credentials', 'box-ai-search' ); ?></span>
					</div>
					<div class="bas-wizard-step" data-step="3">
						<span class="bas-step-number">3</span>
						<span class="bas-step-label"><?php esc_html_e( 'Complete', 'box-ai-search' ); ?></span>
					</div>
				</div>

				<!-- Step 1: Encryption Setup -->
				<div class="bas-wizard-content" id="bas-step-1" style="display: block;">
					<h2><?php esc_html_e( 'Step 1: Configure Encryption', 'box-ai-search' ); ?></h2>

					<?php if ( ! $has_openssl ) : ?>
						<div class="notice notice-error">
							<p>
								<strong><?php esc_html_e( 'OpenSSL Extension Required', 'box-ai-search' ); ?></strong>
							</p>
							<p>
								<?php esc_html_e( 'The OpenSSL PHP extension is required for encryption. Please install it and refresh this page.', 'box-ai-search' ); ?>
							</p>
						</div>
					<?php elseif ( $encryption_available ) : ?>
						<div class="notice notice-success">
							<p>
								<strong><?php esc_html_e( 'Encryption Already Configured!', 'box-ai-search' ); ?></strong>
							</p>
							<p>
								<?php esc_html_e( 'Your encryption key is properly configured. You can proceed to the next step.', 'box-ai-search' ); ?>
							</p>
						</div>
						<p>
							<button type="button" class="button button-primary bas-next-step" data-next="2">
								<?php esc_html_e( 'Next: Box Credentials', 'box-ai-search' ); ?> →
							</button>
						</p>
					<?php else : ?>
						<p><?php esc_html_e( 'For security, Box AI Search encrypts your API credentials using AES-256-CTR encryption. Let\'s set up your encryption key.', 'box-ai-search' ); ?></p>

						<div class="bas-wizard-section">
							<h3><?php esc_html_e( 'Option 1: Automatic Configuration (Recommended)', 'box-ai-search' ); ?></h3>
							<p><?php esc_html_e( 'We can automatically generate and configure your encryption key.', 'box-ai-search' ); ?></p>

							<?php if ( $wp_config_writable ) : ?>
								<div class="notice notice-info inline">
									<p><?php esc_html_e( 'Your wp-config.php file is writable. We can automatically add the encryption key for you.', 'box-ai-search' ); ?></p>
								</div>
								<p>
									<button type="button" class="button button-primary button-hero" id="bas-auto-configure">
										<?php esc_html_e( 'Generate & Configure Automatically', 'box-ai-search' ); ?>
									</button>
								</p>
							<?php else : ?>
								<div class="notice notice-warning inline">
									<p><?php esc_html_e( 'Your wp-config.php file is not writable. You\'ll need to manually add the encryption key.', 'box-ai-search' ); ?></p>
								</div>
							<?php endif; ?>

							<div id="bas-auto-configure-result"></div>
						</div>

						<div class="bas-wizard-section">
							<h3><?php esc_html_e( 'Option 2: Manual Configuration', 'box-ai-search' ); ?></h3>
							<ol>
								<li>
									<?php esc_html_e( 'Click the button below to generate a secure encryption key:', 'box-ai-search' ); ?>
									<p>
										<button type="button" class="button button-secondary" id="bas-generate-key">
											<?php esc_html_e( 'Generate Encryption Key', 'box-ai-search' ); ?>
										</button>
									</p>
								</li>
								<li id="bas-key-display" style="display: none;">
									<?php esc_html_e( 'Copy this code and add it to your wp-config.php file:', 'box-ai-search' ); ?>
									<div class="bas-code-block">
										<code id="bas-generated-key"></code>
										<button type="button" class="button button-small bas-copy-btn" id="bas-copy-key">
											<?php esc_html_e( 'Copy to Clipboard', 'box-ai-search' ); ?>
										</button>
									</div>
									<p class="description">
										<?php
										printf(
											/* translators: %s: wp-config.php file path */
											esc_html__( 'Add this line to %s, before the line "That\'s all, stop editing!"', 'box-ai-search' ),
											'<code>' . esc_html( $wp_config_path ? $wp_config_path : 'wp-config.php' ) . '</code>'
										);
										?>
									</p>
								</li>
								<li id="bas-verify-step" style="display: none;">
									<?php esc_html_e( 'After adding the code to wp-config.php, refresh this page to verify:', 'box-ai-search' ); ?>
									<p>
										<button type="button" class="button button-primary" onclick="location.reload()">
											<?php esc_html_e( 'Verify Configuration', 'box-ai-search' ); ?>
										</button>
									</p>
								</li>
							</ol>
						</div>
					<?php endif; ?>
				</div>

				<!-- Step 2: Box Credentials -->
				<div class="bas-wizard-content" id="bas-step-2" style="display: none;">
					<h2><?php esc_html_e( 'Step 2: Configure Box API Credentials', 'box-ai-search' ); ?></h2>

					<?php if ( ! $encryption_available ) : ?>
						<div class="notice notice-warning">
							<p><?php esc_html_e( 'Please configure encryption first before adding Box credentials.', 'box-ai-search' ); ?></p>
						</div>
						<p>
							<button type="button" class="button bas-prev-step" data-prev="1">
								← <?php esc_html_e( 'Back to Encryption Setup', 'box-ai-search' ); ?>
							</button>
						</p>
					<?php else : ?>
						<p><?php esc_html_e( 'Now let\'s configure your Box API credentials. You\'ll need a Box application with API access.', 'box-ai-search' ); ?></p>

						<div class="bas-wizard-section">
							<h3><?php esc_html_e( 'Don\'t have a Box Application yet?', 'box-ai-search' ); ?></h3>
							<ol>
								<li><?php esc_html_e( 'Go to Box Developer Console', 'box-ai-search' ); ?>: <a href="https://app.box.com/developers/console" target="_blank">https://app.box.com/developers/console</a></li>
								<li><?php esc_html_e( 'Click "Create New App" and choose "Custom App"', 'box-ai-search' ); ?></li>
								<li><?php esc_html_e( 'Select authentication method (OAuth 2.0 with JWT recommended)', 'box-ai-search' ); ?></li>
								<li><?php esc_html_e( 'Download your app configuration JSON file', 'box-ai-search' ); ?></li>
							</ol>
						</div>

						<div class="bas-wizard-section">
							<h3><?php esc_html_e( 'Enter Your Credentials', 'box-ai-search' ); ?></h3>
							<p>
								<a href="<?php echo esc_url( admin_url( 'options-general.php?page=box-ai-search' ) ); ?>" class="button button-primary button-hero">
									<?php esc_html_e( 'Go to Settings Page', 'box-ai-search' ); ?>
								</a>
							</p>
							<p class="description">
								<?php esc_html_e( 'Click the button above to go to the settings page where you can enter your Box API credentials securely.', 'box-ai-search' ); ?>
							</p>
						</div>

						<p>
							<button type="button" class="button bas-prev-step" data-prev="1">
								← <?php esc_html_e( 'Previous', 'box-ai-search' ); ?>
							</button>
							<button type="button" class="button button-primary bas-next-step" data-next="3">
								<?php esc_html_e( 'Next: Complete Setup', 'box-ai-search' ); ?> →
							</button>
						</p>
					<?php endif; ?>
				</div>

				<!-- Step 3: Complete -->
				<div class="bas-wizard-content" id="bas-step-3" style="display: none;">
					<h2><?php esc_html_e( 'Setup Complete!', 'box-ai-search' ); ?></h2>

					<div class="notice notice-success inline">
						<p><strong><?php esc_html_e( 'Congratulations! You\'re all set up.', 'box-ai-search' ); ?></strong></p>
					</div>

					<h3><?php esc_html_e( 'Next Steps:', 'box-ai-search' ); ?></h3>
					<ol>
						<li>
							<strong><?php esc_html_e( 'Test Your Connection:', 'box-ai-search' ); ?></strong>
							<?php esc_html_e( 'Go to the settings page and click "Test Box API Connection"', 'box-ai-search' ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'Add the Shortcode:', 'box-ai-search' ); ?></strong>
							<?php esc_html_e( 'Add', 'box-ai-search' ); ?> <code>[box_ai_search]</code> <?php esc_html_e( 'to any page or post', 'box-ai-search' ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'Configure Cache Settings:', 'box-ai-search' ); ?></strong>
							<?php esc_html_e( 'Adjust cache expiration time if needed', 'box-ai-search' ); ?>
						</li>
					</ol>

					<p>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=box-ai-search' ) ); ?>" class="button button-primary button-hero">
							<?php esc_html_e( 'Go to Settings', 'box-ai-search' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'Back to Plugins', 'box-ai-search' ); ?>
						</a>
					</p>

					<hr>

					<h3><?php esc_html_e( 'Useful Resources:', 'box-ai-search' ); ?></h3>
					<ul>
						<li><a href="<?php echo esc_url( BAS_PLUGIN_URL . 'README.md' ); ?>" target="_blank"><?php esc_html_e( 'Plugin Documentation', 'box-ai-search' ); ?></a></li>
						<li><a href="https://developer.box.com/guides/box-ai/" target="_blank"><?php esc_html_e( 'Box AI Documentation', 'box-ai-search' ); ?></a></li>
						<li><a href="<?php echo esc_url( BAS_PLUGIN_URL . 'SECURITY.md' ); ?>" target="_blank"><?php esc_html_e( 'Security Best Practices', 'box-ai-search' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for generating encryption key.
	 *
	 * @since 1.0.0
	 */
	public function ajax_generate_key() {
		check_ajax_referer( 'bas_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', 'box-ai-search' ),
			) );
		}

		$key = Encryption::generate_key();

		$code = sprintf(
			"define( 'BAS_ENCRYPTION_KEY', '%s' );",
			$key
		);

		wp_send_json_success( array(
			'key'  => $key,
			'code' => $code,
		) );
	}

	/**
	 * AJAX handler for auto-configuring encryption key.
	 *
	 * @since 1.0.0
	 */
	public function ajax_auto_configure_key() {
		check_ajax_referer( 'bas_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', 'box-ai-search' ),
			) );
		}

		$wp_config_path = $this->get_wp_config_path();

		if ( ! $wp_config_path || ! is_writable( $wp_config_path ) ) {
			wp_send_json_error( array(
				'message' => __( 'wp-config.php is not writable. Please use manual configuration.', 'box-ai-search' ),
			) );
		}

		$key  = Encryption::generate_key();
		$code = sprintf( "define( 'BAS_ENCRYPTION_KEY', '%s' );", $key );

		$result = $this->add_to_wp_config( $wp_config_path, $code );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Encryption key has been automatically configured!', 'box-ai-search' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to write to wp-config.php. Please configure manually.', 'box-ai-search' ),
			) );
		}
	}

	/**
	 * AJAX handler for dismissing setup notice.
	 *
	 * @since 1.0.0
	 */
	public function ajax_dismiss_notice() {
		check_ajax_referer( 'bas_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		update_option( 'bas_setup_notice_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * AJAX handler for skipping wizard.
	 *
	 * @since 1.0.0
	 */
	public function ajax_skip_wizard() {
		check_ajax_referer( 'bas_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		update_option( 'bas_setup_notice_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * Get wp-config.php file path.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return string|false Path to wp-config.php or false if not found.
	 */
	private function get_wp_config_path() {
		$config_path = ABSPATH . 'wp-config.php';

		if ( file_exists( $config_path ) ) {
			return $config_path;
		}

		// Check one level up.
		$config_path = dirname( ABSPATH ) . '/wp-config.php';
		if ( file_exists( $config_path ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			return $config_path;
		}

		return false;
	}

	/**
	 * Add configuration to wp-config.php.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $config_path Path to wp-config.php.
	 * @param  string $code        Code to add.
	 * @return bool True on success, false on failure.
	 */
	private function add_to_wp_config( $config_path, $code ) {
		$config_content = file_get_contents( $config_path );

		if ( false === $config_content ) {
			return false;
		}

		// Check if already defined.
		if ( false !== strpos( $config_content, 'BAS_ENCRYPTION_KEY' ) ) {
			return false;
		}

		// Find the insertion point.
		$search = "/* That's all, stop editing!";

		if ( false === strpos( $config_content, $search ) ) {
			// Fallback: insert before closing PHP tag or at end.
			$search = '?>';
		}

		$insert = "\n// Box AI Search encryption key\n" . $code . "\n\n";

		$new_content = str_replace( $search, $insert . $search, $config_content );

		// Attempt to write.
		$result = file_put_contents( $config_path, $new_content );

		return false !== $result;
	}
}
