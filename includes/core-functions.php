<?php
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
 * Set up requst
 *
 * @since 1.0.0
 *
 * @param      $campaign_type
 * @param null $url
 * @param null $campaign_id
 *
 * @return \Curl\Curl
 * @throws \ErrorException
 */
function wpcp_setup_request( $campaign_type, $url = null, $campaign_id = null ) {
	if ( $url !== null ) {
		$curl = new \Curl\Curl( $url );
	} else {
		$curl = new \Curl\Curl();
	}

	$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
	$curl->setOpt( CURLOPT_TIMEOUT, 30 );
	$curl->setOpt( CURLOPT_RETURNTRANSFER, true );

	return apply_filters( 'content_pilot_setup_request', $curl, $campaign_id, $campaign_type );
}

/**
 * Check the response
 *
 * @since 1.0.0
 *
 * @param \Curl\Curl $request
 *
 * @return null|\WP_Error
 *
 */
function wpcp_is_valid_response( \Curl\Curl $request ) {
	if ( empty( $request ) ) {
		return new WP_Error( 'nothing-in-response', __( 'Nothing in the response object', 'content-pilot' ) );
	}

	if ( $request->error ) {
		return new WP_Error( $request->errorCode, $request->curlErrorMessage );
	}

	return $request->response;
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
 * Get plugin settings
 *
 * @since 1.0.0
 * @since 1.0.1 section has been added
 *
 * @param        $section
 * @param        $field
 * @param bool   $default
 *
 * @return string|array|bool
 */
function wpcp_get_settings( $field, $section = 'wpcp_settings', $default = false ) {
	$settings = get_option( $section );

	if ( isset( $settings[ $field ] ) && ! empty( $settings[ $field ] ) ) {
		return is_array( $settings[ $field ] ) ? array_map( 'trim', $settings[ $field ] ) : trim( $settings[ $field ] );
	}

	return $default;
}

/**
 * Update settings
 *
 * @since 1.0.0
 *
 * @param $field
 * @param $data
 */
function wpcp_update_settings( $field, $data ) {
	$settings           = get_option( 'wpcp_settings' );
	$settings[ $field ] = $data;
	update_option( 'wpcp_settings', $settings );
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
 * Disable any keyword
 *
 * @since 1.0.0
 *
 * @param        $keyword
 * @param string $meta_value
 */
function wpcp_disable_keyword( $campaign_id, $keyword, $meta_value = 'keywords' ) {
	do_action( 'wpcp_disable_keyword', $campaign_id, $keyword );

	$keywords_string = wpcp_get_keyword( $campaign_id );
	$parts           = wpcp_string_to_array( $keywords_string, PHP_EOL, array( 'trim' ) );
	$key             = array_search( $keyword, $parts );

	if ( $key !== false ) {
		unset( $parts[ $key ] );
	}

	update_post_meta( $campaign_id, $meta_value, implode( PHP_EOL, $parts ) );

	$disabled_keywords   = (array) wpcp_get_post_meta( $campaign_id, '_disabled_keywords', '' );
	$disabled_keywords[] = $keyword;
	$disabled_keywords   = array_filter( $disabled_keywords );
	update_post_meta( $campaign_id, '_disabled_keywords', $disabled_keywords );

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

	$data = wp_parse_args( $data, [
		'camp_id'     => '',
		'post_id'     => '',
		'keyword'     => '',
		'camp_type'   => '',
		'status'      => '',
		'url'         => '',
		'title'       => '',
		'image'       => '',
		'content'     => '',
		'raw_content' => '',
		'score'       => '',
	] );

	global $wpdb;
	$table = $wpdb->prefix . 'wpcp_links';

	$exist = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$table} where url = %s", $data['url'] ) );

	if ( ! empty( $exist ) ) {
		return false;
	}


	$id = $wpdb->insert(
		$table,
		$data
	);

	return $id;
}

/**
 * Update link in wpcp_links table
 *
 * @param int   $id
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
