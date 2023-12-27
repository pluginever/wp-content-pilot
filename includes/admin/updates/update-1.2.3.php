<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**
 * Content pilot update 1.2.3
 *
 * @since 1.2.3
 * @return void
 */
function content_pilot_update_1_2_3() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links ADD COLUMN `meta` text DEFAULT NULL AFTER `keyword`;" );
}

content_pilot_update_1_2_3();
