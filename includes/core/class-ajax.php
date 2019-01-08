<?php

namespace Pluginever\WPCP\Core;

use Pluginever\WPCP\Traits\Hooker;

class Ajax {
    use Hooker;

    public function __construct() {
        $this->action( 'wp_ajax_wpcp_get_template_tags', 'get_template_tags' );
        $this->action( 'wp_ajax_wpcp_run_test_campaign', 'run_test_campaign' );
    }

    public function get_template_tags() {
        if ( empty( $_REQUEST['data']['type'] ) ) {
            return;
        }
        $campaign_type = esc_attr( $_REQUEST['data']['type'] );

        $tags = wpcp_get_module_supported_tags( $campaign_type );

        wp_send_json_success( $tags );
    }


    public function run_test_campaign() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp-content-pilot' ) ) {
            wp_send_json_error( [ 'message' => __( 'No Cheating!', 'wp-content-pilot' ) ] );
        }

        if ( empty( $_POST['campaign_id'] ) ) {
            wp_send_json_error( [ 'message' => __( 'No campaign id in request. Please try again.', 'wp-content-pilot' ) ] );
        }

        $campaign_id = intval( $_POST['campaign_id'] );
        $post_id      = wpcp_run_campaign( $campaign_id );

        if( is_wp_error( $post_id )){
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }

        $title = get_the_title( $post_id );
        $permalink = get_the_permalink($post_id);
        $keyword = wp_content_pilot()->keyword;
        wp_send_json_success( [
            'message' => __(sprintf('Campaign was successfully posted the article "%s" for the keyword: %s. Do you want to visit the article? ', $title, $keyword), 'wp-content-pilot'),
            'permalink' => $permalink,
        ] );
    }
}
