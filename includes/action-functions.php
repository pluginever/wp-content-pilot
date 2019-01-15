<?php
function wpcp_run_automatic_campaign() {
	global $wpdb;
	$sql   = "select * from {$wpdb->posts} p  left join {$wpdb->postmeta} m on p.id = m.post_id having m.meta_key = '_active' AND m.meta_value = 'on'";
	$posts = $wpdb->get_results( $sql );

	if ( ! $posts ) {
		wpcp_log( 'dev', 'No campaign found in scheduled task' );

		return;
	}

	error_log( print_r( $posts, true ) );
}

add_action( 'wpcp_per_minute_scheduled_events', 'wpcp_run_automatic_campaign' );



