<?php
/**
 * Points Earned Email Template (Plain Text)
 *
 * @package Unstoppable_Points_Rewards_Free
 * @var int    $user_id         User ID.
 * @var string $user_name       User display name.
 * @var int    $points_earned   Points earned.
 * @var int    $current_balance Current points balance.
 * @var string $points_value    Formatted points value.
 * @var int    $order_id        Order ID.
 * @var string $order_number    Order number.
 * @var string $email_heading   Email heading.
 * @var bool   $sent_to_admin   Sent to admin flag.
 * @var bool   $plain_text      Plain text flag.
 * @var object $email           Email object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

$uprf_points_label = UPRF()->get_points_label( $points_earned );

printf(
	/* translators: %s: customer name */
	esc_html__( 'Hi %s,', 'unstoppable-points-loyalty' ),
	esc_html( $user_name )
);
echo "\n\n";

printf(
	/* translators: %1$d: points earned, %2$s: points label */
	esc_html__( 'Great news! You just earned %1$d %2$s from your recent order.', 'unstoppable-points-loyalty' ),
	intval( $points_earned ),
	esc_html( $uprf_points_label )
);
echo "\n\n";

echo "----------------------------------------\n";
echo esc_html__( 'Points Earned:', 'unstoppable-points-loyalty' ) . ' +' . esc_html( number_format_i18n( $points_earned ) ) . "\n";
echo esc_html__( 'Current Balance:', 'unstoppable-points-loyalty' ) . ' ' . esc_html( number_format_i18n( $current_balance ) ) . ' ' . esc_html( UPRF()->get_points_label( $current_balance ) ) . "\n";
echo esc_html__( 'Points Value:', 'unstoppable-points-loyalty' ) . ' ' . esc_html( wp_strip_all_tags( $points_value ) ) . "\n";

if ( ! empty( $order_number ) ) {
	echo esc_html__( 'Order:', 'unstoppable-points-loyalty' ) . ' #' . esc_html( $order_number ) . "\n";
}

echo "----------------------------------------\n\n";

echo esc_html__( 'You can redeem your points at checkout to get a discount on your next order!', 'unstoppable-points-loyalty' );
echo "\n\n";

echo esc_html__( 'Thank you for shopping with us!', 'unstoppable-points-loyalty' );
echo "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
