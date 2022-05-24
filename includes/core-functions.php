<?php
defined( 'ABSPATH' ) || exit();


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
 * @param $title
 *
 * @return string|null
 * @since 1.2.0
 */
function wpcp_is_duplicate_title( $title ) {
	global $wpdb;

	return ! empty( $wpdb->get_var( $wpdb->prepare( "SELECT id from $wpdb->wpcp_links WHERE title=%s", $title ) ) );
}

/**
 * @param $url
 *
 * @return string|null
 * @since 1.2.0
 */
function wpcp_is_duplicate_url( $url ) {
	global $wpdb;

	return $wpdb->get_row( $wpdb->prepare( "SELECT id from $wpdb->wpcp_links WHERE url=%s", $url ) );
}


/**
 * @param $camp_id
 *
 * @since 1.2.0
 */
function wpcp_disable_campaign( $campaign_id ) {
	wpcp_update_post_meta( $campaign_id, '_campaign_status', 'inactive' );
	do_action( 'wpcp_disable_campaign', $campaign_id );
}

/**
 * Returns wpcp meta values
 *
 * @param      $campaign_id
 * @param      $key
 * @param null $default
 *
 * @return null|string|array
 */
function wpcp_get_post_meta( $campaign_id, $key, $default = null ) {
	$meta_value = get_post_meta( $campaign_id, $key, true );

	if ( $meta_value === false || $meta_value === '' ) {
		$value = $default;
	} else {
		$value = get_post_meta( $campaign_id, $key, true );
	}

	return is_string( $value ) ? trim( $value ) : $value;
}

/**
 * Save post meta
 *
 * @param $post_id
 * @param $key
 * @param $value
 *
 * @since 1.0.0
 *
 */
function wpcp_update_post_meta( $post_id, $key, $value ) {
	update_post_meta( $post_id, $key, $value );
}

/**
 * Get admin view
 * since 1.0.0
 *
 * @param $template_name
 * @param array $args
 */
function wpcp_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	if ( file_exists( WPCP_INCLUDES . '/admin/views/' . $template_name ) ) {
		include WPCP_INCLUDES . '/admin/views/' . $template_name;
	}
}


/**
 * Get post categories
 *
 * @return array
 * @since 1.0.3
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
 * @return array
 * @since 1.0.3
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
 * @return array
 *
 * @since 1.0.0
 *
 */
function wpcp_get_authors() {
	$result = [];
	$users  = get_users( [ 'capability__in' => array('publish_posts') ] );

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
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'paged'          => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$posts = get_posts( $args );

	return $posts;
}

/**
 * hyperlink any text
 *
 * since 1.0.0
 *
 * @param $text
 *
 * @return string
 */
function wpcp_hyperlink_text( $text ) {
	return preg_replace( '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$0</a>', $text );
}

/**
 * allow html tag when string from content
 *
 * @param $content
 * @param $length
 * @param bool $html
 *
 * @return string
 */
function wpcp_truncate_content( $content, $length, $html = true ) {
	if ( $html ) {
		// if the plain text is shorter than the maximum length, return the whole text
		if ( strlen( preg_replace( '/<.*?>/', '', $content ) ) <= $length ) {
			return $content;
		}
		//Balances tags of string using a modified stack.
		$content = force_balance_tags( html_entity_decode( wp_trim_words( htmlentities( $content ), $length, '...' ) ) );
	} else {
		$content = wp_trim_words( $content, $length );
	}

	return $content;
}


/**
 * Download image from url
 * since 1.0.0
 *
 * @param $url
 *
 * @return bool|int
 */
function wpcp_download_image( $url, $description = '' ) {
	$raw_url  = $url;
	$url      = explode( '?', esc_url_raw( $url ) );
	$url      = $url[0];
	$get      = wp_remote_get( $raw_url );
	$headers  = wp_remote_retrieve_headers( $get );
	$type     = isset( $headers['content-type'] ) ? $headers['content-type'] : null;
	$types    = array(
		'image/png',
		'image/jpeg',
		'image/gif',
	);
	$file_ext = array(
		'image/png'  => '.png',
		'image/jpeg' => '.jpg',
		'image/gif'  => '.gif',
	);
	if ( is_wp_error( $get ) || ! isset( $type ) || ( ! in_array( $type, $types ) ) ) {
		return false;
	}
	$file_name = basename( $url );
	$ext       = pathinfo( basename( $file_name ), PATHINFO_EXTENSION );

	if ( $ext === '' ) {
		$file_name .= $file_ext[ $type ];
	}

	$mirror     = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $get ) );
	$attachment = array(
		'post_title'     => $file_name,
		'post_mime_type' => $type,
		'post_content'   => $description,
	);

	if ( empty( $mirror['file'] ) ) {
		return false;
	}

	$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );

	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}

/**
 * Add admin notice
 * since 1.2.0
 *
 * @param $notice
 * @param string $type
 * @param bool $dismissible
 */
function wpcp_admin_notice( $notice, $type = 'success' ) {
	WPCP_Admin_Notices::add_notice( $notice, [ 'type' => $type ], true );
}

/**
 * @param $message
 * @param string $level
 * @param string $camp_id
 *
 * @since 1.2.0
 */
function wpcp_insert_log( $message, $level = 'info', $camp_id = '0' ) {
	global $wpdb;
	$wpdb->insert(
		"$wpdb->wpcp_logs",
		array(
			'camp_id'     => $camp_id,
			'level'       => $level,
			'message'     => strip_tags( $message ),
			'instance_id' => defined( 'WPCP_CAMPAIGN_INSTANCE' ) && WPCP_CAMPAIGN_INSTANCE ? WPCP_CAMPAIGN_INSTANCE : null,
			'created_at'  => current_time( 'mysql' ),
		)
	);
}


/**
 * @return array|bool|WP_Error
 * @since 1.2.0
 */
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
 * create & return terms
 *
 * @param $terms
 * @param string $taxonomy
 *
 * @return array
 * @since 1.2.0
 */
function wpcp_get_terms( $terms, $taxonomy = 'category' ) {
	$term_ids = [];
	if ( ! is_array( $terms ) ) {
		$terms = wpcp_string_to_array( $terms );
	}
	foreach ( $terms as $term ) {
		$t = get_term_by( 'name', $term, $taxonomy );
		if ( false == $t ) {
			$t = wp_insert_term( $term, $taxonomy );
		}

		if ( is_wp_error( $t ) ) {
			continue;
		}

		if ( is_array( $t ) && isset( $t['term_id'] ) ) {
			$term_ids[] = $t['term_id'];
		}

		if ( $t instanceof WP_Term ) {
			$term_ids[] = $t->term_id;
		}
	}

	return array_map( 'intval', $term_ids );
}

/**
 * @param $terms
 * @param $post_id
 * @param $taxonomy
 * @param bool $append
 *
 * @return array|bool|false|WP_Error
 * @since 1.2.0
 */
function wpcp_set_post_terms( $terms, $post_id, $taxonomy, $append = true ) {
	if ( ! is_array( $terms ) ) {
		$terms = wpcp_string_to_array( $terms );
	}
	$terms = array_filter( $terms );
	$terms = array_unique( $terms );
	$terms = wpcp_get_terms( $terms, $taxonomy );
	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return false;
	}

	return wp_set_post_terms( $post_id, $terms, $taxonomy, $append );
}

/**
 * @param $content
 * @param $word
 *
 * @return bool
 * @since 1.2.0
 */
function wpcp_content_contains_word( $content, $word ) {
	if ( empty( $word ) ) {
		return false;
	}
	$content = strip_tags( $content );
	if ( strpos( $content, $word ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Trigger skip duplicate title campaigns
 *
 *
 * @param $skip
 * @param $title
 *
 * @return bool
 * @since 1.2.0
 */
function wpcp_maybe_skip_duplicate_title( $skip, $title, $campaign_id ) {
	if ( 'on' == wpcp_get_post_meta( $campaign_id, '_enable_duplicate_title' ) ) {
		return false;
	}

	return wpcp_is_duplicate_title( $title );
}

add_filter( 'wpcp_skip_duplicate_title', 'wpcp_maybe_skip_duplicate_title', 10, 3 );

/**
 * Setup curl request
 *
 * @return \Curl\Curl
 * @since 1.2.5
 */
function wpcp_setup_request( $referrer = 'http://www.bing.com/' ) {
	$jar = get_option( 'wpcp_cookie_jar' );
	if ( empty( $jar ) ) {
		$jar = substr( md5( time() ), 0, 5 );
		update_option( 'wpcp_cookie_jar', $jar );
	}
	$upload_dir = wp_upload_dir();
	$curl       = new Curl\Curl();
	$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
	$curl->setOpt( CURLOPT_TIMEOUT, 30 );
	$curl->setOpt( CURLOPT_MAXREDIRS, 3 );
	$curl->setOpt( CURLOPT_RETURNTRANSFER, true );
	$curl->setOpt( CURLOPT_REFERER, $referrer );
	$curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );
	$curl->setOpt( CURLOPT_COOKIEJAR, untrailingslashit( $upload_dir['basedir'] ) . '/' . $jar );
	$curl->setOpt( CURLOPT_COOKIEJAR, $jar );
	$curl->setOpt( CURLOPT_SSL_VERIFYPEER, false );

	return $curl;
}

/**
 * Calculate discount percentage from sale price
 *
 * @param $original_price
 * @param null $sale_price
 *
 * @return float|int
 * @since 1.2.5
 */
function wpcp_calculate_discount_percent( $original_price, $sale_price = null ) {
	if ( empty( $sale_price ) ) {
		$sale_price = $original_price;
	}

	$sale_price     = preg_replace( '/[^\\d.]+/', '', $sale_price );
	$original_price = preg_replace( '/[^\\d.]+/', '', $original_price );
	if ( empty( $sale_price ) || empty( $original_price ) ) {
		return 0;
	}

	return ( 100 - ( ( 100 / $original_price ) * $sale_price ) );
}


/**
 * @param $content
 *
 * @return mixed
 * @since 1.2.6
 */
function wpcp_spin_article( $campaign_id, $content ) {
	$args = apply_filters( 'wpcp_spinwritter_request_args', [
		'email_address'        => wpcp_get_settings( 'spinrewriter_email', 'wpcp_article_spinner' ),
		'api_key'              => wpcp_get_settings( 'spinrewriter_api_key', 'wpcp_article_spinner' ),
		'action'               => wpcp_get_post_meta( $campaign_id, '_spinner_action', 'unique_variation' ),
		'text'                 => $content,
		'auto_protected_terms' => wpcp_get_post_meta( $campaign_id, '_spinner_auto_protected_terms', false ),
		'confidence_level'     => wpcp_get_post_meta( $campaign_id, '_spinner_confidence_level', 'high' ),
		'auto_sentences'       => wpcp_get_post_meta( $campaign_id, '_spinner_auto_sentences', false ),
		'auto_paragraphs'      => wpcp_get_post_meta( $campaign_id, '_spinner_auto_paragraphs', false ),
		'auto_new_paragraphs'  => wpcp_get_post_meta( $campaign_id, '_spinner_auto_new_paragraphs', false ),
		'auto_sentence_trees'  => wpcp_get_post_meta( $campaign_id, '_spinner_auto_sentence_trees', false ),
		'use_only_synonyms'    => wpcp_get_post_meta( $campaign_id, '_spinner_use_only_synonyms', false ),
		'reorder_paragraphs'   => wpcp_get_post_meta( $campaign_id, '_spinner_reorder_paragraphs', false ),
		'nested_spintax'       => wpcp_get_post_meta( $campaign_id, '_spinner_nested_spintax', false ),
	] );

	if ( empty( $args['email_address'] ) || empty( $args['api_key'] ) ) {
		wpcp_logger()->error( __( 'spinwritter API details is not set, aborting article spinner', 'wp-content-pilot' ) );

		return $content;
	}
	@set_time_limit( 150 );
	$curl     = wpcp_setup_request();
	$endpoint = 'http://www.spinrewriter.com/action/api';
	$curl->post( $endpoint, $args );
	if ( $curl->isError() ) {
		wpcp_logger()->error( __( 'Spinwritter could not send API request, aborting article spinner', 'wp-content-pilot' ) );

		return $content;
	}
	$response = json_decode( $curl->getResponse() );

	if ( isset( $response->status ) && $response->status == 'ERROR' ) {
		wpcp_logger()->error( sprintf( __( 'Aborting article spinner Because [%s]', 'wp-content-pilot' ), $response->response ) );

		return $content;
	}

	if ( isset( $response->status ) && $response->status == 'OK' && ! empty( $response->response ) ) {
		$pattern      = "#< iframe[^>]+>#is";
		$iframeCheck  = preg_match( $pattern, $response->response );
		$new_response = ( $iframeCheck == 1 ) ? preg_replace( $pattern, "", $response->response ) : $response->response;

		return force_balance_tags( $new_response );
		//return force_balance_tags($response->response);
	}


	return $content;
}
