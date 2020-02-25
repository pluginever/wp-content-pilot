<?php
function content_pilot_update_1_1_2() {
	global $wpdb;
	$wpdb->hide_errors();
	$columns = $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'wp_wpcp_automatic_campaign_batch_%'");
	$columns = $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'wp_wpcp_fetch_contents_batch_%'");
}

content_pilot_update_1_1_2();
