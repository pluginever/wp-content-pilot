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
 * Convert a string to array
 *
 * @since 1.0.0
 *
 * @param        $string
 * @param string $separator
 * @param array  $callbacks
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


/**
 * Convert relative URL to absolute URL
 * since 1.0.0
 *
 * @param $rel_url
 * @param $host
 *
 * @return string
 */
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
 * Fix broken links from HTML
 * since 1.0.0
 *
 * @param $html
 * @param $host
 *
 * @return mixed
 */
function wpcp_fix_html_links( $html, $host ) {
	preg_match_all( '/(href|src)="([^"]*)"/i', $html, $matched );
	if ( ! empty( $matches[1] ) ) {
		foreach ( $matches[1] as $src ) {
			if ( filter_var( $src, FILTER_VALIDATE_URL ) ) {
				continue;
			}

			//fix url with appending
			$new_src = wpcp_convert_rel_2_abs_url( $src, $host );
			$request = wp_remote_head( $new_src );
			$type    = wp_remote_retrieve_header( $request, 'content-type' );

			if ( ! $type ) {
				continue;
			}

			$html = str_replace( $src, $new_src, $html );
		}
	}

	return $html;
}

/**
 * get all image tags
 *
 *
 * @since 1.0.0
 *
 * @param $content
 *
 * @return array
 */
function wpcp_get_all_image_urls( $content ) {
	preg_match_all( '/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches );
	if ( ! empty( $matches[1] ) ) {
		return $matches[1];
	}

	return array();
}


/**
 * @since 1.0.
 *
 * @param $content
 *
 * @return string
 *
 */
function wpcp_remove_unauthorized_html( $content ) {
	$default_allowed_tags = [
		'a'          => array(
			'href'   => true,
			'target' => true,
		),
		'audio'      => array(
			'autoplay' => true,
			'controls' => true,
			'loop'     => true,
			'muted'    => true,
			'preload'  => true,
			'src'      => true,
		),
		'b'          => array(),
		'blockquote' => array(
			'cite'     => true,
			'lang'     => true,
			'xml:lang' => true,
		),
		'br'         => array(),
		'button'     => array(
			'disabled' => true,
			'name'     => true,
			'type'     => true,
			'value'    => true,
		),
		'em'         => array(),
		'h2'         => array(
			'align' => true,
		),
		'h3'         => array(
			'align' => true,
		),
		'h4'         => array(
			'align' => true,
		),
		'h5'         => array(
			'align' => true,
		),
		'h6'         => array(
			'align' => true,
		),
		'i'          => array(),
		'img'        => array(
			'alt'    => true,
			'align'  => true,
			'height' => true,
			'src'    => true,
			'width'  => true,
		),
		'p'          => array(
			'xml:lang' => true,
		),
		'table'      => array(),
		'tbody'      => array(),
		'td'         => array(),
		'tfoot'      => array(),
		'th'         => array(),
		'thead'      => array(),
		'tr'         => array(),
		'u'          => array(),
		'ul'         => array(
			'type' => true,
		),
		'ol'         => array(
			'start'    => true,
			'type'     => true,
			'reversed' => true,
		),
		'li'         => array(),
		'iframe'     => array(
			'frameborder' => true,
			'height'      => true,
			'width'       => true,
			'src'         => true,
		)
	];

	$allowed_tags = apply_filters( 'wpcp_allowed_html_tags', $default_allowed_tags );

	return wp_kses( $content, $allowed_tags );
}

/**
 * remove empty html tags
 *
 * since 1.0.0
 *
 * @param      $str
 * @param null $replace_with
 *
 * @return null|string|string[]
 */
function wpcp_remove_empty_tags_recursive( $str, $replace_with = null ) {
	//** Return if string not given or empty.
	if ( ! is_string( $str )
	     || trim( $str ) == '' ) {
		return $str;
	}

	//** Recursive empty HTML tags.
	return preg_replace(

	//** Pattern written by Junaid Atari.
		'/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',

		//** Replace with nothing if string empty.
		! is_string( $replace_with ) ? '' : $replace_with,

		//** Source string
		$str
	);
}

/**
 * Remove url from content
 * since 1.0.0
 * @param $content
 *
 * @return string
 */
function wpcp_strip_urls($content){
	return preg_replace('{http[s]?://[^\s]*}', '', $content);
}
