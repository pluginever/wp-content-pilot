<?php
defined( 'ABSPATH' ) || exit();


function wpcp_handle_manual_campaign() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_run_campaign' ) ) {
		wp_die( __( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );

	$campaign_post = get_post( $campaign_id );

	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_die( __( 'Invalid post action', 'wp-content-pilot' ) );
	}

	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	$article_id = content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'user' );
	if ( is_wp_error( $article_id ) ) {
		wpcp_admin_notice( $article_id->get_error_message(), 'error' );
		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
		exit();
	}


	$article_title = '<strong><a href="' . get_the_permalink( $article_id ) . '" target="_blank">' . get_the_title( $article_id ) . '</a></strong>';
	$message       = sprintf( __( 'A post successfully created by %s titled %s', 'wp-content-pilot' ), '<strong>' . get_the_title( $campaign_id ) . '</strong>', $article_title );

	wpcp_admin_notice( $message );
	wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
	exit();
}

add_action( 'admin_post_wpcp_run_campaign', 'wpcp_handle_manual_campaign' );


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

	if ( empty( $campaigns ) ) {
		wpcp_logger()->debug( 'No campaign found in scheduled task' );

		return;
	}

	$campaigns = wp_list_pluck( $campaigns, 'ID' );

	$last_campaign = get_option( 'wpcp_last_ran_campaign', '' );
	if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
		unset( $campaigns[ $last_campaign ] );
	}


	if ( ! empty( $campaigns ) ) {
//		$automatic_campaign = new WPCP_Automatic_Campaign();
		foreach ( $campaigns as $campaign_id ) {
			$last_run     = wpcp_get_post_meta( $campaign_id, '_last_run', 0 );
			$frequency    = wpcp_get_post_meta( $campaign_id, '_run_every', 0 );
			$target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
			$posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
			$current_time = current_time( 'timestamp' );
			$diff         = $current_time - strtotime( $last_run );
			if ( $diff < $frequency ) {
				continue;
			}

			if ( $posted >= $target ) {
				wpcp_logger()->debug( 'Reached target stopping campaign' );
				wpcp_disable_campaign( $campaign_id );
				continue;
			}

			$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', '' );
			if ( ! empty( $campaign_type ) ) {
				content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'cron' );
			}

//			$automatic_campaign->push_to_queue( $campaign_id );

		}
//		$automatic_campaign->save()->dispatch();
	}

}

add_action( 'wpcp_per_minute_scheduled_events', 'wpcp_run_automatic_campaign' );
add_action( 'wp_wpcp_automatic_campaign_cron', 'wpcp_run_automatic_campaign' );
add_action( 'wp_privacy_delete_old_export_files', 'wpcp_run_automatic_campaign' );


function wpcp_delete_all_campaign_posts() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['camp_id'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_delete_posts' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	$camp_id = isset( $_REQUEST['camp_id'] ) && ! empty( $_REQUEST['camp_id'] ) ? $_REQUEST['camp_id'] : false;
	if ( ! $camp_id ) {
		wp_send_json_error( 'Invalid campaign ID.' );
	}

	$args = array(
		'meta_key'       => '_campaign_id',
		'meta_value'     => $camp_id,
		'posts_per_page' => - 1,
		'post_type'      => wpcp_get_post_meta( $camp_id, '_post_type', 'post' ),

	);

	$posts = wpcp_get_posts( $args );

	if ( is_array( $posts ) && count( $posts ) ) {
		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	wp_send_json_success( 'Done' );
}

add_action( 'wp_ajax_wpcp_delete_all_campaign_posts', 'wpcp_delete_all_campaign_posts' );

function wpcp_clear_logs(){
	if ( ! isset( $_REQUEST['nonce'] )|| ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_clear_logs' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	global $wpdb;
	$wpdb->query( "TRUNCATE TABLE $wpdb->wpcp_logs");
	wp_send_json_success('success');
}
add_action( 'wp_ajax_wpcp_clear_logs', 'wpcp_clear_logs' );
