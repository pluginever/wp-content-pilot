<?php
defined( 'ABSPATH' ) || exit();


function wpcp_handle_manual_campaign() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_run_campaign' ) ) {
		wp_die( __( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );


	$target = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
	$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );


	$campaign_post = get_post( $campaign_id );

	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_die( __( 'Invalid post action', 'wp-content-pilot' ) );
	}

	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	if ( $posted >= $target ) {
		wpcp_disable_campaign( $campaign_id );
		wpcp_admin_notice( 'Reached target stopping campaign' );
		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
		exit();
	}

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
				wpcp_logger()->debug( 'Reached target stopping campaign', $campaign_id );
				wpcp_disable_campaign( $campaign_id );
				continue;
			}

			$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', '' );
			if ( ! empty( $campaign_type ) ) {
				content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'cron' );
			}
		}
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

function wpcp_clear_logs() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_clear_logs' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	global $wpdb;
	$wpdb->query( "TRUNCATE TABLE $wpdb->wpcp_logs" );
	wp_send_json_success( 'success' );
}

add_action( 'wp_ajax_wpcp_clear_logs', 'wpcp_clear_logs' );


// TODO: Keyword Suggestion https://www.google.com/complete/search?q=w&cp=1&client=psy-ab&xssi=t&gs_ri=gws-wiz&hl=en-BD&authuser=0&psi=4oO9XIj8ONm89QOY2LCgDA.1555923942084&ei=4oO9XIj8ONm89QOY2LCgDA
function wpcp_pro_get_keyword_suggestion() {
	$word = $_REQUEST['input'];

	$curl = new Curl\Curl();
	$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
	$curl->setOpt( CURLOPT_TIMEOUT, 30 );
	$curl->setOpt( CURLOPT_RETURNTRANSFER, true );
	$curl->setOpt( CURLOPT_REFERER, 'http://www.bing.com/' );
	$curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );
	$curl->get( 'http://suggestqueries.google.com/complete/search', array(
		'output'         => 'toolbar',
		'hl=en&q=sultan' => 'en',
		'q'              => $word,
		'client'         => 'firefox',
	) );

	if ( is_wp_error( $curl->isError() ) ) {
		wp_send_json_success( [] );
	}
	$response   = $curl->getResponse();
	$suggestion = [];
	$list       = json_decode( $response );
	if ( is_array( $list ) && isset( $list[1] ) ) {
		$list = $list[1];
	}
	if ( is_array( $list ) && count( $list ) ) {
		foreach ( $list as $item ) {
			$str = preg_replace( '/[^a-z0-9.]+/i', '', $item );
			// if(!empty($str)){
			$suggestion[] = $item;
			// }
		}
	}

	$suggestion = array_unique( $suggestion );
	wp_send_json_success( $suggestion );
}

add_action( 'wp_ajax_wpcp_pro_get_keyword_suggestion', 'wpcp_pro_get_keyword_suggestion' );

/**
 * Send notification mail after post insert
 *
 * @param $post_id
 * @param $campaign_id
 * @param $article
 * @param $keyword
 *
 * @since 1.0.9
 *
 */
function wpcp_post_publish_mail_notification( $post_id, $campaign_id, $article ) {
	$send_mail = wpcp_get_settings( 'post_publish_mail', 'wpcp_settings_misc', '' );
	if ( $send_mail != 'on' ) {
		return;
	}
	$author_id = get_post_field( 'post_author', $post_id );
	$to        = get_the_author_meta( 'user_email', $author_id );
	$title     = $article['title'];
	//when excerpt is not available
	if ( empty( $article['excerpt'] ) ) {
		$summary = wp_trim_words( $article['content'], 55 );
		$summary = strip_tags( $summary );
		$excerpt = strip_shortcodes( $summary );
	} else {
		$excerpt = $article['excerpt'];
	}

	$post_link = get_the_permalink( $post_id );
	$subject   = __( 'Post Publish', 'wp-content-pilot' );
	$body      = sprintf( __( "<h4>Post Title: %s</h4>
                    <h5>Post Excerpt</h5>
                    <p>%s</p>
                    <a href='%s'>View Post</a>", 'wp-content-pilot' ), esc_html( $title ), $excerpt, esc_url( $post_link )
	);
	$headers   = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $to, $subject, $body, $headers );
}

add_action( 'wpcp_after_post_publish', 'wpcp_post_publish_mail_notification', 10, 3 );

/**
 * Delete old logs longer than 2 days
 * since 1.2.0
 */
function wpcp_wp_scheduled_delete() {
	global $wpdb;
	$date = date( 'Y-m-d H:i:s', strtotime( '2 days ago' ) );
	$wpdb->query( $wpdb->prepare( "DELETE  FROM $wpdb->wpcp_logs WHERE created_at<=%s", $date ) );
}

add_action( 'wp_scheduled_delete', 'wpcp_wp_scheduled_delete' );

/**
 * Trigger reset campaigns
 *
 *
 * @since 1.2.0
 */

function wpcp_campaign_reset_search_campaign () {
	global $wpdb;
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_campaign_reset_search' ) ) {
		wp_die( __( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );

	$delete_query = "DELETE FROM {$wpdb->postmeta} where post_id=$campaign_id AND meta_key NOT IN ('_post_count','_campaign_type','_post_status','_campaign_target','_last_run','_last_post')";

	$clear_query = "DELETE FROM {$wpdb->wpcp_links} where camp_id=$campaign_id";

	$wpdb->query( $delete_query );
	$wpdb->query( $clear_query );
	wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
}
add_action( 'admin_post_wpcp_campaign_reset_search', 'wpcp_campaign_reset_search_campaign' );