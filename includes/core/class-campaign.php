<?php

namespace Pluginever\WPCP\Core;

class Campaign {
    /**
     * @var int
     */
    protected $campaign_id;

    /**
     * @var string
     */
    protected $campaign_type;

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $campaign_title;

    /**
     * Campaign constructor.
     *
     * @param int $campaign_id
     * @param string $campaign_type
     * @param string $keyword
     * @param string $campaign_title
     */
    public function __construct( $campaign_id, $campaign_type, $keyword, $campaign_title ) {
        $this->campaign_id    = $campaign_id;
        $this->campaign_type  = $campaign_type;
        $this->keyword        = $keyword;
        $this->campaign_title = $campaign_title;
    }


    /**
     * @since 1.0.0
     *
     */
    public function run() {
        //validate all property

        do_action( 'wpcp_before_running_campaign', $this->campaign_id );
        $wpcp = wp_content_pilot();

        $module = $wpcp->modules->get_module( $this->campaign_type );
        if ( ! $module ) {
            $msg = __( 'Could not find the module for the campaign type', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-campaign-type', $msg );
        }

        //get the module callback
        $module_class = $module['callback'];

        //module instance
        $instance              = new $module_class;

        //set the parameters
        $instance->campaign_id = $this->campaign_id;
        $instance->keyword     = $this->keyword;

        //set the module
        $is_error = $instance->setup();

        //check error
        if ( is_wp_error( $is_error ) ) {
            return $is_error;
        }

        //run the module
        $post                  = $instance->run();
        if ( is_wp_error( $post ) ) {
            return $post;
        }


        $inserted = $this->insert_post( $post );

        if ( is_wp_error( $inserted ) ) {
            return $inserted;
        }


        return $this->campaign_id;
    }

    protected function insert_post( $post ) {
        var_dump($post);
    }
}
