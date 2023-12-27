<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/**
 * Content pilot update 1.2.4
 *
 * @since 1.2.4
 * @return void
 */
function content_pilot_update_1_2_4() {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query( "ALTER TABLE $wpdb->wpcp_links CHANGE keyword `for` text DEFAULT NULL;" );
	$wpdb->query( "update {$wpdb->postmeta} set meta_value='playlist' where meta_key='_youtube_search_type' AND meta_value='channel';" );
	$wpdb->query( "update {$wpdb->postmeta} set meta_key='_youtube_playlist_id' where meta_key='_youtube_channel_id';" );
}

content_pilot_update_1_2_4();
