<?php
function content_pilot_update_1_0_4() {
	global $wpdb;
	$wpdb->hide_errors();
	$columns = $wpdb->query("DESCRIBE {$wpdb->prefix}wpcp_links");
	if($columns  < 15){
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpcp_links;");
		WPCP_Install::create_tables();
	}
}

content_pilot_update_1_0_4();
