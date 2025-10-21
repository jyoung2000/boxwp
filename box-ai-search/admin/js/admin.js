/**
 * Box AI Search - Admin JavaScript
 *
 * @package BoxAISearch
 * @since   1.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {

	/**
	 * Test Box API connection.
	 */
	$(document).on('click', '#bas-test-connection', function() {
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
	$(document).on('click', '#bas-clear-cache', function() {
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

	/**
	 * Upload Box JSON configuration.
	 */
	$(document).on('click', '#bas-upload-json', function() {
		var $button = $(this);
		var $file = $('#bas-json-file')[0];
		var $result = $('#bas-json-result');
		var $spinner = $('#bas-json-spinner');

		// Check if file is selected.
		if (!$file.files || !$file.files[0]) {
			$result.html('<div class="notice notice-error inline"><p>Please select a JSON file first.</p></div>');
			return;
		}

		var formData = new FormData();
		formData.append('action', 'bas_upload_json');
		formData.append('nonce', basAdmin.nonce);
		formData.append('json_file', $file.files[0]);

		$button.prop('disabled', true);
		$spinner.addClass('is-active');
		$result.html('');

		$.ajax({
			url: basAdmin.ajaxUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					$result.html('<div class="notice notice-success inline"><p><strong>Success!</strong> ' + response.data.message + '</p><p>Please refresh the page to see the imported credentials.</p></div>');
					// Clear file input.
					$file.value = '';

					// Optionally refresh after 2 seconds
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					$result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$result.html('<div class="notice notice-error inline"><p>An error occurred while uploading the file.</p></div>');
			},
			complete: function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
			}
		});
	});

	}); // End document.ready

})(jQuery);
