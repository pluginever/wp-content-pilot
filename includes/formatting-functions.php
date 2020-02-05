<?php
defined( 'ABSPATH' ) || exit();


/**
 * Convert a string to array
 *
 * @param        $string
 * @param string $separator
 * @param array $callbacks
 *
 * @return array
 * @since 1.0.0
 *
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
 * @param $content
 *
 * @return string
 *
 * @since 1.0.
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
			'class'    => true,
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
			'alt'      => true,
			'align'    => true,
			'height'   => true,
			'src'      => true,
			'width'    => true,
			'data-src' => true,
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
 * clean emoji from content
 * since 1.0.0
 *
 * @param $content
 *
 * @return string
 */
function wpcp_remove_emoji( $content ) {
	$clean_text = "";

	// Match Emoticons
	$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
	$clean_text     = preg_replace( $regexEmoticons, '', $content );

	// Match Miscellaneous Symbols and Pictographs
	$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
	$clean_text   = preg_replace( $regexSymbols, '', $clean_text );

	// Match Transport And Map Symbols
	$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
	$clean_text     = preg_replace( $regexTransport, '', $clean_text );

	// Match Miscellaneous Symbols
	$regexMisc  = '/[\x{2600}-\x{26FF}]/u';
	$clean_text = preg_replace( $regexMisc, '', $clean_text );

	// Match Dingbats
	$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
	$clean_text    = preg_replace( $regexDingbats, '', $clean_text );

	return $clean_text;
}


/**
 * clean title
 *
 * since 1.0.0
 *
 * @param $title
 *
 * @return null|string|string[]
 */
function wpcp_clean_title( $title ) {
	$title = str_replace( 'nospin', '', $title );
	//$title = str_replace( ' ', '-', $title ); // Replaces all spaces with hyphens.
	$title = preg_replace( '/[^A-Za-z0-9\-\s\.\,]/', '', $title ); // Removes special chars.

	return preg_replace( '/-+/', '-', $title ); // Replaces multiple hyphens with single one.
}

/**
 * Fix utf8
 *
 * @param $content
 *
 * @return string
 * @since 1.2.0
 */
function wpcp_fix_utf8( $content ) {
	if ( 1 === @preg_match( '/^./us', $content ) ) {
		return $content;
	}
	if ( function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8//IGNORE', $content );
	}

	return $content;
}

/**
 * @param $list
 *
 * @return string
 * @since 1.2.0
 */
function wpcp_array_to_html( $list ) {
	if ( ! is_array( $list ) || empty( $list ) ) {
		return '';
	}
	$html = '';
	foreach ( $list as $key => $item ) {
		$item_html = '';
		if ( ! is_numeric( $key ) ) {
			$item_html .= sprintf( '<strong>%s : </strong>', strip_tags( $key ) );
		}

		if ( empty( $item ) ) {
			$item = '&mdash;';
		}

		$item_html .= $item;

		$html .= sprintf( '<li>%s</li>', strip_tags( $item_html) );
	}

	return sprintf( '<ul>%s</ul>', $html );
}

/**
 * @since 1.2.0
 * @param $amount
 * @param string $currency
 *
 * @return string
 */
function wpcp_price( $amount, $currency = '$' ) {
	return sprintf( '%s%s', $currency, number_format_i18n( $amount, 2 ) );
}

/**
 * @since 1.2.0
 * @param $content
 *
 * @return mixed
 */
function wpcp_remove_continue_reading($content){
	$dom = wpcp_str_get_html( $content );
	/* @var $node simple_html_dom_node */
	foreach ($dom->find( 'a') as $node){
		if(in_array( strtolower( trim( $node->text())), ['continue reading', 'read more'])){
			$node->remove();
		}
	}

	return $dom;
}
