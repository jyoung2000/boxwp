/**
 * Box AI Search - Frontend JavaScript
 *
 * @package BoxAISearch
 * @since   1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Debounce function to limit how often a function is called.
	 *
	 * @param {Function} func      The function to debounce.
	 * @param {number}   wait      The debounce wait time in milliseconds.
	 * @param {boolean}  immediate Whether to execute immediately.
	 * @return {Function} The debounced function.
	 */
	function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this;
			var args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) {
					func.apply(context, args);
				}
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) {
				func.apply(context, args);
			}
		};
	}

	/**
	 * Initialize Box AI Search.
	 */
	function initializeSearch() {
		$('.bas-search-container').each(function() {
			var $container = $(this);
			var $input = $container.find('.bas-search-input');
			var $button = $container.find('.bas-search-button');
			var $loading = $container.find('.bas-search-loading');
			var $results = $container.find('.bas-search-results');
			var currentRequest = null;

			/**
			 * Perform search.
			 */
			function performSearch() {
				var query = $input.val().trim();

				// Validate query.
				if (query.length === 0) {
					$results.html('<div class="bas-error">' + basPublic.i18n.emptyQuery + '</div>');
					return;
				}

				if (query.length < 3) {
					$results.html('<div class="bas-error">' + basPublic.i18n.minLength + '</div>');
					return;
				}

				// Abort previous request if still running.
				if (currentRequest !== null) {
					currentRequest.abort();
				}

				// Show loading state.
				$loading.show();
				$results.empty();
				$button.prop('disabled', true);

				// Make AJAX request.
				currentRequest = $.ajax({
					url: basPublic.ajaxUrl,
					type: 'POST',
					data: {
						action: 'bas_search',
						nonce: basPublic.nonce,
						query: query
					},
					success: function(response) {
						if (response.success) {
							renderResults(response.data.results);
						} else {
							$results.html('<div class="bas-error">' + response.data.message + '</div>');
						}
					},
					error: function(xhr) {
						if (xhr.statusText !== 'abort') {
							$results.html('<div class="bas-error">' + basPublic.i18n.error + '</div>');
						}
					},
					complete: function(xhr) {
						if (xhr.statusText !== 'abort') {
							$loading.hide();
							$button.prop('disabled', false);
							currentRequest = null;
						}
					}
				});
			}

			/**
			 * Render search results.
			 *
			 * @param {Object} results The search results.
			 */
			function renderResults(results) {
				var html = '';

				if (!results || (!results.answer && !results.citations && !results.items)) {
					html = '<div class="bas-no-results">' + basPublic.i18n.noResults + '</div>';
					$results.html(html);
					return;
				}

				html += '<div class="bas-results-container">';

				// Render answer.
				if (results.answer && results.answer.text) {
					html += '<div class="bas-answer">';
					html += '<h3 class="bas-answer-title">Answer</h3>';
					html += '<div class="bas-answer-text">' + results.answer.text + '</div>';
					if (results.answer.created_at) {
						html += '<div class="bas-answer-meta">' + results.answer.created_at + '</div>';
					}
					html += '</div>';
				}

				// Render citations.
				if (results.citations && results.citations.length > 0) {
					html += '<div class="bas-citations">';
					html += '<h4 class="bas-citations-title">Sources</h4>';
					html += '<ul class="bas-citations-list">';
					results.citations.forEach(function(citation) {
						html += '<li class="bas-citation">';
						if (citation.content) {
							html += '<div class="bas-citation-content">' + citation.content + '</div>';
						}
						if (citation.type) {
							html += '<div class="bas-citation-type">' + citation.type + '</div>';
						}
						html += '</li>';
					});
					html += '</ul>';
					html += '</div>';
				}

				// Render referenced items.
				if (results.items && results.items.length > 0) {
					html += '<div class="bas-items">';
					html += '<h4 class="bas-items-title">Referenced Documents</h4>';
					html += '<ul class="bas-items-list">';
					results.items.forEach(function(item) {
						html += '<li class="bas-item">';
						if (item.name) {
							html += '<span class="bas-item-name">' + item.name + '</span>';
						}
						if (item.type) {
							html += ' <span class="bas-item-type">(' + item.type + ')</span>';
						}
						html += '</li>';
					});
					html += '</ul>';
					html += '</div>';
				}

				html += '</div>';

				$results.html(html);
			}

			// Debounced search function.
			var debouncedSearch = debounce(performSearch, basPublic.debounceTime || 300);

			// Event listeners.
			$input.on('input', debouncedSearch);

			$input.on('keypress', function(e) {
				if (e.which === 13) { // Enter key
					e.preventDefault();
					performSearch();
				}
			});

			$button.on('click', function(e) {
				e.preventDefault();
				performSearch();
			});
		});
	}

	// Initialize on document ready.
	$(document).ready(function() {
		initializeSearch();
	});

})(jQuery);
