<?php
/**
 * Plugin Name:       Unstoppable Points & Loyalty for WooCommerce
 * Plugin URI:        https://unstoppablesolutions.app/PluginDetail?slug=unstoppable-points-loyalty
 * Description:       A simple yet powerful points and loyalty system for WooCommerce. Reward customers for purchases and let them redeem points for discounts.
 * Version:           1.1.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Unstoppable WP
 * Author URI:        https://unstoppablesolutions.app
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       unstoppable-points-loyalty
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * WC requires at least: 8.0
 * WC tested up to:   9.4
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ============================================================================
 * UNSTOPPABLE OPTIONS STUDIO - SHARED SHELL SYSTEM
 * ============================================================================
 *
 * This code creates the "Options Studio" parent menu that all Unstoppable WP
 * plugins share. Each plugin includes this same code block - the first plugin
 * to load creates the shell, others just add their submenus.
 * ============================================================================
 */

if ( ! function_exists( 'unstoppable_options_studio_init' ) ) {
	/**
	 * Initialize the Options Studio shell menu.
	 * This function is shared across all Unstoppable WP plugins.
	 * Only the first plugin to load will create the parent menu.
	 */
	function unstoppable_options_studio_init() {
		add_action( 'admin_menu', 'unstoppable_options_studio_create_menu', 9 );
		add_action( 'admin_enqueue_scripts', 'unstoppable_options_studio_register_admin_css' );
	}

	/**
	 * Create the Options Studio parent menu.
	 */
	function unstoppable_options_studio_create_menu() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Check if parent menu already exists (another Unstoppable plugin may have created it).
		global $menu;
		foreach ( $menu as $item ) {
			if ( isset( $item[2] ) && 'unstoppable-studio' === $item[2] ) {
				return; // Menu already exists.
			}
		}

		// Create the parent menu.
		add_menu_page(
			__( 'Options Studio', 'unstoppable-points-loyalty' ),
			__( 'Options Studio', 'unstoppable-points-loyalty' ),
			'manage_woocommerce',
			'unstoppable-studio',
			'unstoppable_options_studio_dashboard',
			'dashicons-screenoptions',
			56 // Position after WooCommerce.
		);

		// Add dashboard as first submenu (replaces the duplicate parent link).
		add_submenu_page(
			'unstoppable-studio',
			__( 'Dashboard', 'unstoppable-points-loyalty' ),
			__( 'Dashboard', 'unstoppable-points-loyalty' ),
			'manage_woocommerce',
			'unstoppable-studio',
			'unstoppable_options_studio_dashboard'
		);
	}

	/**
	 * Options Studio Dashboard page.
	 * Shows overview of all installed Unstoppable WP plugins.
	 */
	function unstoppable_options_studio_dashboard() {
		$uprf_plugins = apply_filters( 'unstoppable_studio_registered_plugins', array() );
		?>
		<div class="wrap unstoppable-studio-dashboard">
			<h1><?php esc_html_e( 'Options Studio', 'unstoppable-points-loyalty' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Your suite of WooCommerce enhancement tools by Unstoppable WP.', 'unstoppable-points-loyalty' ); ?></p>

			<div class="unstoppable-studio-plugins" style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
				<?php if ( empty( $uprf_plugins ) ) : ?>
					<p><?php esc_html_e( 'No plugins registered yet.', 'unstoppable-points-loyalty' ); ?></p>
				<?php else : ?>
					<?php foreach ( $uprf_plugins as $uprf_plugin ) : ?>
						<div class="unstoppable-plugin-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; width: 300px;">
							<h3 style="margin-top: 0;"><?php echo esc_html( $uprf_plugin['name'] ); ?></h3>
							<p style="color: #666;"><?php echo esc_html( $uprf_plugin['description'] ); ?></p>
							<?php if ( ! empty( $uprf_plugin['menu_slug'] ) ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $uprf_plugin['menu_slug'] ) ); ?>" class="button button-primary">
									<?php esc_html_e( 'Open', 'unstoppable-points-loyalty' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<div class="unstoppable-studio-footer" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccd0d4;">
				<p>
					<a href="https://unstoppablesolutions.app" target="_blank"><?php esc_html_e( 'Visit Unstoppable Solutions', 'unstoppable-points-loyalty' ); ?></a> |
					<a href="https://unstoppablesolutions.app/support" target="_blank"><?php esc_html_e( 'Get Support', 'unstoppable-points-loyalty' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Register admin CSS using wp_add_inline_style (WordPress.org compliant).
	 */
	function unstoppable_options_studio_register_admin_css() {
		// Register an empty handle as an anchor for inline styles.
		wp_register_style( 'unstoppable-studio-admin', false, array(), '1.0.0' );
		wp_enqueue_style( 'unstoppable-studio-admin' );

		$uprf_css = '
			#adminmenu .toplevel_page_unstoppable-studio .wp-menu-image:before {
				color: #f0b849;
			}
			#adminmenu .toplevel_page_unstoppable-studio:hover .wp-menu-image:before,
			#adminmenu .toplevel_page_unstoppable-studio.current .wp-menu-image:before {
				color: #fff;
			}
		';

		wp_add_inline_style( 'unstoppable-studio-admin', $uprf_css );
	}
}

// Initialize the Options Studio shell.
unstoppable_options_studio_init();

/*
 * ============================================================================
 * END SHARED SHELL SYSTEM
 * ============================================================================
 */

// Plugin constants.
define( 'UPRF_VERSION', '1.1.4' );
define( 'UPRF_PLUGIN_FILE', __FILE__ );
define( 'UPRF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPRF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UPRF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class.
 */
final class Unstoppable_Points_Rewards_Free {

	/**
	 * Single instance of the class.
	 *
	 * @var Unstoppable_Points_Rewards_Free
	 */
	private static $instance = null;

	/**
	 * Points manager instance.
	 *
	 * @var UPRF_Manager
	 */
	public $manager;

	/**
	 * Get the single instance.
	 *
	 * @return Unstoppable_Points_Rewards_Free
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		// Core includes.
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-install.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-manager.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-order.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-cart-checkout.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-ajax.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-shortcodes.php';
		require_once UPRF_PLUGIN_DIR . 'includes/class-uprf-emails.php';

		// Admin includes.
		if ( is_admin() ) {
			require_once UPRF_PLUGIN_DIR . 'admin/class-uprf-admin.php';
		}

		// Frontend includes.
		if ( ! is_admin() || wp_doing_ajax() ) {
			require_once UPRF_PLUGIN_DIR . 'public/class-uprf-frontend.php';
			require_once UPRF_PLUGIN_DIR . 'public/class-uprf-my-account.php';
		}
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		register_activation_hook( UPRF_PLUGIN_FILE, array( 'UPRF_Install', 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 20 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_wc_compatibility' ) );
		add_action( 'init', array( $this, 'init' ), 0 );

		// Register with Options Studio.
		add_filter( 'unstoppable_studio_registered_plugins', array( $this, 'register_with_studio' ) );
	}

	/**
	 * Register plugin with Options Studio dashboard.
	 *
	 * @param array $plugins Registered plugins.
	 * @return array
	 */
	public function register_with_studio( $plugins ) {
		$plugins['uprf-points-rewards'] = array(
			'name'        => __( 'Points & Loyalty', 'unstoppable-points-loyalty' ),
			'description' => __( 'A simple yet powerful points and loyalty system for WooCommerce. Reward customers for purchases and let them redeem points for discounts.', 'unstoppable-points-loyalty' ),
			'menu_slug'   => 'uprf-points-rewards',
			'version'     => UPRF_VERSION,
		);
		return $plugins;
	}

	/**
	 * On plugins loaded.
	 */
	public function on_plugins_loaded() {
		if ( ! $this->check_woocommerce() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		$this->init_classes();
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	private function check_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * WooCommerce missing notice.
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo esc_html__(
					'Unstoppable Points & Loyalty requires WooCommerce to be installed and active.',
					'unstoppable-points-loyalty'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Declare WooCommerce compatibility.
	 */
	public function declare_wc_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				UPRF_PLUGIN_FILE,
				true
			);
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				UPRF_PLUGIN_FILE,
				true
			);
		}
	}

	/**
	 * Init plugin.
	 */
	public function init() {
		// Nothing needed here for now - WP handles text domain loading automatically.
	}

	/**
	 * Initialize classes.
	 */
	private function init_classes() {
		$this->manager = new UPRF_Manager();

		new UPRF_Order();
		new UPRF_Cart_Checkout();
		new UPRF_Ajax();
		new UPRF_Shortcodes();
		new UPRF_Emails();

		if ( is_admin() ) {
			new UPRF_Admin();
		}

		if ( ! is_admin() || wp_doing_ajax() ) {
			new UPRF_Frontend();
			new UPRF_My_Account();
		}
	}

	/**
	 * Get points label (singular or plural).
	 *
	 * @param int $count Number of points.
	 * @return string
	 */
	public function get_points_label( $count = 1 ) {
		$uprf_singular = get_option( 'uprf_points_label_singular', __( 'Point', 'unstoppable-points-loyalty' ) );
		$uprf_plural   = get_option( 'uprf_points_label_plural', __( 'Points', 'unstoppable-points-loyalty' ) );

		return absint( $count ) === 1 ? $uprf_singular : $uprf_plural;
	}

	/**
	 * Get earning ratio.
	 *
	 * @return float Points per currency unit.
	 */
	public function get_earning_ratio() {
		return floatval( get_option( 'uprf_earning_ratio', 1 ) );
	}

	/**
	 * Get redemption ratio.
	 *
	 * @return float Currency value per point.
	 */
	public function get_redemption_ratio() {
		return floatval( get_option( 'uprf_redemption_ratio', 0.01 ) );
	}
}

/**
 * Main instance.
 *
 * @return Unstoppable_Points_Rewards_Free
 */
function UPRF() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Unstoppable_Points_Rewards_Free::instance();
}

// Initialize.
UPRF();
