<?php

namespace Pluginever\WPCP\Core;

use Pluginever\WPCP\Traits\Hooker;

class Ajax {
    use Hooker;

    public function __construct() {
        $this->action( 'wp_ajax_wpcp_get_template_tags', 'get_template_tags' );
    }

    public function get_template_tags() {
        if ( empty( $_REQUEST['data']['type'] ) ) {
            return;
        }
        $campaign_type = esc_attr( $_REQUEST['data']['type'] );

        $tags = wpcp_get_module_supported_tags($campaign_type);

        wp_send_json_success($tags);
    }
}
