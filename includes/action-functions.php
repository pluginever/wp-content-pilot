<?php
/**
 * Trigger automatic campaigns
 * This is the main function that handle all automatic
 * postings
 *
 * @since 1.0.0
 */
function wpcp_run_automatic_campaign() {
	global $wpdb;
	$sql       = "select * from {$wpdb->posts} p  left join {$wpdb->postmeta} m on p.id = m.post_id having m.meta_key = '_campaign_status' AND m.meta_value = 'active'";
	$campaigns = $wpdb->get_results( $sql );

	if ( ! $campaigns ) {
		wpcp_log( 'dev', 'No campaign found in scheduled task' );

		return;
	}

	$campaigns = wp_list_pluck( $campaigns, 'ID' );

	$last_campaign = get_option( 'wpcp_last_ran_campaign', '' );

	if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
		unset( $campaigns[ $last_campaign ] );
	}


	if(!empty($campaigns)){
		$automatic_campaign = new WPCP_Automatic_Campaign();

		foreach ( $campaigns as $campaign_id ) {
			$last_run     = wpcp_get_post_meta( $campaign_id, '_last_run', '' );
			$frequency    = wpcp_get_post_meta( $campaign_id, '_campaign_frequency', 0 );
			$target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
			$posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
			$current_time = current_time( 'timestamp' );
			$diff         = $current_time - $last_run;
//			if ( $diff < $frequency ) {
//				continue;
//			}

			if ( $posted >= $target ) {
				wpcp_disable_campaign( $campaign_id );
				continue;
			}

			$automatic_campaign->push_to_queue( $campaign_id );

		}
		$automatic_campaign->save()->dispatch();
	}

}

add_action( 'wpcp_per_minute_scheduled_events', 'wpcp_run_automatic_campaign' );

function wpcp_prepare_campaign_article() {
	global $wpdb;
	$links = $wpdb->get_col( $wpdb->prepare( "select id from {$wpdb->prefix}wpcp_links where status=%s", 'fetched' ) );
	if ( ! empty( $links ) ) {
		$background_process = new WPCP_Fetch_Contents();
		foreach ( $links as $key => $link_id ) {
			$background_process->push_to_queue( $link_id );
		}
		$background_process->save()->dispatch();
	}
}

add_action( 'wpcp_per_minute_scheduled_events', 'wpcp_prepare_campaign_article' );


function wpcp_handle_campaign_test_run() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_campaign_test_run' ) ) {
		wp_die( __( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );

	$campaign_post = get_post( $campaign_id );

	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_die( __( 'Invalid post action', 'wp-content-pilot' ) );
	}

	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	$link = wpcp_get_ready_campaign_links( $campaign_id, $campaign_type );

	if ( is_wp_error( $link ) ) {
		content_pilot()->add_notice( $link->get_error_message(), 'error' );
		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
		exit();
	}

	$campaign = wpcp_run_campaign( $campaign_id );

	if ( is_wp_error( $campaign ) ) {
		content_pilot()->add_notice( $campaign->get_error_message(), 'error' );
		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
		exit();
	}

	$article_title = '<strong><a href="' . get_the_permalink( $campaign ) . '" target="_blank">' . get_the_title( $campaign ) . '</a></strong>';
	$message       = sprintf( __( 'A post successfully created by %s titled %s', 'wp-content-pilot' ), '<strong>' . get_the_title( $campaign_id ) . '</strong>', $article_title );

	content_pilot()->add_notice( $message, 'success' );

	wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );

}

add_action( 'admin_post_wpcp_campaign_test_run', 'wpcp_handle_campaign_test_run' );


/**
 * Update campaign counter and settings
 *
 * @since 1.0.0
 *
 * @param $post_id
 * @param $campaign_id
 */
function wpcp_update_campaign_counter( $post_id, $campaign_id ) {
	$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );

	update_post_meta( $campaign_id, '_post_count', ( $posted + 1 ) );
	update_post_meta( $campaign_id, '_last_run', current_time( 'timestamp' ) );
	update_option( 'wpcp_last_ran_campaign', $campaign_id );
}

add_action( 'wpcp_after_post_publish', 'wpcp_update_campaign_counter', 10, 2 );
