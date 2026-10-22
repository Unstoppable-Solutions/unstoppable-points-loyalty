<?php
/**
 * Points Earned Email Template
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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
do_action( 'woocommerce_email_header', $email_heading, $email );

$uprf_points_label = UPRF()->get_points_label( $points_earned );
?>

<p>
	<?php
	printf(
		/* translators: %s: customer name */
		esc_html__( 'Hi %s,', 'unstoppable-points-loyalty' ),
		esc_html( $user_name )
	);
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$d: points earned, %2$s: points label */
		esc_html__( 'Great news! You just earned %1$d %2$s from your recent order.', 'unstoppable-points-loyalty' ),
		intval( $points_earned ),
		esc_html( $uprf_points_label )
	);
	?>
</p>

<div style="margin: 30px 0; padding: 20px; background-color: #f8f8f8; border-radius: 5px; text-align: center;">
	<p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
		<?php esc_html_e( 'Points Earned', 'unstoppable-points-loyalty' ); ?>
	</p>
	<p style="margin: 0; font-size: 36px; font-weight: bold; color: #7f54b3;">
		+<?php echo esc_html( number_format_i18n( $points_earned ) ); ?>
	</p>
</div>

<table cellspacing="0" cellpadding="6" border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" bordercolor="#e5e5e5">
	<tbody>
		<tr>
			<th scope="row" style="text-align: left; padding: 12px; background-color: #f8f8f8;">
				<?php esc_html_e( 'Your Current Balance', 'unstoppable-points-loyalty' ); ?>
			</th>
			<td style="text-align: left; padding: 12px;">
				<?php
				printf(
					/* translators: %1$s: points balance, %2$s: points label */
					'%1$s %2$s',
					esc_html( number_format_i18n( $current_balance ) ),
					esc_html( UPRF()->get_points_label( $current_balance ) )
				);
				?>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align: left; padding: 12px; background-color: #f8f8f8;">
				<?php esc_html_e( 'Points Value', 'unstoppable-points-loyalty' ); ?>
			</th>
			<td style="text-align: left; padding: 12px;">
				<?php echo wp_kses_post( $points_value ); ?>
			</td>
		</tr>
		<?php if ( ! empty( $order_number ) ) : ?>
		<tr>
			<th scope="row" style="text-align: left; padding: 12px; background-color: #f8f8f8;">
				<?php esc_html_e( 'Order', 'unstoppable-points-loyalty' ); ?>
			</th>
			<td style="text-align: left; padding: 12px;">
				#<?php echo esc_html( $order_number ); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<p>
	<?php esc_html_e( 'You can redeem your points at checkout to get a discount on your next order!', 'unstoppable-points-loyalty' ); ?>
</p>

<p>
	<?php esc_html_e( 'Thank you for shopping with us!', 'unstoppable-points-loyalty' ); ?>
</p>

<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
do_action( 'woocommerce_email_footer', $email );
