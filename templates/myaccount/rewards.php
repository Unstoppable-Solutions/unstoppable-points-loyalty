<?php
/**
 * My Account Rewards Template
 *
 * @package Unstoppable_Points_Rewards_Free
 * @var int    $balance          Current points balance.
 * @var int    $lifetime         Lifetime points earned.
 * @var array  $history          Points history.
 * @var string $points_label     Points label.
 * @var float  $redemption_value Redemption value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="uprf-rewards-page">
	<?php do_action( 'uprf_my_account_before_points' ); ?>
	
	<div class="uprf-rewards-summary">
		<div class="uprf-summary-card uprf-balance-card">
			<h3><?php esc_html_e( 'Current Balance', 'unstoppable-points-loyalty' ); ?></h3>
			<div class="uprf-points-value">
				<?php echo esc_html( number_format_i18n( $balance ) ); ?>
			</div>
			<div class="uprf-points-label"><?php echo esc_html( $points_label ); ?></div>
			<?php if ( $balance > 0 ) : ?>
				<div class="uprf-worth">
					<?php
					printf(
						/* translators: %s: currency value */
						esc_html__( 'Worth %s', 'unstoppable-points-loyalty' ),
						wp_kses_post( wc_price( $redemption_value ) )
					);
					?>
				</div>
			<?php endif; ?>
		</div>

		<div class="uprf-summary-card uprf-lifetime-card">
			<h3><?php esc_html_e( 'Lifetime Earned', 'unstoppable-points-loyalty' ); ?></h3>
			<div class="uprf-points-value">
				<?php echo esc_html( number_format_i18n( $lifetime ) ); ?>
			</div>
			<div class="uprf-points-label"><?php echo esc_html( $points_label ); ?></div>
		</div>
	</div>

	<div class="uprf-rewards-history">
		<h3><?php esc_html_e( 'Points History', 'unstoppable-points-loyalty' ); ?></h3>

		<?php if ( empty( $history ) ) : ?>
			<p class="uprf-no-history">
				<?php esc_html_e( 'You have not earned any points yet. Start shopping to earn rewards!', 'unstoppable-points-loyalty' ); ?>
			</p>
		<?php else : ?>
			<table class="uprf-history-table woocommerce-orders-table shop_table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'unstoppable-points-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Description', 'unstoppable-points-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Points', 'unstoppable-points-loyalty' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $history as $uprf_entry ) : ?>
						<tr>
							<td>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $uprf_entry->date_created ) ) ); ?>
							</td>
							<td>
								<?php echo esc_html( UPRF_Manager::get_event_type_label( $uprf_entry->event_type ) ); ?>
								<?php if ( ! empty( $uprf_entry->order_id ) ) : ?>
									<?php
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
									$order = wc_get_order( $uprf_entry->order_id );
									if ( $order ) :
										?>
										<br>
										<small>
											<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
												<?php
												printf(
													/* translators: %s: order number */
													esc_html__( 'Order #%s', 'unstoppable-points-loyalty' ),
													esc_html( $order->get_order_number() )
												);
												?>
											</a>
										</small>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<td class="<?php echo $uprf_entry->points >= 0 ? 'uprf-positive' : 'uprf-negative'; ?>">
								<?php echo $uprf_entry->points >= 0 ? '+' : ''; ?><?php echo esc_html( number_format_i18n( $uprf_entry->points ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<?php do_action( 'uprf_my_account_after_history' ); ?>

	<?php if ( ! defined( 'UPR_PRO_VERSION' ) ) : ?>
	<div class="uprf-upgrade-cta">
		<h4><?php esc_html_e( 'Want More Rewards?', 'unstoppable-points-loyalty' ); ?></h4>
		<p><?php esc_html_e( 'Ask the store owner about membership tiers, referral bonuses, and more ways to earn!', 'unstoppable-points-loyalty' ); ?></p>
	</div>
	<?php endif; ?>
</div>
