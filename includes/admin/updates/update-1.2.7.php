<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**
 * Content pilot update 1.2.7
 *
 * @since 1.2.7
 * @return void
 */
function content_pilot_update_1_2_7() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs ADD COLUMN `instance_id` varchar(30) DEFAULT NULL AFTER `message`;" );
}

content_pilot_update_1_2_7();
