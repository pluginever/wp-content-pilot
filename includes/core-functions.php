<?php
/**
 * Sanitizes a string key for WPCP Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 * since 1.0.0
 *
 * @param $key
 *
 * @return string
 */
function wpcp_sanitize_key( $key ) {

	return preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
}

/**
 * get all the modules
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_modules() {
	$modules = [];
	foreach ( content_pilot()->modules->get_modules() as $module_name => $module ) {
		$modules[ $module_name ] = $module['title'];
	}

	return $modules;
}


/**
 * Logger for the plugin
 *
 * @since    1.0.0
 *
 * @param  $message
 *
 * @param  $log_level
 * dev - when the development
 * log - normal log
 * critical - error/failed
 *
 *
 * @return  string
 */
function wpcp_log( $message, $log_level = "log" ) {
	$log_level = strtolower( $log_level );

	if ( is_array( $message ) || is_object( $message ) ) {
		$message = print_r( $message, true );
	}

	if ( ! defined( 'WPCP_LOG_FILE' ) ) {
		define( 'WPCP_LOG_FILE', WP_CONTENT_DIR . '/debug.log' );
	}

	if ( in_array( $log_level, array( 'log', 'critical' ) ) ) {
		$camp_id = isset( content_pilot()->campaign_id ) ? content_pilot()->campaign_id : null;
		$keyword = isset( content_pilot()->keyword ) ? content_pilot()->keyword : null;
		$message = strip_tags( $message );
		$level   = $log_level;

		global $wpdb;
		$wpdb->insert(
			"{$wpdb->prefix}wpcp_logs",
			array(
				'camp_id'   => $camp_id,
				'keyword'   => $keyword,
				'log_level' => $level,
				'message'   => addslashes( $message ),
			)
		);

	}

	if ( WP_DEBUG == true ) {
		if ( ! file_exists( WPCP_LOG_FILE ) ) {
			@touch( WPCP_LOG_FILE );
		}

		return error_log( date( "Y-m-d\tH:i:s" ) . "\t\t" . ucwords( $log_level ) . "\t\t" . strip_tags( $message ) . "\n", 3, WPCP_LOG_FILE );
	}

}


/**
 * Returns wpcp meta values
 *
 * @param      $post_id
 * @param      $key
 * @param null $default
 *
 * @return null|string
 */
function wpcp_get_post_meta( $post_id, $key, $default = null ) {
	$meta_value = get_post_meta( $post_id, $key, true );

	if ( $meta_value ) {
		$value = get_post_meta( $post_id, $key, true );
	} else {
		$value = $default;
	}

	return is_string( $value ) ? trim( $value ) : $value;
}

/**
 * Save post meta
 *
 * @since 1.0.0
 *
 * @param $post_id
 * @param $key
 * @param $value
 */
function wpcp_update_post_meta( $post_id, $key, $value ) {
	update_post_meta( $post_id, $key, $value );
}

/**
 * Save option
 *
 * @since 1.0.0
 *
 * @param $key
 * @param $value
 */
function wpcp_update_option( $key, $value ) {
	update_option( $key, $value );
}


/**
 * Mark campaign as disabled
 *
 * @since 1.0.0
 *
 * @param $camp_id
 */
function wpcp_disable_campaign( $camp_id ) {
	wpcp_update_post_meta( $camp_id, 'active', 0 );
}

/**
 * Campaign schedule options
 *
 * @since 1.0.0
 *
 * @return array
 */
function wpcp_get_campaign_schedule_options() {
	$options = [];
	for ( $i = 1; $i <= 24; $i ++ ) {
		$time             = $i * HOUR_IN_SECONDS;
		$options[ $time ] = sprintf( _n( '%s Hour', '%s Hours', $i, 'wp-content-pilot' ), $i );;
	}


	return apply_filters( 'wpcp_campaign_schedule_options', $options );
}

/**
 * get keyword
 *
 * @since 1.0.0
 *
 * @param $campaign_id
 *
 * @return string
 */
function wpcp_get_keyword( $campaign_id ) {
	$keyword = wpcp_get_post_meta( $campaign_id, '_keywords', '' );

	return apply_filters( 'wpcp_keyword', $keyword, $campaign_id );
}

/**
 * Checks if campaign is valid or not
 *
 * @since 1.0.0
 *
 * @param $campaign_id
 *
 * @return bool|\WP_Error
 *
 */
function wpcp_campaign_can_run( $campaign_id ) {
	if ( 'publish' !== get_post_status( $campaign_id ) ) {
		return new \WP_Error( 'invalid-campaign-id', __( 'Campaign is not exist or not publish.', 'wp-content-pilot' ) );
	}

	if ( '1' !== get_post_meta( $campaign_id, '_active', true ) ) {
		return new \WP_Error( 'invalid-campaign-status', __( 'Campaign is not active this wont run.', 'wp-content-pilot' ) );
	}

	$campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );
	if ( empty( $campaign_type ) ) {
		return new \WP_Error( 'invalid-campaign-type', __( 'Campaign type is not set. Campaign won\'t run', 'wp-content-pilot' ) );
	}

	$keyword = wpcp_get_keyword( $campaign_id );
	if ( empty( trim( $keyword ) ) ) {
		return new \WP_Error( 'campaign-keyword-invalid', __( "Campaign keyword is not set. Campaign won't run", 'wp-content-pilot' ) );
	}

	return true;
}

/**
 *
 * wpcp run campaign
 *
 * @since 1.0.0
 *
 * @param $campaign_id
 *
 * @return bool|\WP_Error
 */
function wpcp_run_campaign( $campaign_id ) {
	$can_run = wpcp_campaign_can_run( $campaign_id );
	if ( is_wp_error( $can_run ) ) {
		wpcp_log( $can_run->get_error_message(), 'critical' );

		return $can_run;
	}
	$campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );
	$keyword       = wpcp_get_keyword( $campaign_id );

	$module = content_pilot()->modules->get_module( $campaign_type );

	if ( ! $module ) {
		$msg = __( 'Could not find the module for the campaign type', 'wp-content-pilot' );
		wpcp_log( $msg, 'critical' );

		return new \WP_Error( 'invalid-campaign-type', $msg );
	}

	//get the module callback
	$module_class = $module['callback'];

	// $instance \WPCP_Campaign
	$instance = new $module_class( $campaign_id, $keyword );

	//set the module
	$is_error = $instance->setup();

	//check error
	if ( is_wp_error( $is_error ) ) {
		wpcp_disable_campaign( $this->campaign_id );

		return $is_error;
	}

	$instance->set_campaign_id( $campaign_id );

	$instance->set_keyword( $keyword );

	$instance->set_campaign_type( $module );

	try {
		$article = $instance->run();

		if ( is_wp_error( $article ) ) {
			return $article;
		}
		wpcp_log( sprintf( __( "Post Insertion was success Post ID: %s", 'wp-content-pilot' ), $article ), 'log' );

	} catch ( Exception $exception ) {
		wpcp_log( __( 'Post insertion failed message ' . $exception->getMessage() ), 'critical' );
	}

}

/**
 * Insert new link in wpcp_links table
 *
 * @param array $data
 *
 * @return false|int
 */
function wpcp_insert_link( array $data ) {

	$data  = wp_parse_args( $data, [
		'camp_id'    => null,
		'url'        => '',
		'keyword'    => '',
		'identifier' => null,
		'camp_type'  => '',
		'page'       => null,
		'status'     => 0,
		'hash'       => null,
	] );

	global $wpdb;
	$table = $wpdb->prefix . 'wpcp_links';

	$sql = "SELECT id FROM {$table} where url = '{$data['url']}';";
	$exist = $wpdb->get_results($sql);

	if( !empty($exist) ){
		return false;
	}

	$wpdb->show_errors();
	$id    = $wpdb->insert(
		$table,
		$data
	);
	return $id;
}

/**
 * Update link in wpcp_links table
 *
 * @param int $id
 * @param array $data
 *
 * @return false|int|null
 */
function wpcp_update_link( $id, array $data ) {
	global $wpdb;
	$table = $wpdb->prefix . 'wpcp_links';

	$id = $wpdb->update(
		$table,
		$data,
		[ 'id' => $id ]
	);

	return $id;
}
