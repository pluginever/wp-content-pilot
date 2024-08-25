<?php
/**
 * ContentPilot Uninstall
 *
 * Uninstalling ContentPilot deletes user roles, pages, tables, and options.
 *
 * @package Uninstaller
 * @version 1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
$wpcp_settings = get_option( 'wpcp_settings_misc', array() );
if ( isset( $wpcp_settings['uninstall_on_delete'] ) && 'on' === $wpcp_settings['uninstall_on_delete'] ) {
	global $wpdb;
	// Remove all database tables.
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpcp_links' );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpcp_logs' );

	/** Cleanup Cron Events */
	wp_clear_scheduled_hook( 'wpcp_per_minute_scheduled_events' );
	wp_clear_scheduled_hook( 'wpcp_daily_scheduled_events' );

	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wpcp_%'" );
}
