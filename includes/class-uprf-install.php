<?php
/**
 * Installation Class
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UPRF_Install Class.
 */
class UPRF_Install {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		self::create_tables();
		self::create_options();

		// Set version.
		update_option( 'uprf_version', UPRF_VERSION );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Points table.
		$uprf_user_points_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}uprf_user_points (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			points int(11) NOT NULL DEFAULT 0,
			points_balance int(11) NOT NULL DEFAULT 0,
			order_id bigint(20) UNSIGNED DEFAULT NULL,
			date_earned datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY order_id (order_id)
		) $charset_collate;";

		// Points log table.
		$uprf_points_log_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}uprf_points_log (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			points int(11) NOT NULL,
			event_type varchar(50) NOT NULL,
			order_id bigint(20) UNSIGNED DEFAULT NULL,
			user_points_id bigint(20) UNSIGNED DEFAULT NULL,
			note text DEFAULT NULL,
			date_created datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY event_type (event_type),
			KEY order_id (order_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $uprf_user_points_table );
		dbDelta( $uprf_points_log_table );
	}

	/**
	 * Create default options.
	 */
	private static function create_options() {
		$uprf_default_options = array(
			'uprf_earning_ratio'        => 1,
			'uprf_redemption_ratio'     => 0.01,
			'uprf_min_redemption'       => 100,
			'uprf_max_redemption'       => 0,
			'uprf_points_label_singular' => 'Point',
			'uprf_points_label_plural'  => 'Points',
		);

		foreach ( $uprf_default_options as $uprf_option_key => $uprf_option_value ) {
			if ( false === get_option( $uprf_option_key ) ) {
				add_option( $uprf_option_key, $uprf_option_value );
			}
		}
	}
}
