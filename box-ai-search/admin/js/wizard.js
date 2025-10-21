/**
 * Box AI Search - Setup Wizard JavaScript
 *
 * @package BoxAISearch
 * @since   1.0.0
 */

(function($) {
	'use strict';

	var BasWizard = {
		currentStep: 1,

		init: function() {
			this.bindEvents();
			this.loadGlobalEvents();
		},

		bindEvents: function() {
			// Step navigation
			$('.bas-next-step').on('click', this.nextStep.bind(this));
			$('.bas-prev-step').on('click', this.prevStep.bind(this));

			// Key generation
			$('#bas-generate-key').on('click', this.generateKey.bind(this));
			$('#bas-copy-key').on('click', this.copyKey.bind(this));

			// Auto configuration
			$('#bas-auto-configure').on('click', this.autoConfigureKey.bind(this));

			// Skip setup
			$('.bas-skip-setup').on('click', this.skipSetup.bind(this));
		},

		loadGlobalEvents: function() {
			// Handle dismiss notice
			$(document).on('click', '.bas-setup-notice .notice-dismiss', function() {
				$.post(ajaxurl, {
					action: 'bas_dismiss_setup_notice',
					nonce: basWizard.nonce
				});
			});
		},

		nextStep: function(e) {
			e.preventDefault();
			var nextStep = $(e.currentTarget).data('next');
			this.goToStep(nextStep);
		},

		prevStep: function(e) {
			e.preventDefault();
			var prevStep = $(e.currentTarget).data('prev');
			this.goToStep(prevStep);
		},

		goToStep: function(stepNumber) {
			// Hide all steps
			$('.bas-wizard-content').hide();

			// Show target step
			$('#bas-step-' + stepNumber).show();

			// Update progress
			$('.bas-wizard-step').removeClass('active');
			$('.bas-wizard-step[data-step="' + stepNumber + '"]').addClass('active');

			// Mark previous steps as completed
			$('.bas-wizard-step').each(function() {
				var step = $(this).data('step');
				if (step < stepNumber) {
					$(this).addClass('completed');
				} else {
					$(this).removeClass('completed');
				}
			});

			// Scroll to top
			$('html, body').animate({ scrollTop: $('.bas-wizard-wrap').offset().top - 32 }, 300);

			this.currentStep = stepNumber;
		},

		generateKey: function(e) {
			e.preventDefault();
			var $button = $(e.currentTarget);
			var originalText = $button.text();

			$button.addClass('bas-loading').text(basWizard.i18n.generating);

			$.ajax({
				url: basWizard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bas_generate_key',
					nonce: basWizard.nonce
				},
				success: function(response) {
					if (response.success) {
						$('#bas-generated-key').text(response.data.code);
						$('#bas-key-display').slideDown();
						$('#bas-verify-step').slideDown();
						$button.prop('disabled', true).text(originalText);
					} else {
						alert(response.data.message || 'Failed to generate key');
						$button.removeClass('bas-loading').text(originalText);
					}
				},
				error: function() {
					alert('An error occurred while generating the key');
					$button.removeClass('bas-loading').text(originalText);
				}
			});
		},

		copyKey: function(e) {
			e.preventDefault();
			var $button = $(e.currentTarget);
			var code = $('#bas-generated-key').text();
			var originalText = $button.text();

			// Try to copy to clipboard
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(code).then(function() {
					$button.addClass('copied').text(basWizard.i18n.copied);
					setTimeout(function() {
						$button.removeClass('copied').text(originalText);
					}, 2000);
				}).catch(function() {
					BasWizard.fallbackCopy(code, $button, originalText);
				});
			} else {
				BasWizard.fallbackCopy(code, $button, originalText);
			}
		},

		fallbackCopy: function(text, $button, originalText) {
			// Fallback for older browsers
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();

			try {
				var successful = document.execCommand('copy');
				if (successful) {
					$button.addClass('copied').text(basWizard.i18n.copied);
					setTimeout(function() {
						$button.removeClass('copied').text(originalText);
					}, 2000);
				} else {
					alert(basWizard.i18n.copyFailed);
				}
			} catch (err) {
				alert(basWizard.i18n.copyFailed);
			}

			$temp.remove();
		},

		autoConfigureKey: function(e) {
			e.preventDefault();
			var $button = $(e.currentTarget);
			var originalText = $button.text();

			if (!confirm('This will automatically add the encryption key to your wp-config.php file. Continue?')) {
				return;
			}

			$button.addClass('bas-loading').text(basWizard.i18n.configuring);

			$.ajax({
				url: basWizard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bas_auto_configure_key',
					nonce: basWizard.nonce
				},
				success: function(response) {
					$button.removeClass('bas-loading').text(originalText);

					var $result = $('#bas-auto-configure-result');

					if (response.success) {
						$result.html(
							'<div class="bas-message success">' +
							'<strong>Success!</strong> ' + response.data.message +
							' <a href="#" onclick="location.reload(); return false;">Refresh page</a> to continue.' +
							'</div>'
						);
						$button.prop('disabled', true);
					} else {
						$result.html(
							'<div class="bas-message error">' +
							'<strong>Error:</strong> ' + response.data.message +
							'</div>'
						);
					}
				},
				error: function() {
					$button.removeClass('bas-loading').text(originalText);
					$('#bas-auto-configure-result').html(
						'<div class="bas-message error">' +
						'<strong>Error:</strong> ' + basWizard.i18n.autoConfigError +
						'</div>'
					);
				}
			});
		},

		skipSetup: function(e) {
			e.preventDefault();

			if (!confirm('Are you sure you want to skip the setup wizard? You can always access it later from the Settings page.')) {
				return;
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'bas_skip_wizard',
					nonce: basWizard.nonce
				},
				success: function() {
					$('.bas-setup-notice').fadeOut();
				}
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		BasWizard.init();
	});

})(jQuery);
