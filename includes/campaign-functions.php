<?php
/**
 * All the functions related to campaign
 */


/**
 * Mark campaign as disabled
 *
 * @param $camp_id
 */
function wpcp_disable_campaign( $camp_id ) {
    update_post_meta( $camp_id, '_wpcp_active', 0 );
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
        $options[ $time ] = sprintf( _n( '%s Hour', '%s Hours', $i, 'wpcp' ), $i );;
    }


    return apply_filters( 'wpcp_get_campaign_schedule_options', $options );
}
