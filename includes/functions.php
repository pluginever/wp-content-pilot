<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Logger for the plugin
 *
 * @since    1.0.0
 *
 * @param  $log_level
 * @param  $message
 *
 * @return  string
 */
function wpcp_log( $log_level = "log", $message ) {

    if ( WP_DEBUG !== true ) {
        return;
    }
    if ( is_array( $message ) || is_object( $message ) ) {
        $message = print_r( $message, true );
    }

    if ( ! defined( 'WPCP_LOG_FILE' ) ) {
        define( 'WPCP_LOG_FILE', WP_CONTENT_DIR . '/debug.log' );
    }

    if ( ! file_exists( WPCP_LOG_FILE ) ) {
        @touch( WPCP_LOG_FILE );
    }

    return error_log( date( "Y-m-d\tH:i:s" ) . "\t\t" . ucwords( $log_level ) . "\t\t" . strip_tags( $message ) . "\n", 3, WPCP_LOG_FILE );
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

    $wpdb->show_errors();
    $id = $wpdb->insert(
        $table,
        $data
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
 * @since 1.0.0
 *
 * @param        $field
 * @param bool $default
 *
 * @return bool|string
 */
function wpcp_get_settings( $field, $default = false ) {
    $settings = get_option( 'wpcp_settings' );
    if ( isset( $settings[ $field ] ) && ! empty( $settings[ $field ] ) ) {
        return trim( $settings[ $field ] );
    }

    return $default;
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
function wpcp_get_modules(){
    $wpcp = wp_content_pilot();
    return $wpcp->modules->get_modules();
}
