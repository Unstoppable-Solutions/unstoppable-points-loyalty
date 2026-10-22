<?php
/**
 * Emails Handler Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Emails Class.
 */
class UPRF_Emails {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ) );
		add_action( 'uprf_points_awarded_for_order', array( $this, 'send_points_earned_email' ), 10, 3 );
	}

	/**
	 * Register email classes.
	 *
	 * @param array $emails Email classes.
	 * @return array
	 */
	public function register_emails( $emails ) {
		require_once UPRF_PLUGIN_DIR . 'includes/emails/class-uprf-email-points-earned.php';

		$emails['UPRF_Email_Points_Earned'] = new UPRF_Email_Points_Earned();

		return $emails;
	}

	/**
	 * Send points earned email.
	 *
	 * @param int      $user_id User ID.
	 * @param int      $points  Points earned.
	 * @param WC_Order $order   Order object.
	 */
	public function send_points_earned_email( $user_id, $points, $order ) {
		$uprf_mailer = WC()->mailer();
		$uprf_emails = $uprf_mailer->get_emails();

		if ( isset( $uprf_emails['UPRF_Email_Points_Earned'] ) ) {
			$uprf_emails['UPRF_Email_Points_Earned']->trigger( $user_id, $points, $order );
		}
	}
}
