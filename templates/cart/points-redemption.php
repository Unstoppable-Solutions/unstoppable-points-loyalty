<?php
/**
 * Points Redemption Template
 *
 * @package Unstoppable_Points_Rewards_Free
 * @var int    $balance         Current points balance.
 * @var int    $points_applied  Points currently applied.
 * @var int    $max_redeemable  Maximum redeemable points.
 * @var string $points_label    Points label (singular/plural).
 * @var float  $discount_value  Potential discount value.
 * @var int    $min_redemption  Minimum points to redeem.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="uprf-points-redemption">
	<h3><?php esc_html_e( 'Redeem Points', 'unstoppable-points-loyalty' ); ?></h3>

	<p class="uprf-balance-info">
		<?php
		printf(
			/* translators: %1$s: points balance, %2$s: points label, %3$s: discount value */
			esc_html__( 'You have %1$s %2$s available (worth %3$s).', 'unstoppable-points-loyalty' ),
			'<strong>' . esc_html( number_format_i18n( $balance ) ) . '</strong>',
			esc_html( $points_label ),
			wp_kses_post( wc_price( $discount_value ) )
		);
		?>
	</p>

	<?php if ( $points_applied > 0 ) : ?>
		<div class="uprf-points-applied">
			<p>
				<?php
				printf(
					/* translators: %1$d: points applied, %2$s: points label */
					esc_html__( '%1$d %2$s applied to this order.', 'unstoppable-points-loyalty' ),
					intval( $points_applied ),
					esc_html( $points_label )
				);
				?>
			</p>
			<button type="button" class="button uprf-remove-points">
				<?php esc_html_e( 'Remove', 'unstoppable-points-loyalty' ); ?>
			</button>
		</div>
	<?php else : ?>
		<div class="uprf-redemption-form">
			<label for="uprf-points-input">
				<?php
				printf(
					/* translators: %d: minimum points */
					esc_html__( 'Enter points to redeem (min %d):', 'unstoppable-points-loyalty' ),
					intval( $min_redemption )
				);
				?>
			</label>
			<input 
				type="number" 
				id="uprf-points-input" 
				class="uprf-points-input" 
				min="<?php echo esc_attr( $min_redemption ); ?>" 
				max="<?php echo esc_attr( $max_redeemable ); ?>" 
				value="<?php echo esc_attr( $max_redeemable ); ?>"
			>
			<button type="button" class="button uprf-apply-points">
				<?php esc_html_e( 'Apply', 'unstoppable-points-loyalty' ); ?>
			</button>
		</div>
	<?php endif; ?>

	<div class="uprf-message" style="display: none;"></div>
</div>
