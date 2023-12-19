<?php

defined( 'ABSPATH' ) || exit();

/**
 * Convert a string to array.
 *
 * @param string $content The content.
 * @param string $separator Separator.
 * @param array  $callbacks Array of callback methods.
 *
 * @since 1.0.0
 * @return array
 */
function wpcp_string_to_array( $content, $separator = ',', $callbacks = array() ) {
	if ( is_array( $content ) ) {
		return $content;
	}
	$default   = array(
		'trim',
	);
	$callbacks = wp_parse_args( $callbacks, $default );
	$parts     = explode( $separator, $content );

	if ( ! empty( $callbacks ) ) {
		foreach ( $callbacks as $callback ) {
			$parts = array_map( $callback, $parts );
		}
	}

	return $parts;
}

/**
 * Return main part of the url eg exmaple.com  from https://www.example.com.
 *
 * @param string  $url Url string.
 * @param boolean $base_domain Weather true or false.
 *
 * @since 1.0.0
 * @return mixed
 */
function wpcp_get_host( $url, $base_domain = false ) {
	$parse_url = wp_parse_url( trim( esc_url_raw( $url ) ) );

	if ( $base_domain ) {
		$array = explode( '/', $parse_url['path'], 2 );
		$host  = trim( $parse_url['host'] ? $parse_url['host'] : array_shift( $array ) );
	} else {
		$scheme = ! isset( $parse_url['scheme'] ) ? 'http' : $parse_url['scheme'];

		return $scheme . '://' . $parse_url['host'];
	}

	return $host;
}

/**
 * Remove unauthorized HTML from content.
 *
 * @param string $content The content.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_remove_unauthorized_html( $content ) {
	$default_allowed_tags = array(
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
		),
	);

	$allowed_tags = apply_filters( 'wpcp_allowed_html_tags', $default_allowed_tags );
	$content      = wp_kses( $content, $allowed_tags );

	return trim( $content );
}

/**
 * Remove empty html tags.
 *
 * @param string $str String.
 * @param null   $replace_with Replace string.
 *
 * @since 1.0.0
 * @return null|string|string[]
 */
function wpcp_remove_empty_tags_recursive( $str, $replace_with = null ) {
	// Return if string not given or empty.
	if ( ! is_string( $str )
		|| trim( $str ) === '' ) {
		return $str;
	}

	// Recursive empty HTML tags.
	return preg_replace(
		// Pattern written by Junaid Atari.
		'/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',
		// Replace with nothing if string empty.
		! is_string( $replace_with ) ? '' : $replace_with,
		// Source string.
		$str
	);
}

/**
 * Clean emoji from content.
 *
 * @param string $content The content.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_remove_emoji( $content ) {
	$clean_text = '';

	// Match Emoticons.
	$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
	$clean_text      = preg_replace( $regex_emoticons, '', $content );

	// Match Miscellaneous Symbols and Pictographs.
	$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
	$clean_text    = preg_replace( $regex_symbols, '', $clean_text );

	// Match Transport And Map Symbols.
	$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
	$clean_text      = preg_replace( $regex_transport, '', $clean_text );

	// Match Miscellaneous Symbols.
	$regex_misc = '/[\x{2600}-\x{26FF}]/u';
	$clean_text = preg_replace( $regex_misc, '', $clean_text );

	// Match Dingbats.
	$regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
	$clean_text     = preg_replace( $regex_dingbats, '', $clean_text );

	return $clean_text;
}

/**
 * Clean title.
 *
 * @param string $title The title.
 *
 * @since 1.0.0
 * @return null|string|string[]
 */
function wpcp_clean_title( $title ) {

	$title = html_entity_decode( $title, ENT_QUOTES );

	$title = str_replace( 'nospin', '', $title );
	// $title = str_replace( ' ', '-', $title ); // Replaces all spaces with hyphens.
	// $title = preg_replace( '/[^A-Za-z0-9\-\s\.\,]/', '', $title ); // Removes special chars.
	// $title = preg_replace( '/[^A-Za-z0-9\-\s\'\"\.\,]/', '', $title ); // Removes special chars. allow öäüß ÖÄÜ

	$title = preg_replace( '/-+/', '-', $title ); // Replaces multiple hyphens with single one.

	$title = wp_trim_words( $title, 10, '' );

	if ( stristr( $title, '.' ) ) {
		$title_parts = explode( '.', $title );
		if ( str_word_count( $title_parts[0] ) > 3 ) {
			$title = $title_parts[0];
		}
	}

	if ( preg_match( '/ [\|\-\\\\\/>»] /i', $title ) ) {
		$title = preg_replace( '/(.*)[\|\-\\\\\/>»] .*/i', '$1', $title );
	}
	if ( count( preg_split( '/\s+/', $title ) ) < 3 ) {
		$title = preg_replace( '/[^\|\-\\\\\/>»]*[\|\-\\\\\/>»](.*)/i', '$1', $title );
	}

	return $title;
}

/**
 * Fix utf8.
 *
 * @param string $content The content.
 *
 * @since 1.2.0
 * @return string
 */
function wpcp_fix_utf8( $content ) {
	if ( 1 === preg_match( '/^./us', $content ) ) {
		return $content;
	}
	if ( function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8//IGNORE', $content );
	}

	return $content;
}

/**
 * Array to html.
 *
 * @param array $list_items Array of list items.
 *
 * @since 1.2.0
 * @return string
 */
function wpcp_array_to_html( $list_items ) {
	if ( ! is_array( $list_items ) || empty( $list_items ) ) {
		return '';
	}
	$html = '';
	foreach ( $list_items as $key => $item ) {
		$item_html = '';
		if ( ! is_numeric( $key ) ) {
			$item_html .= sprintf( '<strong>%s : </strong>', wp_strip_all_tags( $key ) );
		}

		if ( empty( $item ) ) {
			$item = '&mdash;';
		}

		$item_html .= $item;

		$html .= sprintf( '<li>%s</li>', wp_strip_all_tags( $item_html ) );
	}

	return sprintf( '<ul>%s</ul>', $html );
}

/**
 * The price.
 *
 * @param float  $amount The amount.
 * @param string $currency The currency.
 *
 * @since 1.2.0
 * @return string
 */
function wpcp_price( $amount, $currency = '$' ) {
	return sprintf( '%s%s', $currency, number_format_i18n( $amount, 2 ) );
}

/**
 * Remove continue reading text.
 *
 * @param string $content The content.
 *
 * @since 1.2.0
 * @return mixed
 */
function wpcp_remove_continue_reading( $content ) {
	$dom = wpcp_str_get_html( $content );
	/* @var object $node simple_html_dom_node. */
	foreach ( $dom->find( 'a' ) as $node ) {
		if ( in_array( strtolower( trim( $node->text() ) ), array( 'continue reading', 'read more' ), true ) ) {
			$node->remove();
		}
	}

	return $dom;
}

/**
 * Convert cents into usd.
 *
 * @param float $cent Cent.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_cent_to_usd( $cent ) {
	return number_format( ( $cent / 100 ), 2, '.', ' ' );
}

/**
 * Generate the title form the content.
 *
 * @param string $content The content.
 * @param int    $length The content length.
 *
 * @since 1.0.0
 * @return bool|null|string|string[]
 */
function wpcp_generate_title_from_content( $content, $length = 80 ) {
	$clean_content = wpcp_remove_emoji( wpcp_strip_urls( wp_strip_all_tags( $content ) ) );

	if ( function_exists( 'mb_substr' ) ) {
		$new_title = ( mb_substr( $clean_content, 0, $length ) );
	} else {
		$new_title = ( substr( $clean_content, 0, $length ) );
	}

	$new_title = preg_replace( '{RT @.*?: }', '', $new_title );

	return wpcp_clean_title( $new_title );
}

/**
 * Remove url from content.
 *
 * @param string $content The content.
 *
 * @since 1.0.0
 * @return string
 */
function wpcp_strip_urls( $content ) {
	return preg_replace( '{http[s]?://[^\s]*}', '', $content );
}

/**
 * Find numbers from string/content.
 *
 * wpcp_get_numbers_from_string('Renewed from $369.99')
 * wpcp_get_numbers_from_string('Showing 20 of 200', true)
 *
 * @param string $content The String.
 * @param bool   $multiple Weather true or false.
 *
 * @since 1.2.5
 * @return array|float|integer
 */
function wpcp_get_numbers_from_string( $content, $multiple = false ) {
	if ( ! $multiple ) {
		return preg_replace( '/[^\\d.]+/', '', $content );
	}
	preg_match_all( '!\d+!', $content, $matched );

	return isset( $matched[0] ) ? $matched[0] : array();
}
