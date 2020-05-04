<?php
function content_pilot_update_1_2_4() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links CHANGE keyword source text DEFAULT NULL;");
}

content_pilot_update_1_2_4();
