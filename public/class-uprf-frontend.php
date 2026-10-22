<?php
/**
 * Frontend Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Frontend Class.
 */
class UPRF_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) {
			return;
		}

		wp_enqueue_style(
			'uprf-frontend',
			UPRF_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			UPRF_VERSION
		);

		if ( is_cart() || is_checkout() ) {
			wp_enqueue_script(
				'uprf-cart',
				UPRF_PLUGIN_URL . 'assets/js/cart.js',
				array( 'jquery' ),
				UPRF_VERSION,
				true
			);

			wp_localize_script(
				'uprf-cart',
				'uprfCart',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'uprf_cart_nonce' ),
				)
			);
		}
	}
}
