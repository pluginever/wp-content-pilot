<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;
use Pluginever\WPCP\Traits\Hooker;

class Vimeo extends Item {
    protected $token;
    use Hooker;

    function setup() {
        $token = wpcp_get_settings( 'vimeo_token', '' );

        if ( empty( $token ) ) {
            $msg = __( 'Vimeo token is not set. Please configure vimeo settings.', 'wp-content-pilot' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-vimeo-settings', $msg );
        }

        $this->token = $token;

        $this->filter( 'content_pilot_setup_request', 'add_token', 10, 2 );
    }

    function fetch_links() {
        // TODO: Implement fetch_links() method.
    }

    function fetch_post( $link ) {
        // TODO: Implement fetch_post() method.
    }


    public function add_token( $curl, $campaign_id ) {
        if ( $campaign_id !== $this->campaign_id ) {
            return;
        }
        $config['headers']['Authorization'] = 'bearer ' . trim( $this->token );

        return $config;
    }

}
