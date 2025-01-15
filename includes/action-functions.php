<?php
defined( 'ABSPATH' ) || exit();

/**
 * Handle the campaign manually.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_handle_manual_campaign() {
	if ( isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'wpcp_run_campaign' ) ) {
		wp_die( esc_html__( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id   = isset( $_REQUEST['campaign_id'] ) ? intval( $_REQUEST['campaign_id'] ) : '';
	$target        = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
	$posted        = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
	$edit_link     = admin_url( sprintf( 'post.php?post=%d&action=edit', $campaign_id ) );
	$campaign_post = get_post( $campaign_id );

	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_die( esc_html__( 'Invalid post action', 'wp-content-pilot' ) );
	}

	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	if ( $posted >= $target ) {
		wpcp_disable_campaign( $campaign_id );
		wpcp_admin_notice( 'Campaign reached its targeted posts, automatically disabled.', 'error' );
		wp_safe_redirect( $edit_link );
		exit();
	}

	$article_id = content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'user' );

	if ( is_wp_error( $article_id ) ) {
		wpcp_admin_notice( $article_id->get_error_message(), 'error' );
		wp_safe_redirect( $edit_link );
		exit();
	}

	$title         = empty( get_the_title( $article_id ) ) ? 'Untitled' : get_the_title( $article_id );
	$article_title = '<strong><a href="' . get_the_permalink( $article_id ) . '" target="_blank">' . $title . '</a></strong>';
	$message       = sprintf( /* translators: 1: The campaign ID, 2: The post title. */ __( 'A post successfully created by %1$s titled %2$s', 'wp-content-pilot' ), '<strong>' . get_the_title( $campaign_id ) . '</strong>', $article_title );
	wpcp_admin_notice( $message );

	wp_safe_redirect( $edit_link );
	exit();
}

add_action( 'admin_post_wpcp_run_campaign', 'wpcp_handle_manual_campaign' );

/**
 * Trigger automatic campaigns.
 * This is the main function that handle all automatic
 * postings.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_run_automatic_campaign() {
	global $wpdb;
	$campaigns = $wpdb->get_results( "select * from {$wpdb->posts} p  left join {$wpdb->postmeta} m on p.id = m.post_id having m.meta_key = '_campaign_status' AND m.meta_value = 'active' AND p.post_status <> 'trash'" );

	if ( empty( $campaigns ) ) {
		return;
	}

	$campaigns = wp_list_pluck( $campaigns, 'ID' );

	$last_campaign = get_option( 'wpcp_last_ran_campaign', '' );
	if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
		unset( $campaigns[ $last_campaign ] );
	}

	if ( ! empty( $campaigns ) ) {
		foreach ( $campaigns as $campaign_id ) {
			$last_run     = wpcp_get_post_meta( $campaign_id, '_last_run', 0 );
			$frequency    = wpcp_get_post_meta( $campaign_id, '_run_every', 0 );
			$target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
			$posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
			$current_time = time();
			$diff         = $current_time - strtotime( $last_run );
			if ( $diff < $frequency ) {
				continue;
			}

			if ( $posted >= $target ) {
				wpcp_logger()->info( __( 'Reached campaign post limit. So campaign stopping publish new post', 'wp-content-pilot' ), $campaign_id );
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

/**
 * Delete all the campaign posts.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_delete_all_campaign_posts() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['camp_id'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'wpcp_delete_posts' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	$camp_id = isset( $_REQUEST['camp_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['camp_id'] ) ) : false;
	if ( ! $camp_id ) {
		wp_send_json_error( 'Invalid campaign ID.' );
	}

	$args = array(
		'meta_key'       => '_campaign_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => $camp_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
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

/**
 * Clear logs data from the database table.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_clear_logs() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_clear_logs' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	global $wpdb;
	$wpdb->query( "TRUNCATE TABLE $wpdb->wpcp_logs" );
	wp_send_json_success( 'success' );
}

add_action( 'wp_ajax_wpcp_clear_logs', 'wpcp_clear_logs' );

/**
 * Remove cached link.
 *
 * @since 2.0.6
 * @return void
 */
function wpcp_remove_cached_link() {
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'ajax_action' ) ) {
		wp_send_json_error( 'Unauthorized!!!' );
	}

	$link = isset( $_REQUEST['link'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['link'] ) ) : '';
	if ( empty( $link ) ) {
		wp_send_json_error( 'Invalid link to process' );
	}

	global $wpdb;
	$link_deleted = $wpdb->delete( $wpdb->wpcp_links, array( 'url' => $link ) );

	if ( ! $link_deleted ) {
		wp_send_json_error( 'Maybe link is not found in the database' );
	}

	wp_send_json_success( 'success' );
	exit();
}

add_action( 'wp_ajax_wpcp_remove_cached_link', 'wpcp_remove_cached_link' );


// TODO: Keyword Suggestion https://www.google.com/complete/search?q=w&cp=1&client=psy-ab&xssi=t&gs_ri=gws-wiz&hl=en-BD&authuser=0&psi=4oO9XIj8ONm89QOY2LCgDA.1555923942084&ei=4oO9XIj8ONm89QOY2LCgDA.
if ( ! function_exists( 'wpcp_pro_get_keyword_suggestion' ) ) :

	/**
	 * Get keyword suggestions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function wpcp_pro_get_keyword_suggestion() {
		$word = isset( $_REQUEST['input'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['input'] ) ) : '';

		$curl = new Curl\Curl();
		$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
		$curl->setOpt( CURLOPT_TIMEOUT, 30 );
		$curl->setOpt( CURLOPT_RETURNTRANSFER, true );
		$curl->setOpt( CURLOPT_REFERER, 'http://www.bing.com/' );
		$curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );
		$curl->get(
			'http://suggestqueries.google.com/complete/search',
			array(
				'output'         => 'toolbar',
				'hl=en&q=sultan' => 'en',
				'q'              => $word,
				'client'         => 'firefox',
			)
		);

		if ( is_wp_error( $curl->isError() ) ) {
			wp_send_json_success( array() );
		}
		$response   = $curl->getResponse();
		$suggestion = array();
		$list       = json_decode( $response );
		if ( is_array( $list ) && isset( $list[1] ) ) {
			$list = $list[1];
		}
		if ( is_array( $list ) && count( $list ) ) {
			foreach ( $list as $item ) {
				$str = preg_replace( '/[^a-z0-9.]+/i', '', $item );
				// TODO: need to fix the condition if(!empty($str)){.
				$suggestion[] = $item;
				// }.
			}
		}

		$suggestion = array_unique( $suggestion );
		wp_send_json_success( $suggestion );
	}
endif;

add_action( 'wp_ajax_wpcp_pro_get_keyword_suggestion', 'wpcp_pro_get_keyword_suggestion' );

/**
 * Send notification mail after post insert.
 *
 * @param int    $post_id The post ID.
 * @param int    $campaign_id The campaign ID.
 * @param Object $article The post object.
 *
 * @since 1.0.9
 * @return void
 */
function wpcp_post_publish_mail_notification( $post_id, $campaign_id, $article ) {
	$send_mail = wpcp_get_settings( 'post_publish_mail', 'wpcp_settings_misc', '' );
	if ( 'on' !== $send_mail ) {
		return;
	}
	$author_id = get_post_field( 'post_author', $post_id );
	$to        = get_the_author_meta( 'user_email', $author_id );
	$title     = $article['title'];
	// When excerpt is not available.
	if ( empty( $article['excerpt'] ) ) {
		$summary = wp_trim_words( $article['content'], 55 );
		$summary = wp_strip_all_tags( $summary );
		$excerpt = strip_shortcodes( $summary );
	} else {
		$excerpt = $article['excerpt'];
	}

	$post_link = get_the_permalink( $post_id );
	$subject   = __( 'Post Publish', 'wp-content-pilot' );
	$body      = sprintf(
		"<h4>Post Title: %s</h4><h5>Post Excerpt</h5><p>%s</p><a href='%s'>View Post</a>",
		esc_html( $title ),
		$excerpt,
		esc_url( $post_link )
	);
	$headers   = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $to, $subject, $body, $headers );
}

add_action( 'wpcp_after_post_publish', 'wpcp_post_publish_mail_notification', 10, 3 );

/**
 * Delete old logs longer than 2 days.
 *
 * @since 1.2.0
 * @return void
 */
function wpcp_wp_scheduled_delete() {
	global $wpdb;
	$date = date( 'Y-m-d H:i:s', strtotime( '2 days ago' ) );
	$wpdb->query( $wpdb->prepare( "DELETE  FROM $wpdb->wpcp_logs WHERE created_at<=%s", $date ) );
}

add_action( 'wp_scheduled_delete', 'wpcp_wp_scheduled_delete' );

/**
 * Trigger reset campaigns.
 *
 * @since 1.2.0
 * @return void
 */
function wpcp_campaign_reset_search_campaign() {
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

/**
 * Run the campaign manually.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_ajax_run_manual_campaign() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'ajax_action' ) ) {
		wp_send_json( [
			'message' => __( 'Cheating?', 'wp-content-pilot' ),
			'level'   => 'ERROR',
			'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
		] );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );
	$instance    = intval( $_REQUEST['instance'] );
	if ( ! defined( 'WPCP_CAMPAIGN_INSTANCE' ) ) {
		define( 'WPCP_CAMPAIGN_INSTANCE', $instance );
	}

	$campaign_post = get_post( $campaign_id );
	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_send_json( [
			'message' => __( 'Invalid post action', 'wp-content-pilot' ),
			'level'   => 'ERROR',
			'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
		] );
	}

	global $current_user;
	wpcp_logger()->info( sprintf( __( 'Campaign <strong>%s</strong> manually initiated by <strong>%s</strong>', 'wp-content-pilot' ), get_the_title( $campaign_id ), $current_user->display_name ) );

	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	$article_id = content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'user' );
	if ( is_wp_error( $article_id ) ) {
		wp_send_json( [
			'message' => $article_id->get_error_message(),
			'level'   => 'ERROR',
			'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
		] );
	}
	$title         = empty( get_the_title( $article_id ) ) ? 'Untitled' : get_the_title( $article_id );
	$article_title = '<a href="' . get_the_permalink( $article_id ) . '" target="_blank">' . $title . '</a>';
	$message       = sprintf( __( 'A post successfully created by campaign %s titled %s', 'wp-content-pilot' ), '<strong>' . get_the_title( $campaign_id ) . '</strong>', $article_title );
	wp_send_json( [
		'message' => $message,
		'link'    => $article_title,
		'level'   => 'INFO',
		'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
	] );
}

add_action( 'wp_ajax_wpcp_run_manual_campaign', 'wpcp_ajax_run_manual_campaign' );

/**
 * Get campaign instance log.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_get_campaign_instance_log() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'ajax_action' ) ) {
		wp_send_json( [
			[
				'message' => __( 'Cheating?', 'wp-content-pilot' ),
				'level'   => 'ERROR',
				'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
			]
		] );
	}

	$campaign_id = absint( $_REQUEST['campaign_id'] );
	$instance    = absint( $_REQUEST['instance'] );
	$offset      = absint( $_REQUEST['offset'] );
	if ( empty( $campaign_id ) || empty( $instance ) ) {
		wp_send_json( [
			[
				'message' => __( 'Something wrong, please try again.', 'wp-content-pilot' ),
				'level'   => 'ERROR',
				'time'    => gmdate( 'H:i:s', current_time( 'timestamp' ) )
			]
		] );
	}

	global $wpdb;
	$data = $wpdb->get_results( "select `level`, message, DATE_FORMAT(created_at, '%H:%i:%s') as time from {$wpdb->prefix}wpcp_logs where instance_id={$instance} order by id ASC limit 9999 offset {$offset}" );
	wp_send_json( $data );
}

add_action( 'wp_ajax_wpcp_get_campaign_instance_log', 'wpcp_get_campaign_instance_log' );

/**
 * Removes logs and links from the database when a campaign is going to trash.
 *
 * @param int $post_id The post id.
 *
 * @since 1.0.0
 * @return void
 */
function remove_campaign_data_when_trash( $post_id ) {
	global $wpdb;
	if ( 'wp_content_pilot' == get_post_type( $post_id ) ) {
		$links_query = "DELETE FROM {$wpdb->wpcp_links} where camp_id=$post_id";
		$logs_query  = "DELETE FROM {$wpdb->wpcp_logs} where camp_id=$post_id";
		$wpdb->query( $links_query );
		$wpdb->query( $logs_query );
	}
}

add_action( 'wp_trash_post', 'remove_campaign_data_when_trash', 10, 1 );
