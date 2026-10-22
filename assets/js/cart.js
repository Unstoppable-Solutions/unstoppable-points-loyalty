/**
 * Cart JavaScript
 *
 * @package Unstoppable_Points_Rewards_Free
 */

/* global jQuery, uprfCart */
(function($) {
	'use strict';

	var UPRF_Cart = {
		init: function() {
			$(document.body).on('click', '.uprf-apply-points', this.applyPoints);
			$(document.body).on('click', '.uprf-remove-points', this.removePoints);
		},

		applyPoints: function(e) {
			e.preventDefault();

			var $button = $(this);
			var $container = $button.closest('.uprf-points-redemption');
			var $input = $container.find('.uprf-points-input');
			var $message = $container.find('.uprf-message');
			var uprfPointsValue = parseInt($input.val(), 10);

			if (isNaN(uprfPointsValue) || uprfPointsValue <= 0) {
				UPRF_Cart.showMessage($message, 'error', 'Please enter a valid number of points.');
				return;
			}

			$button.prop('disabled', true).text('Applying...');

			$.ajax({
				url: uprfCart.ajaxUrl,
				type: 'POST',
				data: {
					action: 'uprf_apply_points',
					nonce: uprfCart.nonce,
					points: uprfPointsValue
				},
				success: function(response) {
					if (response.success) {
						UPRF_Cart.showMessage($message, 'success', response.data.message);
						// Refresh cart/checkout.
						if ($('form.checkout').length) {
							$(document.body).trigger('update_checkout');
						} else {
							$('[name="update_cart"]').prop('disabled', false).trigger('click');
						}
						// Reload after short delay to show updated form.
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						UPRF_Cart.showMessage($message, 'error', response.data.message);
						$button.prop('disabled', false).text('Apply');
					}
				},
				error: function() {
					UPRF_Cart.showMessage($message, 'error', 'An error occurred. Please try again.');
					$button.prop('disabled', false).text('Apply');
				}
			});
		},

		removePoints: function(e) {
			e.preventDefault();

			var $button = $(this);
			var $container = $button.closest('.uprf-points-redemption');
			var $message = $container.find('.uprf-message');

			$button.prop('disabled', true).text('Removing...');

			$.ajax({
				url: uprfCart.ajaxUrl,
				type: 'POST',
				data: {
					action: 'uprf_remove_points',
					nonce: uprfCart.nonce
				},
				success: function(response) {
					if (response.success) {
						UPRF_Cart.showMessage($message, 'success', response.data.message);
						// Refresh cart/checkout.
						if ($('form.checkout').length) {
							$(document.body).trigger('update_checkout');
						} else {
							$('[name="update_cart"]').prop('disabled', false).trigger('click');
						}
						// Reload after short delay.
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						UPRF_Cart.showMessage($message, 'error', response.data.message || 'An error occurred.');
						$button.prop('disabled', false).text('Remove');
					}
				},
				error: function() {
					UPRF_Cart.showMessage($message, 'error', 'An error occurred. Please try again.');
					$button.prop('disabled', false).text('Remove');
				}
			});
		},

		showMessage: function($element, type, message) {
			$element
				.removeClass('uprf-success uprf-error')
				.addClass('uprf-' + type)
				.html(message)
				.show();
		}
	};

	$(document).ready(function() {
		UPRF_Cart.init();
	});

})(jQuery);
