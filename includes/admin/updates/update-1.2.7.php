<?php
function content_pilot_update_1_2_6() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs ADD COLUMN `instance_id` varchar(30) DEFAULT NULL AFTER `message`;");
}

content_pilot_update_1_2_6();
