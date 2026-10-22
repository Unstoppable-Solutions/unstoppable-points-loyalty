<?php
/**
 * AJAX Handler Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Ajax Class.
 */
class UPRF_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_uprf_apply_points', array( $this, 'apply_points' ) );
		add_action( 'wp_ajax_uprf_remove_points', array( $this, 'remove_points' ) );
	}

	/**
	 * Apply points discount.
	 */
	public function apply_points() {
		check_ajax_referer( 'uprf_cart_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array( 'message' => __( 'You must be logged in.', 'unstoppable-points-loyalty' ) )
			);
		}

		$uprf_points = isset( $_POST['points'] ) ? absint( wp_unslash( $_POST['points'] ) ) : 0;
		if ( $uprf_points <= 0 ) {
			wp_send_json_error(
				array( 'message' => __( 'Please enter a valid number of points.', 'unstoppable-points-loyalty' ) )
			);
		}

		$uprf_user_id = get_current_user_id();
		$uprf_balance = UPRF()->manager->get_user_points( $uprf_user_id );

		if ( $uprf_points > $uprf_balance ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have enough points.', 'unstoppable-points-loyalty' ) )
			);
		}

		// Check minimum.
		$uprf_min_redemption = absint( get_option( 'uprf_min_redemption', 100 ) );
		if ( $uprf_points < $uprf_min_redemption ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: minimum points */
						__( 'Minimum redemption is %d points.', 'unstoppable-points-loyalty' ),
						$uprf_min_redemption
					),
				)
			);
		}

		// Check maximum.
		$uprf_max_redemption = absint( get_option( 'uprf_max_redemption', 0 ) );
		if ( $uprf_max_redemption > 0 && $uprf_points > $uprf_max_redemption ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: maximum points */
						__( 'Maximum redemption is %d points.', 'unstoppable-points-loyalty' ),
						$uprf_max_redemption
					),
				)
			);
		}

		// Cap at what the cart is actually worth. Without this, customers can submit more
		// points than the cart is worth (the form's max attribute is client-side only) and
		// lose the excess to the cart-total floor of 0.
		if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		if ( WC()->cart ) {
			$uprf_max_redeemable = UPRF_Cart_Checkout::get_max_redeemable_points();
			if ( $uprf_points > $uprf_max_redeemable ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %d: maximum points for this cart */
							__( 'You can only redeem up to %d points on this cart.', 'unstoppable-points-loyalty' ),
							$uprf_max_redeemable
						),
					)
				);
			}
		}

		// Store in session.
		if ( WC()->session ) {
			WC()->session->set( 'uprf_points_applied', $uprf_points );
		}

		$uprf_discount     = UPRF()->manager->calculate_discount( $uprf_points );
		$uprf_points_label = UPRF()->get_points_label( $uprf_points );

		wp_send_json_success(
			array(
				'message'  => sprintf(
					/* translators: %1$d: points, %2$s: points label, %3$s: discount amount */
					__( '%1$d %2$s applied for a %3$s discount!', 'unstoppable-points-loyalty' ),
					$uprf_points,
					$uprf_points_label,
					wc_price( $uprf_discount )
				),
				'discount' => $uprf_discount,
			)
		);
	}

	/**
	 * Remove points discount.
	 */
	public function remove_points() {
		check_ajax_referer( 'uprf_cart_nonce', 'nonce' );

		if ( WC()->session ) {
			WC()->session->set( 'uprf_points_applied', 0 );
		}

		wp_send_json_success(
			array( 'message' => __( 'Points discount removed.', 'unstoppable-points-loyalty' ) )
		);
	}
}
