<?php
defined( 'ABSPATH' ) || exit();

/**
 * get all the modules
 *
 * @return array
 * @since 1.0.0
 */
function wpcp_get_modules() {
	$modules = [];
	foreach ( content_pilot()->modules->get_modules() as $module_name => $module ) {
		$modules[ $module_name ] = $module['title'];
	}

	return $modules;
}

/**
 * Make a remote request
 *
 * since 1.0.0
 *
 * @param        $url
 * @param array $args
 * @param array $options
 * @param string $type
 *
 * @return \Curl\Curl
 */
function wpcp_remote_request( $url, $args = array(), $options = array(), $headers = array(), $type = 'GET' ) {
	global $wpcp_curl;
	global $wpcp_request_cookies;
	$wpcp_curl = new \Curl\Curl( $url );
	$wpcp_curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
	$wpcp_curl->setOpt( CURLOPT_TIMEOUT, 30 );
	$wpcp_curl->setOpt( CURLOPT_RETURNTRANSFER, true );

	if ( isset( $wpcp_request_cookies ) && is_array( $wpcp_request_cookies ) && ! empty( $wpcp_request_cookies ) ) {
		foreach ( $wpcp_request_cookies as $cookie_key => $cookie_value ) {
			$wpcp_curl->setCookie( $cookie_key, $cookie_value );
		}
	}


	$options = apply_filters( 'wpcp_remote_request_options', $options );
	if ( ! empty( $options ) ) {
		foreach ( $options as $param => $value ) {
			$wpcp_curl->setOpt( $param, $value );
		}
	}

	$wpcp_curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );

	$headers = apply_filters( 'wpcp_remote_request_headers', $headers );
	if ( ! empty( $headers ) ) {
		foreach ( $headers as $param => $value ) {
			$wpcp_curl->setHeader( $param, $value );
		}
	}
	switch ( $type ) {
		case 'POST':
			$wpcp_curl->post( null, $args );
			break;
		default:
			$wpcp_curl->get( null, $args );
			break;
	}


	return $wpcp_curl;
}

/**
 * get response headers
 * since 1.0.0
 *
 * @param \Curl\Curl $response
 * @param null $param
 *
 * @return string
 */
function wpcp_remote_headers( $response, $param = null ) {
	if ( $response->error || ( $param && empty( $response->responseHeaders[ $param ] ) ) ) {
		return '';
	}

	if ( $param && ! empty( $response->responseHeaders[ $param ] ) ) {
		return $response->responseHeaders[ $param ];
	}

	return $response->responseHeaders;
}

/**
 *
 * since 1.0.0
 *
 * @param \Curl\Curl $response
 *
 * @return \SimpleXMLElement | \WP_Error
 */
function wpcp_retrieve_body( $response ) {
	global $wpcp_curl;
	$wpcp_curl->close();

	if ( $response->error ) {
		return new WP_Error( 'request-failed', $response->errorMessage );
	}

	return $response->response;
}

/**
 * Logger for the plugin
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
 * @since    1.0.0
 *
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
		$camp_id = content_pilot()->get_campaign_id();
		// $camp_id = isset( content_pilot()->campaign_id ) ? content_pilot()->campaign_id : null;
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
 * Save option
 *
 * @param $key
 * @param $value
 *
 * @since 1.0.0
 *
 */
function wpcp_update_option( $key, $value ) {
	update_option( $key, $value );
}


/**
 * Get plugin settings
 *
 * @param        $section
 * @param        $field
 * @param bool $default
 *
 * @return string|array|bool
 * @since 1.0.0
 * @since 1.0.1 section has been added
 *
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
 * @param $field
 * @param $data
 *
 * @since 1.0.0
 *
 */
function wpcp_update_settings( $field, $data ) {
	$settings           = get_option( 'wpcp_settings' );
	$settings[ $field ] = $data;
	update_option( 'wpcp_settings', $settings );
}

/**
 * Mark campaign as disabled
 *
 * @param $camp_id
 *
 * @since 1.0.0
 *
 */
function wpcp_disable_campaign( $camp_id ) {
	wpcp_update_post_meta( $camp_id, '_campaign_status', 'inactive' );
	do_action( 'wpcp_disable_campaign', $camp_id );
}

/**
 * Disable any keyword
 *
 * @param        $keyword
 * @param string $meta_value
 *
 * @since 1.0.0
 *
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
 * @return array
 * @since 1.0.0
 *
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
 * @param $campaign_id
 *
 * @return string
 * @since 1.0.0
 *
 */
function wpcp_get_keyword( $campaign_id ) {
	$keyword = wpcp_get_post_meta( $campaign_id, '_keywords', '' );

	return apply_filters( 'wpcp_keyword', $keyword, $campaign_id );
}

/**
 * Checks if campaign is valid or not
 *
 * @param $campaign_id
 *
 * @return bool|\WP_Error
 *
 * @since 1.0.0
 *
 */
function wpcp_campaign_can_run( $campaign_id ) {
	if ( ! get_post( $campaign_id ) ) {
		return new \WP_Error( 'invalid-campaign-id', __( 'Campaign is not exist.', 'wp-content-pilot' ) );
	}

	if ( 'active' !== get_post_meta( $campaign_id, '_campaign_status', true ) ) {
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
 * @param $campaign_id
 *
 * @return bool|\WP_Error
 * @since 1.0.0
 *
 */
function wpcp_run_campaign( $campaign_id ) {
	$can_run = wpcp_campaign_can_run( $campaign_id );
	content_pilot()->set_campaign_id( $campaign_id );
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

	/* @var $module_class WPCP_Campaign */
	$instance = new $module_class();

	$instance->set_campaign_id( $campaign_id );
	$instance->set_keyword( $keyword );

	//set the module
	$is_error = $instance->setup();

	wpcp_log( 'loaded module ' . $module_class );

	//check error
	if ( is_wp_error( $is_error ) ) {
		wpcp_disable_campaign( $campaign_id );

		return $is_error;
	}


	$instance->set_campaign_type( $campaign_type );

	try {
		$article = $instance->run();
	} catch ( Exception $exception ) {
		wpcp_log( __( 'Post insertion failed message ', 'wp-content-pilot' ) . $exception->getMessage(), 'critical' );
	}

	if ( is_wp_error( $article ) ) {
		return $article;
	}
	wpcp_log( sprintf( __( "Post Insertion was success Post ID: %s", 'wp-content-pilot' ), $article ), 'log' );

	content_pilot()->set_campaign_id();

	return $article;

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
		'gmt_date'    => '',
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

function wpcp_get_link( $id ) {
	global $wpdb;
	$table  = $wpdb->prefix . 'wpcp_links';
	$sql    = $wpdb->prepare( "select * from {$table} where id = %d", $id );
	$result = $wpdb->get_row( $sql );

	//$result = array_map( 'maybe_unserialize', $result );

	return $result;
}

/**
 * find readability score
 *
 * since 1.0.0
 *
 * @param $html
 *
 * @return float|int
 */
function wpcp_get_read_ability_score( $html ) {
	$textStatistics = new \DaveChild\TextStatistics\TextStatistics();

	return @$textStatistics->fleschKincaidReadingEase( $html );
}

/**
 * Find readability of a given HTML
 * since 1.0.0
 *
 * @param $html
 * @param $url
 *
 * @return array|\WP_Error
 */
function wpcp_get_readability( $html, $url ) {
	$configuration = new \andreskrey\Readability\Configuration();
	$configuration->setFixRelativeURLs( true );
	$configuration->setOriginalURL( $url );
	$readability = new \andreskrey\Readability\Readability( $configuration );
	try {
		$readability->parse( $html );
	} catch ( \andreskrey\Readability\ParseException $e ) {
		return new WP_Error( 'readability-error', $e->getMessage() );
	}
	$title   = $readability->getTitle();
	$content = $readability->getContent();
	$image   = $readability->getImage();
	$content = balanceTags( $content, true );
	$content = ent2ncr( $content );
	$content = convert_chars( $content );
	$content = wpcp_remove_empty_tags_recursive( $content );
	$content = wpcp_remove_unauthorized_html( $content );
	$content = wpcp_fix_image_src( $content );

	if ( empty( $image ) ) {
		$images = wpcp_get_all_image_urls( $content );
		if ( ! empty( $images ) ) {
			$image = $images[0];
		}
	}


	$article = [
		'content' => $content,
		'title'   => $title,
		'image'   => $image,
	];

	return $article;
}

/**
 * Replace template tag with content
 *
 * since 1.0.0
 *
 * @param       $content
 * @param array $article
 *
 * @return string
 */
function wpcp_replace_template_tags( $content, $article = array() ) {

	$content = str_replace( '{title}', empty( $article['title'] ) ? '' : $article['title'], $content );
	$content = str_replace( '{content}', empty( $article['content'] ) ? '' : $article['content'], $content );
	$content = str_replace( '{image_url}', empty( $article['image_url'] ) ? '' : $article['image_url'], $content );
	$content = str_replace( '{source_url}', empty( $article['source_url'] ) ? '' : $article['source_url'], $content );
	$content = str_replace( '{date}', empty( $article['date'] ) ? '' : $article['date'], $content );

	$content = apply_filters( 'wpcp_replace_template_tags', $content, $article );

	return html_entity_decode( $content );
}

/**
 * checks for links
 *
 * @param      $campaign_id
 * @param null $campaign_type
 *
 * @return object|\WP_Error
 * @since 1.0.0
 *
 */
function wpcp_get_ready_campaign_links( $campaign_id, $campaign_type = null ) {
	if ( ! $campaign_type ) {
		$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );
	}
	global $wpdb;

	$link = $wpdb->get_row( $wpdb->prepare( "select * from {$wpdb->prefix}wpcp_links where camp_id=%d AND camp_type=%s AND status=%s", $campaign_id, $campaign_type, 'ready' ) );

	if ( empty( $link ) ) {
		return new WP_Error( 'no-links', __( 'The campaign is not ready yet for testing, please allow some time', 'wp-content-pilot' ) );
	}

	return $link;
}


/**
 * returns the modules that support keyword suggestion
 *
 * @return array
 * @since 1.0.0
 */
function wpcp_get_keyword_suggestion_supported_modules() {
	$modules = array( 'article', 'youtube', 'envato', 'flickr' );

	return apply_filters( 'wpcp_keyword_suggestion_supported_modules', $modules );
}






/**
 * get random user agent
 * since 1.0.0
 *
 * @return string
 */
function wpcp_get_random_user_agent() {
	$agents = array(
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0",
		"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:55.0) Gecko/20100101 Firefox/55.0",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:55.0) Gecko/20100101 Firefox/55.0",
		"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36"
	);
	$rand   = rand( 0, count( $agents ) - 1 );

	return trim( $agents[ $rand ] );
}

/**
 * Get latest 10 logs for campaign
 *
 * @param int $campaign_id
 *
 * @return array|bool|null
 */
function wpcp_get_latest_logs( $campaign_id ) {
	global $wpdb;

	if ( ! isset( $campaign_id ) || ! $campaign_id ) {
		return false;
	}

	$campaign_id = addslashes( $campaign_id );

	$sql = "SELECT * FROM {$wpdb->prefix}wpcp_logs WHERE `log_level`='log' AND `camp_id`='{$campaign_id}' ORDER BY `created_at` DESC LIMIT 10;";

	$logs = $wpdb->get_results( $sql );

	return $logs;
}


function wpcp_check_cron_status() {
	global $wp_version;

	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		/* translators: 1: The name of the PHP constant that is set. */
		return new WP_Error( 'crontrol_info', sprintf( __( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'wp-content-pilot' ), 'DISABLE_WP_CRON' ) );
	}

	if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
		/* translators: 1: The name of the PHP constant that is set. */
		return new WP_Error( 'crontrol_info', sprintf( __( 'The %s constant is set to true.', 'wp-content-pilot' ), 'ALTERNATE_WP_CRON' ) );
	}

	$cached_status = get_transient( 'wpcp-cron-test-ok' );

	if ( $cached_status ) {
		return true;
	}

	$sslverify     = version_compare( $wp_version, 4.0, '<' );
	$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

	$cron_request = apply_filters( 'cron_request', array(
		'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
		'key'  => $doing_wp_cron,
		'args' => array(
			'timeout'   => 3,
			'blocking'  => true,
			'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
		),
	) );

	$cron_request['args']['blocking'] = true;

	$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

	if ( is_wp_error( $result ) ) {
		return $result;
	} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
		return new WP_Error( 'unexpected_http_response_code', sprintf(
		/* translators: 1: The HTTP response code. */
			__( 'Unexpected HTTP response code: %s', 'wp-content-pilot' ),
			intval( wp_remote_retrieve_response_code( $result ) )
		) );
	} else {
		set_transient( 'wpcp-cron-test-ok', 1, 3600 );

		return true;
	}

}



/**
 * @param $campaign_id
 * @param int $limit
 *
 * @return int
 */
function wpcp_perpage_data_fetch_limit( $campaign_id, $limit = 50 ) {
	$target = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
	$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
	$need   = $target - $posted;
	if ( $limit > $need && $need > 0 ) {
		$limit = $need + ceil( ( $need / 100 ) * 10 );
	}

	return $limit;
}
