<?php
/**
 * Order Handler Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Order Class.
 */
class UPRF_Order {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'award_points_for_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'handle_order_cancelled' ), 10, 1 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'handle_order_refunded' ), 10, 1 );
	}

	/**
	 * Award points when order is completed.
	 *
	 * @param int $order_id Order ID.
	 */
	public function award_points_for_order( $order_id ) {
		$uprf_order = wc_get_order( $order_id );
		if ( ! $uprf_order ) {
			return;
		}

		// Check if points already awarded.
		if ( 'yes' === $uprf_order->get_meta( '_uprf_points_awarded' ) ) {
			return;
		}

		$uprf_user_id = $uprf_order->get_user_id();
		if ( ! $uprf_user_id ) {
			return; // Guest orders don't earn points.
		}

		$uprf_points = $this->calculate_points_for_order( $uprf_order );
		if ( $uprf_points <= 0 ) {
			return;
		}

		// Award points.
		$uprf_result = UPRF()->manager->add_points(
			$uprf_user_id,
			$uprf_points,
			'purchase',
			array(
				'order_id' => $order_id,
				'note'     => sprintf(
					/* translators: %s: order number */
					__( 'Points earned for order #%s', 'unstoppable-points-loyalty' ),
					$uprf_order->get_order_number()
				),
			)
		);

		if ( $uprf_result ) {
			$uprf_order->update_meta_data( '_uprf_points_awarded', 'yes' );
			$uprf_order->update_meta_data( '_uprf_points_earned', $uprf_points );
			$uprf_order->save();

			// Fire action for email notification.
			do_action( 'uprf_points_awarded_for_order', $uprf_user_id, $uprf_points, $uprf_order );
		}
	}

	/**
	 * Calculate points for an order.
	 *
	 * @param WC_Order $order Order object.
	 * @return int
	 */
	private function calculate_points_for_order( $order ) {
		$uprf_total = $order->get_total();

		// Subtract any points discount applied to this order.
		$uprf_points_discount = floatval( $order->get_meta( '_uprf_discount_amount' ) );
		$uprf_total           = $uprf_total - $uprf_points_discount;

		if ( $uprf_total <= 0 ) {
			return 0;
		}

		return UPRF()->manager->calculate_points( $uprf_total );
	}

	/**
	 * Handle cancelled order - reverse points.
	 *
	 * @param int $order_id Order ID.
	 */
	public function handle_order_cancelled( $order_id ) {
		$this->reverse_order_points( $order_id, 'order_cancelled' );
	}

	/**
	 * Handle refunded order - reverse points.
	 *
	 * @param int $order_id Order ID.
	 */
	public function handle_order_refunded( $order_id ) {
		$this->reverse_order_points( $order_id, 'order_refunded' );
	}

	/**
	 * Reverse points for an order.
	 *
	 * @param int    $order_id   Order ID.
	 * @param string $event_type Event type.
	 */
	private function reverse_order_points( $order_id, $event_type ) {
		$uprf_order = wc_get_order( $order_id );
		if ( ! $uprf_order ) {
			return;
		}

		// Check if points were awarded.
		if ( 'yes' !== $uprf_order->get_meta( '_uprf_points_awarded' ) ) {
			return;
		}

		// Check if already reversed.
		if ( 'yes' === $uprf_order->get_meta( '_uprf_points_reversed' ) ) {
			return;
		}

		$uprf_user_id = $uprf_order->get_user_id();
		if ( ! $uprf_user_id ) {
			return;
		}

		$uprf_points_earned = absint( $uprf_order->get_meta( '_uprf_points_earned' ) );
		if ( $uprf_points_earned <= 0 ) {
			return;
		}

		// Deduct the points that were earned.
		$uprf_result = UPRF()->manager->deduct_points(
			$uprf_user_id,
			$uprf_points_earned,
			$event_type,
			array(
				'order_id' => $order_id,
				'note'     => sprintf(
					/* translators: %s: order number */
					__( 'Points reversed for order #%s', 'unstoppable-points-loyalty' ),
					$uprf_order->get_order_number()
				),
			)
		);

		if ( $uprf_result ) {
			$uprf_order->update_meta_data( '_uprf_points_reversed', 'yes' );
			$uprf_order->save();
		}

		// Also restore any redeemed points if applicable.
		$uprf_points_redeemed = absint( $uprf_order->get_meta( '_uprf_points_redeemed' ) );
		if ( $uprf_points_redeemed > 0 ) {
			UPRF()->manager->add_points(
				$uprf_user_id,
				$uprf_points_redeemed,
				$event_type,
				array(
					'order_id' => $order_id,
					'note'     => sprintf(
						/* translators: %s: order number */
						__( 'Redeemed points restored for cancelled order #%s', 'unstoppable-points-loyalty' ),
						$uprf_order->get_order_number()
					),
				)
			);
		}
	}
}
