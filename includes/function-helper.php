<?php
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
function wpcp_setup_request( $campaign_type, $url = null, $campaign_id ) {
    $curl = new \Curl\Curl( $url );
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

function wpcp_convert_youtube_duration( $youtube_time ) {
    preg_match_all( '/(\d+)/', $youtube_time, $parts );

    // Put in zeros if we have less than 3 numbers.
    if ( count( $parts[0] ) == 1 ) {
        array_unshift( $parts[0], "0", "0" );
    } elseif ( count( $parts[0] ) == 2 ) {
        array_unshift( $parts[0], "0" );
    }

    $sec_init         = $parts[0][2];
    $seconds          = $sec_init % 60;
    $seconds_overflow = floor( $sec_init / 60 );

    $min_init         = $parts[0][1] + $seconds_overflow;
    $minutes          = ( $min_init ) % 60;
    $minutes_overflow = floor( ( $min_init ) / 60 );

    $hours = $parts[0][0] + $minutes_overflow;

    if ( $hours != 0 ) {
        return $hours . ':' . $minutes . ':' . $seconds;
    } else {
        return $minutes . ':' . $seconds;
    }
}
