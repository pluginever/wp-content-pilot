<?php
defined( 'ABSPATH' ) || exit();

/**
 * Get random user agent.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_get_random_user_agent() {
	$agents = array(
		'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
		'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
		'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
		'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0',
		'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0',
		'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
		'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
		'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0',
		'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:55.0) Gecko/20100101 Firefox/55.0',
		'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:55.0) Gecko/20100101 Firefox/55.0',
		'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
	);
	$rand   = wp_rand( 0, count( $agents ) - 1 );

	return trim( $agents[ $rand ] );
}


/**
 * Get plugin settings.
 *
 * @param string     $field Field key.
 * @param string     $section Settings section.
 * @param bool|array $default_settings Default settings.
 *
 * @since 1.0.0
 * @since 1.0.1 section has been added
 * @return string|array|bool
 */
function wpcp_get_settings( $field, $section = 'wpcp_settings', $default_settings = false ) {
	$settings = get_option( $section );

	if ( isset( $settings[ $field ] ) && ! empty( $settings[ $field ] ) ) {
		return is_array( $settings[ $field ] ) ? array_map( 'trim', $settings[ $field ] ) : trim( $settings[ $field ] );
	}

	return $default_settings;
}

/**
 * Update settings.
 *
 * @param string $field Field key.
 * @param array  $data array of settings data.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_update_settings( $field, $data ) {
	$settings           = get_option( 'wpcp_settings' );
	$settings[ $field ] = $data;
	update_option( 'wpcp_settings', $settings );
}

/**
 * Whether the title is duplicate or not.
 *
 * @param string $title The title.
 *
 * @return bool
 * @since 1.2.0
 */
function wpcp_is_duplicate_title( $title ) {
	global $wpdb;

	return ! empty( $wpdb->get_var( $wpdb->prepare( "SELECT id from $wpdb->wpcp_links WHERE title=%s", $title ) ) );
}

/**
 * Whether the URL is duplicate or not.
 *
 * @param string $url URL.
 *
 * @since 1.2.0
 * @return array|object|stdClass|null
 */
function wpcp_is_duplicate_url( $url ) {
	global $wpdb;

	return $wpdb->get_row( $wpdb->prepare( "SELECT id from $wpdb->wpcp_links WHERE url=%s", $url ) );
}

/**
 * Disable campaign.
 *
 * @param int $campaign_id Campaign ID.
 *
 * @since 1.2.0
 * @return void
 */
function wpcp_disable_campaign( $campaign_id ) {
	wpcp_update_post_meta( $campaign_id, '_campaign_status', 'inactive' );
	do_action( 'wpcp_disable_campaign', $campaign_id );
}

/**
 * Returns wpcp meta values.
 *
 * @param int        $campaign_id The campaign ID.
 * @param string     $key Key.
 * @param null|mixed $default_value Default meta value.
 *
 * @return null|string|array
 */
function wpcp_get_post_meta( $campaign_id, $key, $default_value = null ) {
	$meta_value = get_post_meta( $campaign_id, $key, true );

	if ( false === $meta_value || '' === $meta_value ) {
		$value = $default_value;
	} else {
		$value = get_post_meta( $campaign_id, $key, true );
	}

	return is_string( $value ) ? trim( $value ) : $value;
}

/**
 * Save post meta.
 *
 * @param int    $post_id The post ID.
 * @param string $key Meta key.
 * @param mixed  $value Meta value.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_update_post_meta( $post_id, $key, $value ) {
	update_post_meta( $post_id, $key, $value );
}

/**
 * Get admin view.
 *
 * @param string $template_name Template name.
 * @param array  $args Array of arguments.
 *
 * @since 1.0.0
 * @return void
 */
function wpcp_get_views( $template_name, $args = array() ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	if ( file_exists( WPCP_INCLUDES . '/admin/views/' . $template_name ) ) {
		include WPCP_INCLUDES . '/admin/views/' . $template_name;
	}
}

/**
 * Get post categories.
 *
 * @since 1.0.3
 * @return array
 */
function wpcp_get_post_categories() {

	$args = array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
	);

	$categories = get_terms( $args );

	return wp_list_pluck( $categories, 'name', 'term_id' );
}

/**
 * Get post categories.
 *
 * @since 1.0.3
 * @return array
 */
function wpcp_get_post_tags() {

	$args = array(
		'taxonomy'   => 'post_tag',
		'hide_empty' => false,
	);

	$tags = get_terms( $args );

	return wp_list_pluck( $tags, 'name', 'term_id' );
}

/**
 * Get all the authors.
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_authors() {
	$result = array();
	$users  = get_users( array( 'capability__in' => array( 'publish_posts' ) ) );

	foreach ( $users as $user ) {
		$result[ $user->ID ] = "{$user->display_name} ({$user->user_email})";
	}

	return $result;
}

/**
 * Get posts.
 *
 * @param array $args Array of query arguments.
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_posts( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'paged'          => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$posts = get_posts( $args );

	return $posts;
}

/**
 * Hyperlink any text.
 *
 * @param string $text Text.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_hyperlink_text( $text ) {
	return preg_replace( '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$0</a>', $text );
}

/**
 * Allow html tag when string from content.
 *
 * @param string $content The content.
 * @param int    $length Content length.
 * @param bool   $html Weather true or false.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_truncate_content( $content, $length, $html = true ) {
	if ( $html ) {
		// if the plain text is shorter than the maximum length, return the whole text.
		if ( strlen( preg_replace( '/<.*?>/', '', $content ) ) <= $length ) {
			return $content;
		}
		// Balances tags of string using a modified stack.
		$content = force_balance_tags( html_entity_decode( wp_trim_words( htmlentities( $content ), $length, '...' ) ) );
	} else {
		$content = wp_trim_words( $content, $length );
	}

	return $content;
}

/**
 * Download image from url.
 *
 * @param string $url URL.
 * @param string $description Description text.
 *
 * @since 1.0.0
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

	// If the image type is equal to : image/jpeg;charset=ISO-8859-1 the only take image/jpeg
	if ( ! in_array( $type, $types, true ) && strpos( $type, ';' ) !== false ) {
		$type = explode( ';', $type );
		$type = $type[0];
	}

	$file_ext = array(
		'image/png'  => '.png',
		'image/jpeg' => '.jpg',
		'image/gif'  => '.gif',
	);

	if ( is_wp_error( $get ) || ! isset( $type ) || ( ! in_array( $type, $types, true ) ) ) {
		wpcp_logger()->error( __( 'Failed to download image', 'wp-content-pilot' ) );
		return false;
	}
	$file_name = basename( $url );
	$ext       = pathinfo( basename( $file_name ), PATHINFO_EXTENSION );

	if ( '' === $ext ) {
		$file_name .= $file_ext[ $type ];
	}

	$mirror     = wp_upload_bits( $file_name, null, wp_remote_retrieve_body( $get ) );
	$attachment = array(
		'post_title'     => $file_name,
		'post_mime_type' => $type,
		'post_content'   => $description,
	);

	if ( empty( $mirror['file'] ) ) {
		return false;
	}

	$attach_id = wp_insert_attachment( $attachment, $mirror['file'] );

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}

/**
 * Add admin notice.
 *
 * @param string $notice Notice text.
 * @param string $type Notice type.
 *
 * @since 1.2.0
 * @return void
 */
function wpcp_admin_notice( $notice, $type = 'success' ) {
	WPCP_Admin_Notices::add_notice( $notice, array( 'type' => $type ), true );
}

/**
 * Insert logs.
 *
 * @param string $message Message text.
 * @param string $level Level string.
 * @param string $camp_id the campaign ID.
 *
 * @since 1.2.0
 * @return void
 */
function wpcp_insert_log( $message, $level = 'info', $camp_id = '0' ) {
	global $wpdb;
	$wpdb->insert(
		"$wpdb->wpcp_logs",
		array(
			'camp_id'     => $camp_id,
			'level'       => $level,
			'message'     => wp_strip_all_tags( $message ),
			'instance_id' => defined( 'WPCP_CAMPAIGN_INSTANCE' ) && WPCP_CAMPAIGN_INSTANCE ? WPCP_CAMPAIGN_INSTANCE : null,
			'created_at'  => current_time( 'mysql' ),
		)
	);
}

/**
 * Check the cron status.
 *
 * @since 1.2.0
 * @return array|bool|WP_Error
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

	$cron_request = apply_filters(
		'cron_request',
		array(
			'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
			'key'  => $doing_wp_cron,
			'args' => array(
				'timeout'   => 3,
				'blocking'  => true,
				'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
			),
		)
	);

	$cron_request['args']['blocking'] = true;

	$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

	if ( is_wp_error( $result ) ) {
		return $result;
	} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
		return new WP_Error(
			'unexpected_http_response_code',
			sprintf(
			/* translators: 1: The HTTP response code. */
				__( 'Unexpected HTTP response code: %s', 'wp-content-pilot' ),
				intval( wp_remote_retrieve_response_code( $result ) )
			)
		);
	} else {
		set_transient( 'wpcp-cron-test-ok', 1, 3600 );

		return true;
	}
}

/**
 * Create & return terms.
 *
 * @param array|string $terms Array of terms.
 * @param string       $taxonomy The taxonomy.
 *
 * @since 1.2.0
 * @return array
 */
function wpcp_get_terms( $terms, $taxonomy = 'category' ) {
	$term_ids = array();
	if ( ! is_array( $terms ) ) {
		$terms = wpcp_string_to_array( $terms );
	}
	foreach ( $terms as $term ) {
		$t = get_term_by( 'name', $term, $taxonomy );
		if ( false === $t ) {
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
 * Set the post term.
 *
 * @param array|string $terms Array of terms.
 * @param int          $post_id The post ID.
 * @param string       $taxonomy The taxonomy.
 * @param bool         $append Weather true or false.
 *
 * @since 1.2.0
 * @return array|bool|false|WP_Error
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
 * Check whether content has words.
 *
 * @param string $content The content.
 * @param string $word The words.
 *
 * @since 1.2.0
 * @return bool
 */
function wpcp_content_contains_word( $content, $word ) {
	if ( empty( $word ) ) {
		return false;
	}
	$content = wp_strip_all_tags( $content );
	if ( strpos( $content, $word ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Trigger skip duplicate title campaigns
 *
 * @param mixed  $skip Maybe skip.
 * @param string $title The title.
 * @param int    $campaign_id The campaign ID.
 *
 * @since 1.2.0
 * @return bool
 */
function wpcp_maybe_skip_duplicate_title( $skip, $title, $campaign_id ) {
	if ( 'on' === wpcp_get_post_meta( $campaign_id, '_enable_duplicate_title' ) ) {
		return false;
	}

	return wpcp_is_duplicate_title( $title );
}

add_filter( 'wpcp_skip_duplicate_title', 'wpcp_maybe_skip_duplicate_title', 10, 3 );

/**
 * Setup curl request.
 *
 * @param string $referrer Curl referrer.
 *
 * @since 1.2.5
 * @return \Curl\Curl
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
 * Calculate discount percentage from sale price.
 *
 * @param float      $original_price Original price.
 * @param float|null $sale_price Sale price.
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
 * Spin the article.
 *
 * @param int    $campaign_id The campaign ID.
 * @param string $content The content.
 *
 * @since 1.2.6
 * @return mixed
 */
function wpcp_spin_article( $campaign_id, $content ) {
	$args = apply_filters(
		'wpcp_spinwritter_request_args',
		array(
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
		)
	);

	if ( empty( $args['email_address'] ) || empty( $args['api_key'] ) ) {
		wpcp_logger()->error( __( 'spinwritter API details is not set, aborting article spinner', 'wp-content-pilot' ) );

		return $content;
	}

	set_time_limit( 150 );
	$curl     = wpcp_setup_request();
	$endpoint = 'http://www.spinrewriter.com/action/api';
	$curl->post( $endpoint, $args );
	if ( $curl->isError() ) {
		wpcp_logger()->error( __( 'Spinwritter could not send API request, aborting article spinner', 'wp-content-pilot' ) );

		return $content;
	}
	$response = json_decode( $curl->getResponse() );

	if ( isset( $response->status ) && 'ERROR' === $response->status ) {
		wpcp_logger()->error( sprintf( /* translators: ERROR message */ __( 'Aborting article spinner Because [%s]', 'wp-content-pilot' ), $response->response ) );

		return $content;
	}

	if ( isset( $response->status ) && 'OK' === $response->status && ! empty( $response->response ) ) {
		$pattern      = '#< iframe[^>]+>#is';
		$iframe_check = preg_match( $pattern, $response->response );
		$new_response = ( 1 === $iframe_check ) ? preg_replace( $pattern, '', $response->response ) : $response->response;

		return force_balance_tags( $new_response );
	}

	return $content;
}
