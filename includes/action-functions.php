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

	if ( empty( $campaigns ) ) {
		wpcp_log( 'dev', 'No campaign found in scheduled task' );

		return;
	}

	$campaigns = wp_list_pluck( $campaigns, 'ID' );

	$last_campaign = get_option( 'wpcp_last_ran_campaign', '' );

	if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
		unset( $campaigns[ $last_campaign ] );
	}


	if ( ! empty( $campaigns ) ) {
		$automatic_campaign = new WPCP_Automatic_Campaign();

		foreach ( $campaigns as $campaign_id ) {
			$last_run     = wpcp_get_post_meta( $campaign_id, '_last_run', 0 );
			$frequency    = wpcp_get_post_meta( $campaign_id, '_campaign_frequency', 0 );
			$target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
			$posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
			$current_time = current_time( 'timestamp' );
			$diff         = $current_time - $last_run;
			if ( $diff < $frequency ) {
				continue;
			}
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

//	$link = wpcp_get_ready_campaign_links( $campaign_id, $campaign_type );
//
//
//	if ( is_wp_error( $link ) ) {
//		content_pilot()->add_notice( $link->get_error_message(), 'error' );
//		wp_safe_redirect( get_edit_post_link( $campaign_id, 'edit' ) );
//		exit();
//	}

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


function wpcp_render_repeat_row( $key, $args, $post_id ) {

	$defaults = array(
		'key'   => null,
		'value' => null,
	);
	$args     = wp_parse_args( $args, $defaults );
	?>
	<td>
		<input type="hidden" name="download_details[<?php echo absint( $key ); ?>][index]" class="edd_repeatable_index"
		       value="<?php echo absint( $key ); ?>"/>

		<input type="text" name="<?php echo '_meta_fields[' . $key . '][key]'; ?>"
		       id="<?php echo sanitize_key( '_meta_fields[' . $key . '][key]' ); ?>"
		       value="<?php echo esc_attr( $args['key'] ); ?>" class="regular-text ever-field large-text ever-field"
		       autocomplete="false">
	</td>

	<td class="pricing">
		<input type="text" name="<?php echo '_meta_fields[' . $key . '][value]'; ?>"
		       id="<?php echo sanitize_key( '_meta_fields[' . $key . '][key]' ); ?>"
		       value="<?php echo esc_attr( $args['key'] ); ?>" class="regular-text ever-field large-text ever-field"
		       autocomplete="false">
	</td>

	<td>
		<span class="ever-remove-repeatable ever-remove-row edd-remove-row"
		      style="background: url(<?php echo admin_url( '/images/xit.gif' ); ?>) no-repeat;"></span>
	</td>
	<?php
}

add_action( 'wpcp_render_repeat_row', 'wpcp_render_repeat_row', 10, 4 );

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
