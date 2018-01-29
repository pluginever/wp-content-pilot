<?php

namespace Pluginever\WPCP\Core;

class Processor {
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
     * Processor constructor.
     */
    public function __construct( $campaign_id = null ) {
        add_action( 'wpcp_per_minute_scheduled_events', [ $this, 'run_automatic_campaign' ] );
    }

    public function run_automatic_campaign() {
        $args = array(
            'post_type'  => 'wp_content_pilot',
            'meta_key'   => '_last_run',
            'orderby'    => 'meta_value_num',
            'order'      => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => '_active',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
        );

        $query = new \WP_Query( $args );
        wp_reset_query();

        if ( ! $query->have_posts() ) {
            wpcp_log( 'dev', 'No campaign found in scheduled task' );

            return;
        }

        $campaigns = wp_list_pluck( $query->posts, 'ID' );

        $last_campaign = get_option( 'wpcp_last_campaign', '' );

        if ( ! empty( $last_campaign ) && count( $campaigns ) > 1 ) {
            unset( $campaigns[ $last_campaign ] );
        }

        foreach ( $campaigns as $campaign_id ) {
            $last_run     = get_post_meta( $campaign_id, '_last_run', true );
            $frequency    = wpcp_get_post_meta( $campaign_id, '_frequency', 0 );
            $target       = wpcp_get_post_meta( $campaign_id, '_target', 0 );
            $posted       = wpcp_get_post_meta( $campaign_id, '_total_posted', 0 );
            $current_time = current_time( 'timestamp' );
            $diff         = $current_time - $last_run;
            if ( $diff < $frequency ) {
                continue;
            }

            if ( $posted >= $target ) {
                wpcp_disable_campaign( $campaign_id );
                continue;
            }

            $campaign = $this->run_campaign( $campaign_id );

            if ( is_wp_error( $campaign ) ) {
                wpcp_disable_campaign( $campaign_id );
                wpcp_log( 'critical', sprintf( __( 'Campaign has been disabled Reason %s', 'erp' ), $campaign->get_error_message() ) );
            }


        }

    }


    public function run_campaign( $campaign_id ) {
        $this->campaign_id = $campaign_id;
        $loaded            = $this->load( $campaign_id );

        if ( is_wp_error( $loaded ) ) {
            return $loaded;
        }

        $campaign = new Campaign( $campaign_id, $this->campaign_type, $this->keyword, $this->campaign_title );
        $result = $campaign->run();
        if ( is_wp_error( $result ) ) {
            wpcp_disable_campaign( $campaign_id );
            wpcp_log( 'critical', sprintf( __( 'Campaign has been disabled Reason %s', 'erp' ), $result->get_error_message() ) );
        } else {
            $posted = wpcp_get_post_meta( $campaign_id, '_total_posted', 0 );
            update_post_meta( $this->campaign_id, '_total_posted', ( intval( $posted ) + 1 ) );
        }
        update_post_meta( $this->campaign_id, '_last_keyword', $this->keyword );
        update_post_meta( $this->campaign_id, '_last_run', current_time( 'timestamp' ) );
        update_option( 'wpcp_last_campaign', $this->campaign_id );
    }

    /**
     * Load campaign
     *
     * @since 1.0.0
     *
     * @param $campaign_id
     *
     * @return \WP_Error
     */
    public function load( $campaign_id ) {
        if ( 'publish' !== get_post_status( $campaign_id ) ) {
            $msg = __( 'Campaign is removed or post is not publish', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'campaign-post-invalid', $msg );
        }

        if ( '1' !== get_post_meta( $campaign_id, '_active', true ) ) {
            $msg = __( 'Campaign is not active this wont run', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'campaign-post-invalid', $msg );
        }

        $this->campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );

        $this->campaign_title = get_the_title( $campaign_id );

        $keyword = $this->setup_keyword();

        if ( ! $keyword ) {
            $msg = __( 'No valid keyword/feed links found', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-campaign-keyword', $msg );
        }

        $this->keyword = $keyword;

    }

    /**
     * Select keyword for the campaign
     *
     * @since 1.0.0
     *
     * @return string|boolean
     */
    protected function setup_keyword() {
        if ( $this->campaign_type == 'feeds' ) {
            $meta = get_post_meta( $this->campaign_id, '_feed_links', true );
        } else {
            $meta = get_post_meta( $this->campaign_id, '_keywords', true );
        }

        $keywords = (array) wpcp_string_to_array( $meta, PHP_EOL, array( 'trim' ) );
        if ( empty( $keywords ) ) {
            return false;
        }

        $last_keyword = get_post_meta( $this->campaign_id, '_last_keyword', true );

        if ( ! empty( $last_keyword ) && count( $keywords ) > 1 ) {
            if ( ( $key = array_search( $last_keyword, $keywords ) ) !== false ) {
                unset( $keywords[ $key ] );
            }
        }

        $keyword_key      = array_rand( $keywords, 1 );
        $selected_keyword = $keywords[ $keyword_key ];

        return apply_filters( 'wpcp_campaign_selected_keyword', $selected_keyword, $this->campaign_id, $this->campaign_type );
    }


}
