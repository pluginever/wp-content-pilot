<?php
function content_pilot_update_1_2_0() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN post_id;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN camp_type;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN image;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN content;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN raw_content;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN score;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN gmt_date;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN data;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN created_at;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links DROP COLUMN updated_at;");


	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs DROP COLUMN updated_at;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs DROP COLUMN keyword;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs DROP COLUMN log_level;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs DROP COLUMN message;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs ADD COLUMN level varchar(20) NOT NULL DEFAULT 'info' AFTER camp_id;");
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_logs ADD COLUMN message text DEFAULT '' AFTER camp_id;");
}

content_pilot_update_1_2_0();
