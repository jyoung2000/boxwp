/**
 * Box AI Search - Admin JavaScript
 *
 * @package BoxAISearch
 * @since   1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Test Box API connection.
	 */
	$('#bas-test-connection').on('click', function() {
		var $button = $(this);
		var $result = $('#bas-test-result');

		$button.prop('disabled', true).text(basAdmin.testingText || 'Testing...');
		$result.html('');

		$.ajax({
			url: basAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'bas_test_connection',
				nonce: basAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					$result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
				} else {
					$result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$result.html('<div class="notice notice-error inline"><p>An error occurred while testing the connection.</p></div>');
			},
			complete: function() {
				$button.prop('disabled', false).text(basAdmin.testButtonText || 'Test Box API Connection');
			}
		});
	});

	/**
	 * Clear cache.
	 */
	$('#bas-clear-cache').on('click', function() {
		var $button = $(this);
		var $result = $('#bas-clear-cache-result');

		if (!confirm(basAdmin.clearCacheConfirm || 'Are you sure you want to clear all cached results?')) {
			return;
		}

		$button.prop('disabled', true).text(basAdmin.clearingText || 'Clearing...');
		$result.html('');

		$.ajax({
			url: basAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'bas_clear_cache',
				nonce: basAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					$result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
				} else {
					$result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$result.html('<div class="notice notice-error inline"><p>An error occurred while clearing the cache.</p></div>');
			},
			complete: function() {
				$button.prop('disabled', false).text(basAdmin.clearCacheButtonText || 'Clear All Cached Results');
			}
		});
	});

})(jQuery);
