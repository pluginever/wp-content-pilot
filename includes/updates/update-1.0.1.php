<?php
/**
 * This migrates  the settings from old to new
 *
 * @since 1.0.1
 */
function wpcp_update_1_0_1() {
    $banned_host = get_option( 'wpcp_settings' );

    if ( ! empty( $banned_host['article_banned_host'] ) ) {
        $option = [
            'banned_hosts' => $banned_host['article_banned_host'],
        ];
        update_option( 'wpcp_settings_article', $option );
    }

}
