<?php
function content_pilot_update_1_2_3() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links ADD COLUMN `meta` text DEFAULT NULL AFTER `keyword`;");
}

content_pilot_update_1_2_3();
