<?php
/**
 * Admin Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Admin Class.
 */
class UPRF_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'plugin_action_links_' . UPRF_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
	}

	/**
	 * Add admin menu under Options Studio.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'unstoppable-studio',
			__( 'Points & Loyalty', 'unstoppable-points-loyalty' ),
			__( 'Points & Loyalty', 'unstoppable-points-loyalty' ),
			'manage_woocommerce',
			'uprf-points-rewards',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Get current tab.
	 *
	 * @return string
	 */
	private function get_current_tab() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
	}

	/**
	 * Get available tabs.
	 *
	 * @return array
	 */
	private function get_tabs() {
		return array(
			'dashboard' => array(
				'label' => __( 'Dashboard', 'unstoppable-points-loyalty' ),
				'icon'  => 'dashicons-dashboard',
			),
			'settings'  => array(
				'label' => __( 'Settings', 'unstoppable-points-loyalty' ),
				'icon'  => 'dashicons-admin-generic',
			),
		);
	}

	/**
	 * Render the admin page with tabs.
	 */
	public function render_admin_page() {
		$uprf_current_tab = $this->get_current_tab();
		$uprf_tabs        = $this->get_tabs();
		?>
		<div class="wrap uprf-admin-wrap">
			<h1><?php esc_html_e( 'Points & Loyalty', 'unstoppable-points-loyalty' ); ?></h1>

			<nav class="uprf-tabs-nav">
				<?php foreach ( $uprf_tabs as $uprf_tab_id => $uprf_tab ) :
					$uprf_url       = admin_url( 'admin.php?page=uprf-points-rewards&tab=' . $uprf_tab_id );
					$uprf_is_active = $uprf_current_tab === $uprf_tab_id;
				?>
					<a href="<?php echo esc_url( $uprf_url ); ?>"
					   class="uprf-tab-link <?php echo $uprf_is_active ? 'active' : ''; ?>">
						<span class="dashicons <?php echo esc_attr( $uprf_tab['icon'] ); ?>"></span>
						<?php echo esc_html( $uprf_tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="uprf-tab-content">
				<?php
				switch ( $uprf_current_tab ) {
					case 'settings':
						$this->render_settings_tab();
						break;
					case 'dashboard':
					default:
						$this->render_dashboard_tab();
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render dashboard tab.
	 */
	private function render_dashboard_tab() {
		global $wpdb;

		// Get stats.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_total_points_issued = $wpdb->get_var(
			"SELECT SUM(points) FROM {$wpdb->prefix}uprf_user_points"
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_total_users = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}uprf_user_points"
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$uprf_points_redeemed = $wpdb->get_var(
			"SELECT ABS(SUM(points)) FROM {$wpdb->prefix}uprf_points_log WHERE points < 0 AND event_type = 'redemption'"
		);

		$uprf_total_points_issued = absint( $uprf_total_points_issued );
		$uprf_total_users         = absint( $uprf_total_users );
		$uprf_points_redeemed     = absint( $uprf_points_redeemed );
		?>
		<div class="uprf-dashboard">
			<div class="uprf-stats-grid">
				<div class="uprf-stat-card">
					<div class="uprf-stat-icon">
						<span class="dashicons dashicons-star-filled"></span>
					</div>
					<div class="uprf-stat-content">
						<h3><?php echo esc_html( number_format_i18n( $uprf_total_points_issued ) ); ?></h3>
						<p><?php esc_html_e( 'Total Points Issued', 'unstoppable-points-loyalty' ); ?></p>
					</div>
				</div>

				<div class="uprf-stat-card">
					<div class="uprf-stat-icon">
						<span class="dashicons dashicons-groups"></span>
					</div>
					<div class="uprf-stat-content">
						<h3><?php echo esc_html( number_format_i18n( $uprf_total_users ) ); ?></h3>
						<p><?php esc_html_e( 'Customers with Points', 'unstoppable-points-loyalty' ); ?></p>
					</div>
				</div>

				<div class="uprf-stat-card">
					<div class="uprf-stat-icon">
						<span class="dashicons dashicons-cart"></span>
					</div>
					<div class="uprf-stat-content">
						<h3><?php echo esc_html( number_format_i18n( $uprf_points_redeemed ) ); ?></h3>
						<p><?php esc_html_e( 'Points Redeemed', 'unstoppable-points-loyalty' ); ?></p>
					</div>
				</div>
			</div>

			<div class="uprf-quick-settings">
				<h2><?php esc_html_e( 'Quick Overview', 'unstoppable-points-loyalty' ); ?></h2>
				<table class="widefat">
					<tbody>
						<tr>
							<th><?php esc_html_e( 'Earning Ratio', 'unstoppable-points-loyalty' ); ?></th>
							<td>
								<?php
								printf(
									/* translators: %s: earning ratio */
									esc_html__( '%s point(s) per currency unit spent', 'unstoppable-points-loyalty' ),
									esc_html( get_option( 'uprf_earning_ratio', 1 ) )
								);
								?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Redemption Value', 'unstoppable-points-loyalty' ); ?></th>
							<td>
								<?php
								$uprf_ratio = floatval( get_option( 'uprf_redemption_ratio', 0.01 ) );
								printf(
									/* translators: %s: currency value */
									esc_html__( '1 point = %s', 'unstoppable-points-loyalty' ),
									wp_kses_post( wc_price( $uprf_ratio ) )
								);
								?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Minimum Redemption', 'unstoppable-points-loyalty' ); ?></th>
							<td>
								<?php
								printf(
									/* translators: %s: minimum points */
									esc_html__( '%s points', 'unstoppable-points-loyalty' ),
									esc_html( number_format_i18n( get_option( 'uprf_min_redemption', 100 ) ) )
								);
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<?php $this->render_upgrade_notice(); ?>
			<?php $this->render_pro_active_notice(); ?>
		</div>
		<?php
	}

	/**
	 * Render Pro Active notice.
	 */
	private function render_pro_active_notice() {
		if ( ! defined( 'UPR_PRO_VERSION' ) ) {
			return;
		}
		?>
		<div class="uprf-pro-active-notice" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #fff; padding: 20px; border-radius: 8px; margin-top: 20px;">
			<h3 style="margin: 0 0 10px; color: #fff;">
				✓ <?php esc_html_e( 'Pro Features Active!', 'unstoppable-points-loyalty' ); ?>
			</h3>
			<p style="margin: 0 0 15px; opacity: 0.9;">
				<?php esc_html_e( 'You have access to membership tiers, referral programs, points expiration, and more.', 'unstoppable-points-loyalty' ); ?>
			</p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=upr-pro-settings' ) ); ?>" class="button" style="background: #fff; color: #28a745; border: none;">
				<?php esc_html_e( 'Manage Pro Settings →', 'unstoppable-points-loyalty' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Render settings tab.
	 */
	private function render_settings_tab() {
		$uprf_earning_ratio   = get_option( 'uprf_earning_ratio', 1 );
		$uprf_redemption_ratio = get_option( 'uprf_redemption_ratio', 0.01 );
		$uprf_min_redemption  = get_option( 'uprf_min_redemption', 100 );
		$uprf_max_redemption  = get_option( 'uprf_max_redemption', 0 );
		$uprf_label_singular  = get_option( 'uprf_points_label_singular', 'Point' );
		$uprf_label_plural    = get_option( 'uprf_points_label_plural', 'Points' );
		?>
		<div class="uprf-settings">
			<form method="post" action="">
				<?php wp_nonce_field( 'uprf_save_settings', 'uprf_settings_nonce' ); ?>

				<div class="uprf-settings-section">
					<h2><?php esc_html_e( 'General Settings', 'unstoppable-points-loyalty' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="uprf_points_label_singular">
									<?php esc_html_e( 'Points Label (Singular)', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="text" id="uprf_points_label_singular" name="uprf_points_label_singular"
									   value="<?php echo esc_attr( $uprf_label_singular ); ?>" class="regular-text">
								<p class="description">
									<?php esc_html_e( 'Label for a single point (e.g., Point, Coin, Star).', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="uprf_points_label_plural">
									<?php esc_html_e( 'Points Label (Plural)', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="text" id="uprf_points_label_plural" name="uprf_points_label_plural"
									   value="<?php echo esc_attr( $uprf_label_plural ); ?>" class="regular-text">
								<p class="description">
									<?php esc_html_e( 'Label for multiple points (e.g., Points, Coins, Stars).', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="uprf-settings-section">
					<h2><?php esc_html_e( 'Earning Settings', 'unstoppable-points-loyalty' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="uprf_earning_ratio">
									<?php esc_html_e( 'Earning Ratio', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="uprf_earning_ratio" name="uprf_earning_ratio"
									   value="<?php echo esc_attr( $uprf_earning_ratio ); ?>"
									   step="0.01" min="0" class="small-text">
								<p class="description">
									<?php esc_html_e( 'Points earned per currency unit spent. E.g., 1 = 1 point per $1 spent.', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="uprf-settings-section">
					<h2><?php esc_html_e( 'Redemption Settings', 'unstoppable-points-loyalty' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="uprf_redemption_ratio">
									<?php esc_html_e( 'Redemption Ratio', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="uprf_redemption_ratio" name="uprf_redemption_ratio"
									   value="<?php echo esc_attr( $uprf_redemption_ratio ); ?>"
									   step="0.001" min="0" class="small-text">
								<p class="description">
									<?php esc_html_e( 'Currency value per point. E.g., 0.01 = 1 point = $0.01, so 100 points = $1.', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="uprf_min_redemption">
									<?php esc_html_e( 'Minimum Redemption', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="uprf_min_redemption" name="uprf_min_redemption"
									   value="<?php echo esc_attr( $uprf_min_redemption ); ?>"
									   step="1" min="0" class="small-text">
								<p class="description">
									<?php esc_html_e( 'Minimum points required to redeem. Set to 0 for no minimum.', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="uprf_max_redemption">
									<?php esc_html_e( 'Maximum Redemption', 'unstoppable-points-loyalty' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="uprf_max_redemption" name="uprf_max_redemption"
									   value="<?php echo esc_attr( $uprf_max_redemption ); ?>"
									   step="1" min="0" class="small-text">
								<p class="description">
									<?php esc_html_e( 'Maximum points that can be redeemed per order. Set to 0 for no maximum.', 'unstoppable-points-loyalty' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<p class="submit">
					<input type="submit" name="uprf_save_settings" class="button button-primary"
						   value="<?php esc_attr_e( 'Save Settings', 'unstoppable-points-loyalty' ); ?>">
				</p>
			</form>

			<?php $this->render_upgrade_notice(); ?>
		</div>
		<?php
	}

	/**
	 * Render upgrade notice (only if Pro is not active).
	 */
	private function render_upgrade_notice() {
		// Don't show upsell if Pro is active.
		if ( defined( 'UPR_PRO_VERSION' ) ) {
			return;
		}
		?>
		<div class="uprf-upgrade-notice">
			<h3>
				<?php esc_html_e( '🚀 Unlock More Features with Pro!', 'unstoppable-points-loyalty' ); ?>
			</h3>
			<p>
				<?php esc_html_e( 'Get membership tiers, referral programs, spin wheel gamification, points expiration, bonus actions, and more!', 'unstoppable-points-loyalty' ); ?>
			</p>
			<ul>
				<li>✅ <?php esc_html_e( 'Membership Tiers with Point Multipliers', 'unstoppable-points-loyalty' ); ?></li>
				<li>✅ <?php esc_html_e( 'Referral Program', 'unstoppable-points-loyalty' ); ?></li>
				<li>✅ <?php esc_html_e( 'Spin-to-Win Wheel', 'unstoppable-points-loyalty' ); ?></li>
				<li>✅ <?php esc_html_e( 'Points Expiration', 'unstoppable-points-loyalty' ); ?></li>
				<li>✅ <?php esc_html_e( 'Birthday & Daily Login Rewards', 'unstoppable-points-loyalty' ); ?></li>
				<li>✅ <?php esc_html_e( 'Advanced Reporting', 'unstoppable-points-loyalty' ); ?></li>
			</ul>
			<a href="https://unstoppablesolutions.app/PluginDetail?slug=unstoppable-points-rewards" target="_blank" class="button button-primary">
				<?php esc_html_e( 'Learn More →', 'unstoppable-points-loyalty' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Save settings.
	 */
	public function save_settings() {
		if ( ! isset( $_POST['uprf_save_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['uprf_settings_nonce'] ) ||
			 ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['uprf_settings_nonce'] ) ), 'uprf_save_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Save settings.
		if ( isset( $_POST['uprf_points_label_singular'] ) ) {
			update_option( 'uprf_points_label_singular', sanitize_text_field( wp_unslash( $_POST['uprf_points_label_singular'] ) ) );
		}
		if ( isset( $_POST['uprf_points_label_plural'] ) ) {
			update_option( 'uprf_points_label_plural', sanitize_text_field( wp_unslash( $_POST['uprf_points_label_plural'] ) ) );
		}
		if ( isset( $_POST['uprf_earning_ratio'] ) ) {
			update_option( 'uprf_earning_ratio', floatval( wp_unslash( $_POST['uprf_earning_ratio'] ) ) );
		}
		if ( isset( $_POST['uprf_redemption_ratio'] ) ) {
			update_option( 'uprf_redemption_ratio', floatval( wp_unslash( $_POST['uprf_redemption_ratio'] ) ) );
		}
		if ( isset( $_POST['uprf_min_redemption'] ) ) {
			update_option( 'uprf_min_redemption', absint( wp_unslash( $_POST['uprf_min_redemption'] ) ) );
		}
		if ( isset( $_POST['uprf_max_redemption'] ) ) {
			update_option( 'uprf_max_redemption', absint( wp_unslash( $_POST['uprf_max_redemption'] ) ) );
		}

		// Add success notice.
		add_action( 'admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved successfully.', 'unstoppable-points-loyalty' ); ?></p>
			</div>
			<?php
		} );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		$uprf_screen = get_current_screen();
		if ( ! $uprf_screen ) {
			return;
		}

		$uprf_allowed_screens = array(
			'options-studio_page_uprf-points-rewards',
			'shop_order',
			'woocommerce_page_wc-orders',
		);

		if ( ! in_array( $uprf_screen->id, $uprf_allowed_screens, true ) && false === strpos( $uprf_screen->id, 'uprf' ) ) {
			return;
		}

		wp_enqueue_style(
			'uprf-admin',
			UPRF_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			UPRF_VERSION
		);

		wp_enqueue_script(
			'uprf-admin',
			UPRF_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			UPRF_VERSION,
			true
		);

		wp_localize_script(
			'uprf-admin',
			'uprfAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'uprf_admin_nonce' ),
			)
		);
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$uprf_settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=uprf-points-rewards&tab=settings' ),
			__( 'Settings', 'unstoppable-points-loyalty' )
		);

		array_unshift( $links, $uprf_settings_link );

		// Add upgrade link.
		$uprf_upgrade_link = sprintf(
			'<a href="%s" target="_blank" style="color: #a36597; font-weight: bold;">%s</a>',
			'https://unstoppablesolutions.app/PluginDetail?slug=unstoppable-points-rewards',
			__( 'Upgrade to Pro', 'unstoppable-points-loyalty' )
		);

		$links[] = $uprf_upgrade_link;

		return $links;
	}

	/**
	 * Add order meta box.
	 */
	public function add_order_meta_box() {
		$uprf_screen = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'uprf-order-points',
			__( 'Points & Loyalty', 'unstoppable-points-loyalty' ),
			array( $this, 'render_order_meta_box' ),
			$uprf_screen,
			'side',
			'default'
		);
	}

	/**
	 * Render order meta box.
	 *
	 * @param WP_Post|WC_Order $post_or_order Post or order object.
	 */
	public function render_order_meta_box( $post_or_order ) {
		$uprf_order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
		if ( ! $uprf_order ) {
			return;
		}

		$uprf_points_earned   = absint( $uprf_order->get_meta( '_uprf_points_earned' ) );
		$uprf_points_awarded  = $uprf_order->get_meta( '_uprf_points_awarded' );
		$uprf_points_redeemed = absint( $uprf_order->get_meta( '_uprf_points_redeemed' ) );
		$uprf_discount_amount = floatval( $uprf_order->get_meta( '_uprf_discount_amount' ) );
		?>
		<div class="uprf-order-points-info">
			<?php if ( $uprf_points_earned > 0 ) : ?>
				<p>
					<strong><?php esc_html_e( 'Points Earned:', 'unstoppable-points-loyalty' ); ?></strong>
					<?php echo esc_html( number_format_i18n( $uprf_points_earned ) ); ?>
					<?php if ( 'yes' === $uprf_points_awarded ) : ?>
						<span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="<?php esc_attr_e( 'Awarded', 'unstoppable-points-loyalty' ); ?>"></span>
					<?php else : ?>
						<span class="dashicons dashicons-clock" style="color: #f0b849;" title="<?php esc_attr_e( 'Pending', 'unstoppable-points-loyalty' ); ?>"></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<?php if ( $uprf_points_redeemed > 0 ) : ?>
				<p>
					<strong><?php esc_html_e( 'Points Redeemed:', 'unstoppable-points-loyalty' ); ?></strong>
					<?php echo esc_html( number_format_i18n( $uprf_points_redeemed ) ); ?>
					(<?php echo wp_kses_post( wc_price( $uprf_discount_amount ) ); ?>)
				</p>
			<?php endif; ?>

			<?php if ( 0 === $uprf_points_earned && 0 === $uprf_points_redeemed ) : ?>
				<p class="description">
					<?php esc_html_e( 'No points activity for this order.', 'unstoppable-points-loyalty' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
