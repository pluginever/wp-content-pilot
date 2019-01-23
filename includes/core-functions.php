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
 * Make a get request
 *
 * since 1.0.0
 *
 * @param       $url
 * @param array $args
 * @param array $options
 *
 * @return \Curl\Curl
 */
function wpcp_remote_get( $url, $args = array(), $options = array(), $headers = array() ) {
	return wpcp_remote_request( $url, $args, $options, $headers, 'GET' );
}

/**
 * Make a post request
 *
 * since 1.0.0
 *
 * @param       $url
 * @param array $args
 * @param array $options
 *
 * @return \Curl\Curl
 */
function wpcp_remote_post( $url, $args = array(), $options = array(), $headers = array() ) {
	return wpcp_remote_request( $url, $args, $options, $headers, 'POST' );
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
	$curl = new \Curl\Curl( $url );
	$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
	$curl->setOpt( CURLOPT_TIMEOUT, 30 );
	$curl->setOpt( CURLOPT_RETURNTRANSFER, true );

	$options = apply_filters( 'wpcp_remote_request_options', $options );
	if ( ! empty( $options ) ) {
		foreach ( $options as $param => $value ) {
			$curl->setOpt( $param, $value );
		}
	}

	$headers = apply_filters( 'wpcp_remote_request_headers', $headers );
	if ( ! empty( $headers ) ) {
		foreach ( $headers as $param => $value ) {
			$curl->setHeader( $param, $value );
		}
	}
	switch ( $type ) {
		case 'POST':
			$curl->post( null, $args );
			break;
		default:
			$curl->get( null, $args );
			break;
	}

	return $curl;
}

/**
 * get response headers
 * since 1.0.0
 *
 * @param  \Curl\Curl $response
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

	if ( $response->error ) {
		return new WP_Error( 'request-failed', $response->errorMessage );
	}

	return $response->response;
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
 * @param bool $default
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
	do_action( 'wpcp_disable_campaign', $camp_id );
	wpcp_update_post_meta( $camp_id, '_campaign_status', 'inactive' );
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
	$instance = new $module_class();

	//set the module
	$is_error = $instance->setup();

	//check error
	if ( is_wp_error( $is_error ) ) {
		wpcp_disable_campaign( $campaign_id );

		return $is_error;
	}

	$instance->set_campaign_id( $campaign_id );

	$instance->set_keyword( $keyword );

	$instance->set_campaign_type( $campaign_type );

	try {
		$article = $instance->run();
	} catch ( Exception $exception ) {
		wpcp_log( __( 'Post insertion failed message ' . $exception->getMessage() ), 'critical' );
	}

	if ( is_wp_error( $article ) ) {
		return $article;
	}
	wpcp_log( sprintf( __( "Post Insertion was success Post ID: %s", 'wp-content-pilot' ), $article ), 'log' );

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

	return $textStatistics->fleschKincaidReadingEase( $html );
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
	$configuration = new \andreskrey\Readability\Configuration( [
		'fixRelativeURLs' => true,
		'originalURL'     => $url,
		// other parameters ... listing below
	] );

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
 * Download image from url
 * since 1.0.0
 *
 * @param $url
 *
 * @return bool|int
 */
function wpcp_download_image( $url ) {
	$url     = explode( '?', esc_url_raw( $url ) );
	$url     = $url[0];
	$get     = wp_remote_get( $url );
	$headers = wp_remote_retrieve_headers( $get );
	$type    = isset( $headers['content-type'] ) ? $headers['content-type'] : null;
	if ( is_wp_error( $get ) || ! isset( $type ) || ( ! in_array( $type, [ 'image/png', 'image/jpeg' ] ) ) ) {
		return false;
	}

	$mirror     = wp_upload_bits( basename( $url ), '', wp_remote_retrieve_body( $get ) );
	$attachment = array(
		'post_title'     => basename( $url ),
		'post_mime_type' => $type
	);

	$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );

	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
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
 * @since 1.0.0
 *
 * @param      $campaign_id
 * @param null $campaign_type
 *
 * @return object|\WP_Error
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
 * convert cents into usd
 *
 * @since 1.0.0
 *
 * @param $cent
 *
 * @return string
 */
function wpcp_cent_to_usd( $cent ) {
	return number_format( ( $cent / 100 ), 2, '.', ' ' );
}

/**
 * returns the modules that support keyword suggestion
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_keyword_suggestion_supported_modules() {
	$modules = array( 'article', 'youtube', 'envato', 'flickr' );

	return apply_filters( 'wpcp_keyword_suggestion_supported_modules', $modules );
}

/**
 * Get post categories
 *
 * @since 1.0.3
 * @return array
 */
function wpcp_get_post_categories() {

	$args = [
		'taxonomy'   => 'category',
		'hide_empty' => false
	];

	$categories = get_terms( $args );

	return wp_list_pluck( $categories, 'name', 'term_id' );
}

/**
 * Get post categories
 *
 * @since 1.0.3
 * @return array
 */
function wpcp_get_post_tags() {

	$args = [
		'taxonomy'   => 'post_tag',
		'hide_empty' => false
	];

	$tags = get_terms( $args );

	return wp_list_pluck( $tags, 'name', 'term_id' );
}

/**
 * Get all the authors
 *
 * @since 1.0.0
 *
 * @return array
 *
 */
function wpcp_get_authors() {
	$result = [];
	$users  = get_users( [ 'who' => 'authors' ] );
	foreach ( $users as $user ) {
		$result[ $user->ID ] = "{$user->display_name} ({$user->user_email})";
	}

	return $result;
}

/**
 * Get posts
 *
 * @param $args
 *
 * @return array
 */

function wpcp_get_posts( $args ) {
	$args = wp_parse_args( $args, array(
		'post_type'      => 'post',
		'meta_key'       => '',
		'meta_value'     => '',
		'post_status'    => 'publish',
		'posts_per_page' => 15,
		'paged'          => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$posts = get_posts($args);

	return $posts;
}
