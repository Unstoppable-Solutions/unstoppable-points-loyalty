<?php
/**
 * Cart & Checkout Handler Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Cart_Checkout Class.
 */
class UPRF_Cart_Checkout {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Display redemption form.
		add_action( 'woocommerce_before_checkout_form', array( $this, 'display_redemption_form' ), 5 );
		add_action( 'woocommerce_before_cart_totals', array( $this, 'display_redemption_form' ) );

		// Apply discount.
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_points_discount' ) );

		// Save redemption data to order.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_order_redemption' ), 10, 1 );

		// Display points earned message.
		add_action( 'woocommerce_thankyou', array( $this, 'display_points_earned_message' ) );
	}

	/**
	 * Display the redemption form.
	 */
	public function display_redemption_form() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$uprf_user_id = get_current_user_id();
		$uprf_balance = UPRF()->manager->get_user_points( $uprf_user_id );

		if ( $uprf_balance <= 0 ) {
			return;
		}

		$uprf_min_redemption = absint( get_option( 'uprf_min_redemption', 100 ) );
		if ( $uprf_balance < $uprf_min_redemption ) {
			return;
		}

		$uprf_points_applied = WC()->session ? WC()->session->get( 'uprf_points_applied', 0 ) : 0;
		$uprf_max_discount   = self::get_max_redeemable_points();
		$uprf_points_label   = UPRF()->get_points_label( $uprf_balance );
		$uprf_discount_value = UPRF()->manager->calculate_discount( $uprf_balance );

		wc_get_template(
			'cart/points-redemption.php',
			array(
				'balance'         => $uprf_balance,
				'points_applied'  => $uprf_points_applied,
				'max_redeemable'  => $uprf_max_discount,
				'points_label'    => $uprf_points_label,
				'discount_value'  => $uprf_discount_value,
				'min_redemption'  => $uprf_min_redemption,
			),
			'',
			UPRF_PLUGIN_DIR . 'templates/'
		);
	}

	/**
	 * Get maximum redeemable points for current cart.
	 *
	/**
	 * Get maximum redeemable points for the current cart.
	 *
	 * Caps the user's redeemable points by THREE constraints:
	 *  - The user's current balance
	 *  - What the cart subtotal is worth (so customers can't redeem more than the cart total)
	 *  - The store's configured maximum redemption per order
	 *
	 * Static + public so AJAX handlers can reuse the exact same logic the cart uses.
	 *
	 * @return int
	 */
	public static function get_max_redeemable_points() {
		if ( ! WC()->cart ) {
			return 0;
		}

		$uprf_user_id    = get_current_user_id();
		$uprf_balance    = UPRF()->manager->get_user_points( $uprf_user_id );
		$uprf_cart_total = WC()->cart->get_subtotal();

		// Calculate max points that can be redeemed.
		$uprf_redemption_ratio = UPRF()->get_redemption_ratio();
		if ( $uprf_redemption_ratio <= 0 ) {
			return 0;
		}

		$uprf_max_points_for_cart = (int) floor( $uprf_cart_total / $uprf_redemption_ratio );
		$uprf_max_redeemable      = min( $uprf_balance, $uprf_max_points_for_cart );

		// Check max redemption setting.
		$uprf_max_redemption = absint( get_option( 'uprf_max_redemption', 0 ) );
		if ( $uprf_max_redemption > 0 ) {
			$uprf_max_redeemable = min( $uprf_max_redeemable, $uprf_max_redemption );
		}

		return $uprf_max_redeemable;
	}

	/**
	 * Apply points discount to cart.
	 *
	 * @param WC_Cart $cart Cart object.
	 */
	public function apply_points_discount( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! WC()->session ) {
			return;
		}

		$uprf_points_applied = WC()->session->get( 'uprf_points_applied', 0 );
		if ( $uprf_points_applied <= 0 ) {
			return;
		}

		// Validate user still has enough points.
		$uprf_user_id = get_current_user_id();
		if ( ! $uprf_user_id ) {
			WC()->session->set( 'uprf_points_applied', 0 );
			return;
		}

		$uprf_balance = UPRF()->manager->get_user_points( $uprf_user_id );
		if ( $uprf_balance < $uprf_points_applied ) {
			$uprf_points_applied = $uprf_balance;
			WC()->session->set( 'uprf_points_applied', $uprf_points_applied );
		}

		// Also cap at what the cart is worth, so users can't lose points to the cart-total floor of 0.
		$uprf_max_redeemable = self::get_max_redeemable_points();
		if ( $uprf_max_redeemable < $uprf_points_applied ) {
			$uprf_points_applied = $uprf_max_redeemable;
			WC()->session->set( 'uprf_points_applied', $uprf_points_applied );
		}

		if ( $uprf_points_applied <= 0 ) {
			return;
		}

		$uprf_discount = UPRF()->manager->calculate_discount( $uprf_points_applied );
		if ( $uprf_discount <= 0 ) {
			return;
		}

		$uprf_points_label = UPRF()->get_points_label( $uprf_points_applied );

		$cart->add_fee(
			sprintf(
				/* translators: %1$d: points, %2$s: points label */
				__( 'Points Discount (%1$d %2$s)', 'unstoppable-points-loyalty' ),
				$uprf_points_applied,
				$uprf_points_label
			),
			-$uprf_discount
		);
	}

	/**
	 * Process redemption when order is placed.
	 *
	 * @param int $order_id Order ID.
	 */
	public function process_order_redemption( $order_id ) {
		if ( ! WC()->session ) {
			return;
		}

		$uprf_points_applied = WC()->session->get( 'uprf_points_applied', 0 );
		if ( $uprf_points_applied <= 0 ) {
			return;
		}

		$uprf_order = wc_get_order( $order_id );
		if ( ! $uprf_order ) {
			return;
		}

		$uprf_user_id = $uprf_order->get_user_id();
		if ( ! $uprf_user_id ) {
			return;
		}

		$uprf_discount = UPRF()->manager->calculate_discount( $uprf_points_applied );

		// Deduct points.
		$uprf_result = UPRF()->manager->deduct_points(
			$uprf_user_id,
			$uprf_points_applied,
			'redemption',
			array(
				'order_id' => $order_id,
				'note'     => sprintf(
					/* translators: %s: order number */
					__( 'Points redeemed for order #%s', 'unstoppable-points-loyalty' ),
					$uprf_order->get_order_number()
				),
			)
		);

		if ( $uprf_result ) {
			$uprf_order->update_meta_data( '_uprf_points_redeemed', $uprf_points_applied );
			$uprf_order->update_meta_data( '_uprf_discount_amount', $uprf_discount );
			$uprf_order->save();
		} else {
			// Deduction failed — likely a concurrent checkout consumed the balance first, or
			// the user's balance changed between the cart-fee stage and order completion.
			// The discount fee is already locked into the order total. Fail closed: hold the
			// order so a human can review (refund the customer, reverse the discount, or accept
			// the loss as goodwill — admin's call).
			$uprf_order->update_meta_data( '_uprf_redemption_failed', 'yes' );
			$uprf_order->update_meta_data( '_uprf_points_attempted', $uprf_points_applied );
			$uprf_order->update_meta_data( '_uprf_discount_amount', $uprf_discount );
			$uprf_order->add_order_note(
				sprintf(
					/* translators: %1$d: points attempted, %2$s: discount amount */
					__( 'Points redemption could not be completed: customer did not have %1$d points available at order placement (possible concurrent checkout). The %2$s discount was applied to the order total but no points were deducted. Please review.', 'unstoppable-points-loyalty' ),
					$uprf_points_applied,
					wc_price( $uprf_discount )
				)
			);
			$uprf_order->update_status( 'on-hold', __( 'Points redemption could not be completed.', 'unstoppable-points-loyalty' ) );
			$uprf_order->save();
		}

		// Clear session.
		WC()->session->set( 'uprf_points_applied', 0 );
	}

	/**
	 * Display points earned message on thank you page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function display_points_earned_message( $order_id ) {
		$uprf_order = wc_get_order( $order_id );
		if ( ! $uprf_order ) {
			return;
		}

		$uprf_user_id = $uprf_order->get_user_id();
		if ( ! $uprf_user_id ) {
			return;
		}

		// Calculate potential points (actual awarding happens on completed status).
		$uprf_total  = $uprf_order->get_total();
		$uprf_points = UPRF()->manager->calculate_points( $uprf_total );

		if ( $uprf_points <= 0 ) {
			return;
		}

		$uprf_points_label = UPRF()->get_points_label( $uprf_points );

		echo '<div class="woocommerce-message uprf-points-message">';
		printf(
			/* translators: %1$d: points, %2$s: points label */
			esc_html__( 'You will earn %1$d %2$s when this order is completed!', 'unstoppable-points-loyalty' ),
			intval( $uprf_points ),
			esc_html( $uprf_points_label )
		);
		echo '</div>';
	}
}
