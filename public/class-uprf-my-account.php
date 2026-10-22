<?php
/**
 * My Account Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_My_Account Class.
 */
class UPRF_My_Account {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_rewards_menu_item' ) );
		add_action( 'init', array( $this, 'add_rewards_endpoint' ) );
		add_action( 'woocommerce_account_rewards_endpoint', array( $this, 'render_rewards_content' ) );
	}

	/**
	 * Add rewards menu item.
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public function add_rewards_menu_item( $items ) {
		$uprf_new_items = array();

		foreach ( $items as $uprf_key => $uprf_label ) {
			$uprf_new_items[ $uprf_key ] = $uprf_label;

			// Add after dashboard.
			if ( 'dashboard' === $uprf_key ) {
				$uprf_new_items['rewards'] = __( 'Rewards', 'unstoppable-points-loyalty' );
			}
		}

		return $uprf_new_items;
	}

	/**
	 * Add rewards endpoint.
	 */
	public function add_rewards_endpoint() {
		add_rewrite_endpoint( 'rewards', EP_ROOT | EP_PAGES );
	}

	/**
	 * Render rewards content.
	 */
	public function render_rewards_content() {
		$uprf_user_id         = get_current_user_id();
		$uprf_balance         = UPRF()->manager->get_user_points( $uprf_user_id );
		$uprf_lifetime        = UPRF()->manager->get_user_lifetime_points( $uprf_user_id );
		$uprf_history         = UPRF()->manager->get_user_points_history( $uprf_user_id, array( 'limit' => 20 ) );
		$uprf_points_label    = UPRF()->get_points_label( $uprf_balance );
		$uprf_redemption_value = UPRF()->manager->calculate_discount( $uprf_balance );

		wc_get_template(
			'myaccount/rewards.php',
			array(
				'balance'          => $uprf_balance,
				'lifetime'         => $uprf_lifetime,
				'history'          => $uprf_history,
				'points_label'     => $uprf_points_label,
				'redemption_value' => $uprf_redemption_value,
			),
			'',
			UPRF_PLUGIN_DIR . 'templates/'
		);
	}
}
