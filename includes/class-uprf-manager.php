<?php
/**
 * Points Manager Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Manager Class.
 */
class UPRF_Manager {

	/**
	 * Get user points balance.
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	public function get_user_points( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_balance = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(points_balance) FROM {$wpdb->prefix}uprf_user_points WHERE user_id = %d AND points_balance > 0",
				$user_id
			)
		);

		return absint( $uprf_balance );
	}

	/**
	 * Get user lifetime points.
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	public function get_user_lifetime_points( $user_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_lifetime = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(points) FROM {$wpdb->prefix}uprf_user_points WHERE user_id = %d",
				$user_id
			)
		);

		return absint( $uprf_lifetime );
	}

	/**
	 * Add points to user.
	 *
	 * @param int    $user_id    User ID.
	 * @param int    $points     Points to add.
	 * @param string $event_type Event type.
	 * @param array  $args       Additional arguments.
	 * @return bool
	 */
	public function add_points( $user_id, $points, $event_type = 'purchase', $args = array() ) {
		global $wpdb;

		$points = absint( $points );
		if ( $points <= 0 ) {
			return false;
		}

		// Allow Pro to modify points (e.g., tier multipliers).
		$points = apply_filters( 'uprf_points_to_add', $points, $user_id, $event_type );
		$points = absint( $points );

		$uprf_table = $wpdb->prefix . 'uprf_user_points';

		$uprf_data = array(
			'user_id'        => absint( $user_id ),
			'points'         => $points,
			'points_balance' => $points,
			'order_id'       => isset( $args['order_id'] ) ? absint( $args['order_id'] ) : null,
			'date_earned'    => current_time( 'mysql', true ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$uprf_result = $wpdb->insert(
			$uprf_table,
			$uprf_data,
			array( '%d', '%d', '%d', '%d', '%s' )
		);

		if ( ! $uprf_result ) {
			return false;
		}

		$uprf_points_id = $wpdb->insert_id;

		// Log the event.
		$this->log_event(
			array(
				'user_id'        => $user_id,
				'points'         => $points,
				'event_type'     => $event_type,
				'order_id'       => isset( $args['order_id'] ) ? absint( $args['order_id'] ) : null,
				'user_points_id' => $uprf_points_id,
				'note'           => isset( $args['note'] ) ? sanitize_text_field( $args['note'] ) : '',
			)
		);

		// Fire action.
		do_action( 'uprf_points_added', $user_id, $points, $event_type, $args );

		return true;
	}

	/**
	 * Deduct points from user using FIFO.
	 *
	 * @param int    $user_id    User ID.
	 * @param int    $points     Points to deduct.
	 * @param string $event_type Event type.
	 * @param array  $args       Additional arguments.
	 * @return bool
	 */
	public function deduct_points( $user_id, $points, $event_type = 'redemption', $args = array() ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$points  = absint( $points );
		if ( $user_id <= 0 || $points <= 0 ) {
			return false;
		}

		$uprf_table     = $wpdb->prefix . 'uprf_user_points';
		$uprf_lock_name = 'uprf_deduct_' . $user_id;

		// Acquire a MySQL advisory lock so concurrent redemptions for the same user are serialized.
		// This closes a TOCTOU race where two simultaneous checkouts could both pass the balance check
		// and double-spend the user's points.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_lock_acquired = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $uprf_lock_name, 5 ) );
		if ( '1' !== (string) $uprf_lock_acquired ) {
			// Could not acquire lock within timeout — fail closed.
			return false;
		}

		try {
			// Re-read balance INSIDE the critical section so we have an authoritative value.
			$uprf_current_balance = $this->get_user_points( $user_id );
			if ( $uprf_current_balance < $points ) {
				return false;
			}

			$uprf_points_to_deduct = $points;

			// Get rows with remaining balance, ordered by date (FIFO).
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$uprf_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, points_balance FROM {$uprf_table} WHERE user_id = %d AND points_balance > 0 ORDER BY date_earned ASC",
					$user_id
				)
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			foreach ( $uprf_rows as $uprf_row ) {
				if ( $uprf_points_to_deduct <= 0 ) {
					break;
				}

				$uprf_deduct_from_row = min( (int) $uprf_row->points_balance, $uprf_points_to_deduct );
				$uprf_new_balance     = (int) $uprf_row->points_balance - $uprf_deduct_from_row;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update(
					$uprf_table,
					array( 'points_balance' => $uprf_new_balance ),
					array( 'id' => $uprf_row->id ),
					array( '%d' ),
					array( '%d' )
				);

				$uprf_points_to_deduct -= $uprf_deduct_from_row;
			}

			// Log the event.
			$this->log_event(
				array(
					'user_id'    => $user_id,
					'points'     => -$points,
					'event_type' => $event_type,
					'order_id'   => isset( $args['order_id'] ) ? absint( $args['order_id'] ) : null,
					'note'       => isset( $args['note'] ) ? sanitize_text_field( $args['note'] ) : '',
				)
			);

			// Fire action.
			do_action( 'uprf_points_deducted', $user_id, $points, $event_type, $args );

			return true;
		} finally {
			// Always release the lock — even if anything inside the try throws.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $uprf_lock_name ) );
		}
	}

	/**
	 * Log a points event.
	 *
	 * @param array $args Event arguments.
	 * @return bool
	 */
	private function log_event( $args ) {
		global $wpdb;

		$uprf_log_data = array(
			'user_id'        => absint( $args['user_id'] ),
			'points'         => intval( $args['points'] ),
			'event_type'     => sanitize_key( $args['event_type'] ),
			'order_id'       => isset( $args['order_id'] ) ? absint( $args['order_id'] ) : null,
			'user_points_id' => isset( $args['user_points_id'] ) ? absint( $args['user_points_id'] ) : null,
			'note'           => isset( $args['note'] ) ? sanitize_text_field( $args['note'] ) : '',
			'date_created'   => current_time( 'mysql', true ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->insert(
			$wpdb->prefix . 'uprf_points_log',
			$uprf_log_data,
			array( '%d', '%d', '%s', '%d', '%d', '%s', '%s' )
		);
	}

	/**
	 * Calculate points for an amount.
	 *
	 * @param float $amount Amount.
	 * @return int
	 */
	public function calculate_points( $amount ) {
		$uprf_ratio = UPRF()->get_earning_ratio();
		return (int) floor( floatval( $amount ) * $uprf_ratio );
	}

	/**
	 * Calculate discount value for points.
	 *
	 * @param int $points Points.
	 * @return float
	 */
	public function calculate_discount( $points ) {
		$uprf_ratio = UPRF()->get_redemption_ratio();
		return round( absint( $points ) * $uprf_ratio, 2 );
	}

	/**
	 * Get user points history.
	 *
	 * @param int   $user_id User ID.
	 * @param array $args    Query arguments.
	 * @return array
	 */
	public function get_user_points_history( $user_id, $args = array() ) {
		global $wpdb;

		$uprf_defaults = array(
			'limit'  => 20,
			'offset' => 0,
		);

		$uprf_args   = wp_parse_args( $args, $uprf_defaults );
		$uprf_limit  = absint( $uprf_args['limit'] );
		$uprf_offset = absint( $uprf_args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}uprf_points_log WHERE user_id = %d ORDER BY date_created DESC LIMIT %d OFFSET %d",
				$user_id,
				$uprf_limit,
				$uprf_offset
			)
		);

		return $uprf_results ? $uprf_results : array();
	}

	/**
	 * Get event type label.
	 *
	 * @param string $event_type Event type.
	 * @return string
	 */
	public static function get_event_type_label( $event_type ) {
		$uprf_labels = array(
			'purchase'        => __( 'Purchase', 'unstoppable-points-loyalty' ),
			'redemption'      => __( 'Redemption', 'unstoppable-points-loyalty' ),
			'admin_add'       => __( 'Admin Adjustment', 'unstoppable-points-loyalty' ),
			'admin_deduct'    => __( 'Admin Adjustment', 'unstoppable-points-loyalty' ),
			'order_cancelled' => __( 'Order Cancelled', 'unstoppable-points-loyalty' ),
			'order_refunded'  => __( 'Order Refunded', 'unstoppable-points-loyalty' ),
		);

		return isset( $uprf_labels[ $event_type ] ) ? $uprf_labels[ $event_type ] : $event_type;
	}
}
