<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled.
 *
 * @package Unstoppable_Points_Rewards_Free
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only clean up if the option is set.
$uprf_cleanup = get_option( 'uprf_cleanup_on_uninstall', false );

if ( $uprf_cleanup ) {
	global $wpdb;

	// Delete options.
	$uprf_options = array(
		'uprf_version',
		'uprf_earning_ratio',
		'uprf_redemption_ratio',
		'uprf_min_redemption',
		'uprf_max_redemption',
		'uprf_points_label_singular',
		'uprf_points_label_plural',
		'uprf_cleanup_on_uninstall',
	);

	foreach ( $uprf_options as $uprf_option ) {
		delete_option( $uprf_option );
	}

	// Drop custom tables.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}uprf_user_points" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}uprf_points_log" );

	// Clear any transients.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_uprf_%'" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_uprf_%'" );
}
