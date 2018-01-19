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
