<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpcp_run_campaign( $campaign_id, $force = false ) {
    $can_run = wpcp_campaign_can_run( $campaign_id );
    if ( is_wp_error( $can_run ) ) {
        wpcp_log( 'critical', $can_run->get_error_message() );

        return $can_run;
    }

    if( $force and !defined('WPCP_FORCE_CAMPAIGN')){
        define('WPCP_FORCE_CAMPAIGN', true );
    }

    $keyword       = wpcp_setup_keyword( $campaign_id );
    $campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );
    $campaign      = new \Pluginever\WPCP\Core\Campaign( $campaign_id, $campaign_type, $keyword );
    $result        = $campaign->run();
    if ( is_wp_error( $result ) ) {
        return $result;
    }

    wpcp_log( 'log', __( "Post Insertion was success Post ID: {x}", 'wp-content-pilot' ) );

    return $result;
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

    if ( $campaign_type == 'feed' ) {
        $keywords = get_post_meta( $campaign_id, '_feed_links', true );
    } else {
        $keywords = get_post_meta( $campaign_id, '_keywords', true );
    }

    if ( empty( trim( $keywords ) ) ) {
        $keywords = __( 'Keywords', 'wp-content-pilot' );
        if ( $campaign_type == 'feed' ) {
            $keywords = __( 'Feed links', 'wp-content-pilot' );
        }

        return new \WP_Error( 'campaign-' . $keywords . '-invalid', __( "Campaign {$keywords} is not set. Campaign won't run", 'wp-content-pilot' ) );
    }

    return true;
}

/**
 * Select keyword for the campaign
 *
 * @since 1.0.0
 *
 * @return string|boolean
 */
function wpcp_setup_keyword( $campaign_id ) {
    $campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );

    if ( $campaign_type == 'feed' ) {
        $meta = get_post_meta( $campaign_id, '_feed_links', true );

        $last_keyword = get_post_meta( $campaign_id, '_last_keyword', true );

        $keywords = (array) wpcp_string_to_array( $meta, ',', array( 'trim' ) );

        if ( empty( $keywords ) ) {
            return false;
        }

        if ( ! empty( $last_keyword ) && count( $keywords ) > 1 ) {
            if ( ( $key = array_search( $last_keyword, $keywords ) ) !== false ) {
                unset( $keywords[ $key ] );
            }
        }

        $keyword_key      = array_rand( $keywords, 1 );
        $selected_keyword = $keywords[ $keyword_key ];


    } else {
        $meta = get_post_meta( $campaign_id, '_keywords', true );

        $keywords_type = wpcp_get_post_meta( $campaign_id, '_keywords_type', 'exact' );

        if( $keywords_type == 'exact'){
            $selected_keyword = "\" $meta \"";
        }else{

            $keywords = (array) wpcp_string_to_array( $meta, ',', array( 'trim' ) );
            if ( empty( $keywords ) ) {
                return false;
            }

            $selected_keyword = implode(' OR ', $keywords);

        }

    }


    return apply_filters( 'wpcp_campaign_selected_keyword', $selected_keyword, $campaign_id, $campaign_type );
}

/**
 * Logger for the plugin
 *
 * @since    1.0.0
 *
 * @param  $log_level
 * dev - when the development
 * log - normal log
 * critical - error/failed
 *
 * @param  $message
 *
 * @return  string
 */
function wpcp_log( $log_level = "log", $message ) {
    $log_level = strtolower( $log_level );

    if ( is_array( $message ) || is_object( $message ) ) {
        $message = print_r( $message, true );
    }

    if ( ! defined( 'WPCP_LOG_FILE' ) ) {
        define( 'WPCP_LOG_FILE', WP_CONTENT_DIR . '/debug.log' );
    }

    if ( in_array( $log_level, array( 'log', 'critical' ) ) ) {
        $camp_id = isset( wp_content_pilot()->campaign_id ) ? wp_content_pilot()->campaign_id : null;
        $keyword = isset( wp_content_pilot()->keyword ) ? wp_content_pilot()->keyword : null;
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
 * Insert new link in wpcp_links table
 *
 * @param array $data
 *
 * @return false|int
 */
function wpcp_insert_link( array $data ) {

    $data = wp_parse_args( $data, [
        'camp_id'    => null,
        'url'        => '',
        'keyword'    => '',
        'identifier' => null,
        'camp_type'  => '',
        'status'     => 0,
    ] );

    global $wpdb;
    $table = $wpdb->prefix . 'wpcp_links';

    $sql   = $wpdb->prepare( "SELECT id FROM {$table} where url = '%s';", $data['url'] );
    $exist = $wpdb->get_results( $sql );

    if ( ! empty( $exist ) ) {
        return false;
    }

    //$wpdb->show_errors();
    $id = $wpdb->insert(
        $table,
        $data
    );

    return $id;
}

/**
 * Update link in wpcp_links table
 *
 * @param null $id
 * @param array $data
 *
 * @return false|int|null
 */
function wpcp_update_link( $id = null, array $data ) {
    global $wpdb;
    $table = $wpdb->prefix . 'wpcp_links';

    $id = $wpdb->update(
        $table,
        $data,
        [ 'id' => $id ]
    );

    return $id;
}

/**
 * Get meta value
 *
 * @since 1.0.0
 *
 * @param      $post_id
 * @param      $meta_name
 * @param null $default
 *
 * @return mixed|null
 */
function wpcp_get_post_meta( $post_id, $meta_name, $default = null ) {
    $meta_value = get_post_meta( esc_attr( $post_id ), esc_attr( $meta_name ), true );

    if ( empty( $meta_value ) && null !== $default ) {
        return $default;
    }

    return $meta_value;
}

/**
 * Get plugin settings
 *
 * @since 1.0.0
 * @since 1.0.1 section has been added
 *
 * @param $section
 * @param        $field
 * @param bool $default
 *
 * @return string|array|bool
 */
function wpcp_get_settings( $field, $section = 'wpcp_settings', $default = false ) {
    $settings = get_option( $section );

    if ( isset( $settings[ $field ] ) && ! empty( $settings[ $field ] ) ) {
        return is_array($settings[ $field ]) ? array_map('trim', $settings[ $field ]): trim( $settings[ $field ] );
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
 * parse date time
 *
 * @since 1.0.0
 *
 * @param $date_time
 * @param bool $date
 * @param bool $time
 *
 * @return string
 *
 */
function wpcp_parse_date_time( $date_time, $date = true, $time = true, $timestamp = false ) {
    $date_time_format = "";
    if ( $date ) {
        $date_format      = get_option( 'date_format' );
        $date_time_format .= $date_format;
    }

    if ( $time ) {
        $time_format      = get_option( 'time_format' );
        $date_time_format .= ' ' . $time_format;
    }

    if ( $timestamp == false ) {
        $date_time = strtotime( $date_time );
    }

    return date( $date_time_format, $date_time );
}

/**
 * get all the modules
 *
 * @since 1.0.0
 *
 * @return mixed
 *
 */
function wpcp_get_modules() {
    $wpcp = wp_content_pilot();

    return $wpcp->modules->get_modules();
}

/**
 * Set up requst
 * @since 1.0.0
 *
 * @param $campaign_type
 * @param null $url
 *
 * @return \Curl\Curl
 *
 */
function wpcp_setup_request( $campaign_type, $url = null, $campaign_id= null) {
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
 * Get the links from a html documents
 *
 * @since 1.0.0
 *
 * @param $html
 *
 * @return array|\WP_Error
 */
function wpcp_get_html_links( $html ) {
    //b_results
    $dom = new \PHPHtmlParser\Dom();
    $dom->setOptions( [
        'enforceEncoding' => true,
        'cleanupInput'    => true,
    ] );
    $dom->load( $html );

    $links = $dom->find( '#b_results a' );

    if ( empty( $links ) ) {
        return new WP_Error( 'no-links-found', __( 'Could not retrieve any links', 'content-pilot' ) );
    }

    $links          = apply_filters( 'content_pilot_search_links', $links );
    $accepted_links = array();
    foreach ( $links as $link ) {
        $a = $link->getAttribute( 'href' );

        if ( wp_http_validate_url( $a ) ) {
            $accepted_links[] = $a;
        }
    }

    return $accepted_links;
}


/**
 * Convert a string to array
 *
 * @since 1.0.0
 *
 * @param $string
 * @param string $separator
 * @param array $callbacks
 *
 * @return array
 */
function wpcp_string_to_array( $string, $separator = ',', $callbacks = array() ) {
    $default   = array(
        'trim',
    );
    $callbacks = wp_parse_args( $callbacks, $default );
    $parts     = explode( $separator, $string );

    if ( ! empty( $callbacks ) ) {
        foreach ( $callbacks as $callback ) {
            $parts = array_map( $callback, $parts );
        }
    }

    return $parts;
}


/**
 * Mark campaign as disabled
 *
 * @param $camp_id
 */
function wpcp_disable_campaign( $camp_id ) {
    do_action( 'wpcp_disable_campaign', $camp_id );
    update_post_meta( $camp_id, '_active', 0 );
}

/**
 * Disable any keyword
 *
 * @since 1.0.0
 *
 * @param $keyword
 * @param string $meta_value
 */
function wpcp_disable_keyword( $campaign_id, $keyword, $meta_value = 'keywords' ) {
    do_action( 'wpcp_disable_keyword', $campaign_id, $keyword );

    $keywords_string = wpcp_get_post_meta( $campaign_id, '_wpcp_keywords', '' );
    $parts           = wpcp_string_to_array( $keywords_string, PHP_EOL, array( 'trim' ) );
    $key             = array_search( $keyword, $parts );

    if ( $key !== false ) {
        unset( $parts[ $key ] );
    }

    update_post_meta( $campaign_id, $meta_value, implode( PHP_EOL, $parts ) );

    $disabled_keywords   = (array) wpcp_get_post_meta( $campaign_id, '_wpcp_disabled_keywords', '' );
    $disabled_keywords[] = $keyword;
    $disabled_keywords   = array_filter( $disabled_keywords );
    update_post_meta( $campaign_id, '_wpcp_disabled_keywords', $disabled_keywords );

}

/**
 * Sanitize links from string
 * @since 1.0.0
 *
 * @param $string_links
 *
 * @return string
 *
 */
function wpcp_sanitize_feed_links( $string_links ) {
    $links           = explode( PHP_EOL, $string_links );
    $sanitized_links = [];

    foreach ( $links as $link ) {
        $sl = trim( $link );
        if ( filter_var( $link, FILTER_VALIDATE_URL ) === false ) {
            continue;
        }

        $sanitized_links[] = $sl;
    }

    return implode( PHP_EOL, $sanitized_links );
}

/**
 * sanitize keywords
 * @since 1.0.0
 *
 * @param $string_keywords
 *
 * @return string
 *
 */
function wpcp_sanitize_keywords( $string_keywords ) {
    $words = explode( ',', $string_keywords );
    $words = array_map( 'trim', $words );
    $words = array_map( 'sanitize_text_field', $words );

    return implode( ',', $words );
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
 * Get list of supported post types
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_get_post_types() {

    $supported_post_types = array(
        'post' => 'Post',
        'page' => 'Page',
    );

    return apply_filters( 'wpcp_get_post_types', $supported_post_types );
}

/**
 * Get post categories
 *
 * @since 1.0.3
 * @return array
 */
function wpcp_get_post_categories() {

    $args = [
        'taxonomy' => 'category',
        'hide_empty' => false
    ];

    $categories = get_terms( $args );

    return wp_list_pluck( $categories, 'name', 'term_id' );
}

/**
 * Campaaign schedule options
 * @since 1.0.0
 *
 * @return array
 *
 */
function wpcp_get_campaign_schedule_options() {
    $options = [];
    for ( $i = 1; $i <= 24; $i ++ ) {
        $time             = $i * HOUR_IN_SECONDS;
        $options[ $time ] = sprintf( _n( '%s Hour', '%s Hours', $i, 'wp-content-pilot' ), $i );;
    }


    return apply_filters( 'wpcp_get_campaign_schedule_options', $options );
}

/**
 * Return main part of the url eg exmaple.com  from https://www.example.com
 *
 * @param $url
 *
 * @return mixed
 */
function wpcp_get_host( $url, $base_domain = false ) {
    $parseUrl = parse_url( trim( esc_url_raw( $url ) ) );

    if ( $base_domain ) {
        $host = trim( $parseUrl['host'] ? $parseUrl['host'] : array_shift( explode( '/', $parseUrl['path'], 2 ) ) );
    } else {
        $scheme = ! isset( $parseUrl['scheme'] ) ? 'http' : $parseUrl['scheme'];

        return $scheme . "://" . $parseUrl['host'];
    }

    return $host;
}


function wpcp_convert_rel_2_abs_url( $rel_url, $host ) {
    //return if already absolute URL
    if ( parse_url( $rel_url, PHP_URL_SCHEME ) != '' ) {
        return $rel_url;
    }

    $default    = [
        'scheme' => 'http',
        'host'   => '',
        'path'   => '',
    ];
    $host_parts = wp_parse_args( parse_url( $host ), $default );

    //queries and anchors
    if ( $rel_url[0] == '#' || $rel_url[0] == '?' ) {
        return $host . $rel_url;
    }

    //remove non-directory element from path
    $path = preg_replace( '#/[^/]*$#', '', $host_parts['path'] );

    //destroy path if relative url points to root
    if ( $rel_url[0] == '/' ) {
        $path = '';
    }

    //dirty absolute URL
    $abs = "{$host_parts['host']}$path/$rel_url";

    //replace '//' or '/./' or '/foo/../' with '/'
    $re = array( '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' );
    for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, - 1, $n ) ) {
    }

    //absolute URL is ready!
    return $host_parts['scheme'] . '://' . $abs;
}


/**
 * Upload Image
 *
 * @param $url
 *
 * @return bool|int|WP_Error
 */
function wpcp_upload_image( $url ) {
    $url = explode( '?', esc_url_raw( $url ) );
    $url = $url[0];
    $get     = wp_remote_get( $url );
    $headers = wp_remote_retrieve_headers( $get );
    $type    = isset( $headers['content-type'] ) ? $headers['content-type'] : null;
    wpcp_log( 'dev', $type );
    if ( is_wp_error( $get ) || ! isset( $type ) || ( ! in_array( $type, [ 'image/png', 'image/jpeg' ] ) ) ) {
        wpcp_log( 'dev', "failed $url" );

        return false;
    }

    $mirror = wp_upload_bits( basename( $url ), '', wp_remote_retrieve_body( $get ) );
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
 *
 * @since 1.0.0
 *
 * @param $image_url
 * @param $post_id
 *
 * @return bool|int|WP_Error
 */
function wpcp_set_featured_image_from_link( $image_url, $post_id ) {
    $attach_id = wpcp_upload_image( $image_url );
    if ( $attach_id ) {
        update_post_meta( $post_id, '_thumbnail_id', $attach_id );

        return $attach_id;
    }

    return false;

}

/**
 * Search for template tags from the supplied contents
 * and replace by contents
 *
 * @since 1.0.0
 * @since 1.0.1
 * $skips array is added to skip replacing any template tags
 *
 * @param $template
 * @param $article
 * @param $campaign_id
 * @param $keyword
 * @param $skips
 *
 * @return mixed
 *
 */
function wpcp_parse_template_tags( $template, $article, $campaign_id, $skips = ['content', 'title'] ) {
    wpcp_log('dev', 'wpcp_parse_template_tags');
    $cache_key     = md5( "wpcp_template_tags_{$campaign_id}" );
    $template_tags = wp_cache_get( $cache_key );
    if ( false == $template_tags ) {
        wpcp_log( 'dev', 'have not cached' );
        wpcp_log( 'dev', 'Skipping tags ' );
        wpcp_log( 'dev', $skips );
        unset( $article['link'] );
        $template_tags = [];
        foreach ( $article as $tag => $content ) {

            if( in_array($tag, $skips)){
                continue;
            }
            $template_tags[ '{' . $tag . '}' ] = is_array( $content ) ? serialize( $content ) : $content;
        }

        $template_tags = apply_filters( 'wpcp_parse_template_tags', $template_tags, $article, $campaign_id );
        wp_cache_set( $cache_key, $template_tags );
    }

    $tags     = array_keys( $template_tags );
    $contents = array_values( $template_tags );

    wpcp_log('dev', '-------Tags to replace------');
    wpcp_log('dev', $tags);
    return str_replace( $tags, $contents, $template );
}


function wpcp_get_module_supported_tags( $module ) {
    $modules = wpcp_get_modules();
    if ( isset( $modules[ $module ] ) ) {
        $selected = $modules[ $module ];

        return $selected['supports'];
    }

    return [];
}
