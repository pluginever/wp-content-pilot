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

function wpcp_fetch_content() {
	global $wpdb;
	$links = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}wpcp_links where status=%s  order by id asc limit 2", 'fetched' ) );
	foreach ( $links as $link ) {

		$request = wpcp_setup_request( $link->campaign_type, null, $link->camp_id );
		$request->get( $link->url );
		$response = wpcp_is_valid_response( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$readability = new \andreskrey\Readability\Readability( new \andreskrey\Readability\Configuration() );

		try {
			$readability->parse( $response );
		} catch ( ParseError $e ) {
			wpcp_log( 'critical', $e->getMessage() );

			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}


	}
}

add_action( 'wpcp_per_minute_scheduled_events', 'wpcp_fetch_content' );
