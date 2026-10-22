<?php
/**
 * Shortcodes Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Shortcodes Class.
 */
class UPRF_Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'uprf_points_balance', array( $this, 'points_balance' ) );
		add_shortcode( 'uprf_points_history', array( $this, 'points_history' ) );
	}

	/**
	 * Display user's points balance.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function points_balance( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$uprf_atts = shortcode_atts(
			array(
				'show_label' => 'yes',
			),
			$atts,
			'uprf_points_balance'
		);

		$uprf_user_id = get_current_user_id();
		$uprf_balance = UPRF()->manager->get_user_points( $uprf_user_id );

		if ( 'yes' === $uprf_atts['show_label'] ) {
			$uprf_label = UPRF()->get_points_label( $uprf_balance );
			return sprintf( '%s %s', esc_html( number_format_i18n( $uprf_balance ) ), esc_html( $uprf_label ) );
		}

		return esc_html( number_format_i18n( $uprf_balance ) );
	}

	/**
	 * Display user's points history.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function points_history( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$uprf_atts = shortcode_atts(
			array(
				'limit' => 10,
			),
			$atts,
			'uprf_points_history'
		);

		$uprf_user_id = get_current_user_id();
		$uprf_history = UPRF()->manager->get_user_points_history(
			$uprf_user_id,
			array( 'limit' => absint( $uprf_atts['limit'] ) )
		);

		if ( empty( $uprf_history ) ) {
			return '<p>' . esc_html__( 'No points history yet.', 'unstoppable-points-loyalty' ) . '</p>';
		}

		ob_start();
		?>
		<table class="uprf-points-history-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'unstoppable-points-loyalty' ); ?></th>
					<th><?php esc_html_e( 'Description', 'unstoppable-points-loyalty' ); ?></th>
					<th><?php esc_html_e( 'Points', 'unstoppable-points-loyalty' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $uprf_history as $uprf_entry ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $uprf_entry->date_created ) ) ); ?></td>
						<td>
							<?php echo esc_html( UPRF_Manager::get_event_type_label( $uprf_entry->event_type ) ); ?>
							<?php if ( ! empty( $uprf_entry->note ) ) : ?>
								<br><small><?php echo esc_html( $uprf_entry->note ); ?></small>
							<?php endif; ?>
						</td>
						<td class="<?php echo esc_attr( $uprf_entry->points >= 0 ? 'uprf-positive' : 'uprf-negative' ); ?>">
							<?php echo esc_html( $uprf_entry->points >= 0 ? '+' : '' ); ?><?php echo esc_html( number_format_i18n( $uprf_entry->points ) ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}
}
