<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**
 * Content pilot update 1.0.4
 *
 * @since 1.0.4
 * @return void
 */
function content_pilot_update_1_0_4() {
	global $wpdb;
	$wpdb->hide_errors();
	$columns = $wpdb->query( "DESCRIBE {$wpdb->prefix}wpcp_links" );
	if ( $columns < 15 ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcp_links;" );
		WPCP_Install::create_tables();
	}
}

content_pilot_update_1_0_4();
