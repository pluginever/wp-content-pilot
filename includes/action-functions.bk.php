<?php
defined( 'ABSPATH' ) || exit();

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


/**
 * Update campaign counter and settings
 *
 * @param $post_id
 * @param $campaign_id
 *
 * @since 1.0.0
 *
 */
function wpcp_update_campaign_counter( $post_id, $campaign_id ) {
	$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );

	update_post_meta( $campaign_id, '_post_count', ( $posted + 1 ) );
	update_post_meta( $campaign_id, '_last_run', current_time( 'timestamp' ) );
	update_option( 'wpcp_last_ran_campaign', $campaign_id );
}

add_action( 'wpcp_after_post_publish', 'wpcp_update_campaign_counter', 10, 2 );

/**
 *
 */
function wpcp_remove_logs() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! empty( $_REQUEST['remove_logs'] ) && ! empty( $_REQUEST['page'] ) && 'wpcp-logs' == $_REQUEST['page'] ) {
		if ( wp_verify_nonce( $_REQUEST['wpcp_nonce'], 'wpcp_remove_logs' ) ) {
			global $wpdb;
			$sql = "truncate {$wpdb->prefix}wpcp_logs;";
			$wpdb->query( $sql );
			$page_url = admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' );
			$page_url = remove_query_arg( array( 'wpcp_nonce', 'remove_logs' ), $page_url );
			wp_safe_redirect( $page_url );
		}
	}

}

add_action( 'admin_init', 'wpcp_remove_logs' );

function wpcp_custom_wpkses_post_tags( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
		);
	}

	return $tags;
}

add_filter( 'wp_kses_allowed_html', 'wpcp_custom_wpkses_post_tags', 10, 2 );
add_action( 'wp_version_check', 'wpcp_per_minute_cron_auto_activate' );

/**
 * check wpcp_per_minute_scheduled_events status
 */
function wpcp_per_minute_cron_auto_activate() {
	$per_minute_cron = wp_get_scheduled_event( 'wpcp_per_minute_scheduled_events' );
	if ( ! $per_minute_cron ) {
		wp_schedule_event( time(), 'once_a_minute', 'wpcp_per_minute_scheduled_events' );
	}
}

/**
 * Reset search page number
 */
function wpcp_handle_campaign_reset_search() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_campaign_reset_search' ) ) {
		wp_die( __( 'No Cheating', 'wp-content-pilot' ) );
	}

	$campaign_id = intval( $_REQUEST['campaign_id'] );

	$campaign_post = get_post( $campaign_id );

	if ( empty( $campaign_post ) || 'wp_content_pilot' !== $campaign_post->post_type ) {
		wp_die( __( 'Invalid post action', 'wp-content-pilot' ) );
	}

	$exclude       = array( 'feed', 'craigslist', 'reddit' );
	$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

	if ( in_array( $campaign_type, $exclude ) ) {
		content_pilot()->add_notice( sprintf( __( 'Reset search not working with %', 'wp-content-pilot' ), $campaign_type ), 'error' );
		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
	}

	$keywords = wpcp_get_post_meta( $campaign_id, '_keywords', '' );
	$keywords = explode( ',', $keywords );

	if ( ! empty( $keywords ) ) {
		foreach ( $keywords as $keyword ) {
			$string = 'page-_wpcp_' . $campaign_id . '-' . $campaign_type . '-' . $keyword . '-page-number';
			$string = sanitize_title( $string );
			update_post_meta( $campaign_id, $string, 0 );
		}
	}

	$message = sprintf( __( ' Reset search page number', 'wp-content-pilot' ) );

	content_pilot()->add_notice( $message, 'success' );

	wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );

}

add_action( 'admin_post_wpcp_campaign_reset_search', 'wpcp_handle_campaign_reset_search' );


function wpcp_save_last_post_id( $post_id, $campaign_id ) {
	wpcp_update_post_meta( $campaign_id, '_last_post', $post_id );
}

add_action( 'wpcp_after_post_publish', 'wpcp_save_last_post_id', 10, 2 );
