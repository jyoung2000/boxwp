<?php
/**
 * Shortcode Class
 *
 * Handles the [box_ai_search] shortcode and AJAX search functionality.
 *
 * @package    BoxAISearch
 * @subpackage BoxAISearch/public
 * @since      1.0.0
 */

namespace BoxAISearch\PublicInterface;

use BoxAISearch\BoxAI;
use BoxAISearch\Cache;

/**
 * Shortcode class.
 *
 * Provides the [box_ai_search] shortcode for rendering the search interface
 * and handles AJAX search requests with proper security measures.
 *
 * @since 1.0.0
 */
class Shortcode {

	/**
	 * Whether assets have been enqueued.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    bool
	 */
	private static $assets_enqueued = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_shortcode( 'box_ai_search', array( $this, 'render_shortcode' ) );
		add_action( 'wp_ajax_bas_search', array( $this, 'ajax_search' ) );
		add_action( 'wp_ajax_nopriv_bas_search', array( $this, 'ajax_search' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @since  1.0.0
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string Shortcode HTML output.
	 */
	public function render_shortcode( $atts, $content = '' ) {
		// Parse attributes.
		$atts = shortcode_atts(
			array(
				'placeholder' => __( 'Search Box documents...', 'box-ai-search' ),
				'button_text' => __( 'Search', 'box-ai-search' ),
			),
			$atts,
			'box_ai_search'
		);

		// Enqueue assets only when shortcode is used.
		$this->enqueue_assets();

		// Generate a unique ID for this instance.
		$instance_id = 'bas-search-' . wp_rand();

		ob_start();
		?>
		<div class="bas-search-container" id="<?php echo esc_attr( $instance_id ); ?>">
			<div class="bas-search-form">
				<input
					type="text"
					class="bas-search-input"
					placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
					aria-label="<?php echo esc_attr__( 'Search query', 'box-ai-search' ); ?>"
				/>
				<button type="button" class="bas-search-button">
					<?php echo esc_html( $atts['button_text'] ); ?>
				</button>
			</div>
			<div class="bas-search-loading" style="display: none;">
				<span class="bas-spinner"></span>
				<span><?php esc_html_e( 'Searching...', 'box-ai-search' ); ?></span>
			</div>
			<div class="bas-search-results"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function enqueue_assets() {
		// Only enqueue once per page.
		if ( self::$assets_enqueued ) {
			return;
		}

		wp_enqueue_style(
			'bas-frontend',
			BAS_PLUGIN_URL . 'public/css/frontend.css',
			array(),
			BAS_VERSION
		);

		wp_enqueue_script(
			'bas-frontend',
			BAS_PLUGIN_URL . 'public/js/frontend.js',
			array( 'jquery' ),
			BAS_VERSION,
			true
		);

		wp_localize_script(
			'bas-frontend',
			'basPublic',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'bas_search_nonce' ),
				'debounceTime' => 300, // 300ms debounce.
				'i18n'         => array(
					'noResults'    => __( 'No results found.', 'box-ai-search' ),
					'error'        => __( 'An error occurred while searching. Please try again.', 'box-ai-search' ),
					'emptyQuery'   => __( 'Please enter a search query.', 'box-ai-search' ),
					'minLength'    => __( 'Please enter at least 3 characters.', 'box-ai-search' ),
				),
			)
		);

		self::$assets_enqueued = true;
	}

	/**
	 * Handle AJAX search request.
	 *
	 * @since 1.0.0
	 */
	public function ajax_search() {
		// Verify nonce.
		check_ajax_referer( 'bas_search_nonce', 'nonce' );

		// Get and sanitize query.
		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

		// Validate query.
		if ( empty( $query ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter a search query.', 'box-ai-search' ),
			) );
		}

		if ( strlen( $query ) < 3 ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter at least 3 characters.', 'box-ai-search' ),
			) );
		}

		// Get additional parameters.
		$items = isset( $_POST['items'] ) ? json_decode( wp_unslash( $_POST['items'] ), true ) : array();

		// Perform search.
		$cache_expiration = get_option( 'bas_cache_expiration', 3600 );
		$cache            = new Cache( $cache_expiration );
		$box_ai           = new BoxAI( $cache );

		$args = array();
		if ( ! empty( $items ) && is_array( $items ) ) {
			$args['items'] = $items;
		}

		$results = $box_ai->search( $query, $args );

		// Handle errors.
		if ( is_wp_error( $results ) ) {
			wp_send_json_error( array(
				'message' => $results->get_error_message(),
			) );
		}

		// Format results for display.
		$formatted_results = $this->format_results( $results );

		wp_send_json_success( array(
			'results' => $formatted_results,
			'query'   => $query,
		) );
	}

	/**
	 * Format search results for display.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  array $results Raw search results from Box AI.
	 * @return array Formatted results.
	 */
	private function format_results( $results ) {
		$formatted = array();

		// Check if we have an answer.
		if ( isset( $results['answer'] ) ) {
			$formatted['answer'] = array(
				'text'       => wp_kses_post( $results['answer'] ),
				'created_at' => isset( $results['created_at'] ) ? esc_html( $results['created_at'] ) : '',
			);
		}

		// Check if we have citations.
		if ( isset( $results['citations'] ) && is_array( $results['citations'] ) ) {
			$formatted['citations'] = array();

			foreach ( $results['citations'] as $citation ) {
				$formatted['citations'][] = array(
					'content' => isset( $citation['content'] ) ? wp_kses_post( $citation['content'] ) : '',
					'type'    => isset( $citation['type'] ) ? esc_html( $citation['type'] ) : '',
				);
			}
		}

		// Check if we have items (referenced documents).
		if ( isset( $results['items'] ) && is_array( $results['items'] ) ) {
			$formatted['items'] = array();

			foreach ( $results['items'] as $item ) {
				$formatted['items'][] = array(
					'id'   => isset( $item['id'] ) ? esc_html( $item['id'] ) : '',
					'type' => isset( $item['type'] ) ? esc_html( $item['type'] ) : '',
					'name' => isset( $item['name'] ) ? esc_html( $item['name'] ) : '',
				);
			}
		}

		return $formatted;
	}
}
