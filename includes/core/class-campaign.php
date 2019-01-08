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
     */
    public function __construct( $campaign_id, $campaign_type, $keyword ) {
        $this->campaign_id    = $campaign_id;
        $this->campaign_type  = $campaign_type;
        $this->keyword        = $keyword;
        $this->campaign_title = get_the_title( $campaign_id );

        //set globally
        $wpcp                 = wp_content_pilot();
        $wpcp->campaign_id    = $this->campaign_id;
        $wpcp->campaign_type  = $this->campaign_type;
        $wpcp->keyword        = $this->keyword;
        $wpcp->campaign_title = $this->campaign_title;
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
            $msg = __( 'Could not find the module for the campaign type', 'wp-content-pilot' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-campaign-type', $msg );
        }

        //get the module callback
        $module_class = $module['callback'];

        //module instance
        $instance = new $module_class;

        //set the parameters
        $instance->campaign_id = $this->campaign_id;
        $instance->keyword     = $this->keyword;

        //set the module
        $is_error = $instance->setup();

        //check error
        if ( is_wp_error( $is_error ) ) {
            wpcp_disable_campaign($this->campaign_id);
            return $is_error;
        }

        //run the module
        $article = apply_filters('wpcp_article_before_post_insert', $instance->run() , $this->campaign_id, $this->keyword);

        if ( is_wp_error( $article ) ) {
            return $article;
        }

        $inserted = $this->insert_post( $article );

        if ( is_wp_error( $inserted ) ) {
            return $inserted;
        }


        return $inserted;
    }

    protected function insert_post( $article ) {
        do_action( 'wpcp_before_post_insert', $article, $this->campaign_id, $this->keyword );
        $title          = apply_filters( 'wpcp_post_title', $article['title'], $article, $this->campaign_id );
        $post_content   = apply_filters( 'wpcp_post_content', $article['content'], $article, $this->campaign_id );
        $summary        = wp_trim_words( $article['content'], 55 );
        $summary        = preg_replace( '/\[embed.+embed\]/', '', strip_tags( $summary ) );
        $post_excerpt   = apply_filters( 'wpcp_post_excerpt', $summary, $this->campaign_id, $this->keyword );
        $author_id      = get_post_field( 'post_author', $this->campaign_id );
        $post_author    = apply_filters( 'wpcp_post_author', $author_id, $this->campaign_id, $this->keyword );
        $post_type      = apply_filters( 'wpcp_post_type', 'post', $this->campaign_id, $this->keyword );
        $post_status    = apply_filters( 'wpcp_post_status', 'publish', $this->campaign_id, $this->keyword );
        $post_meta      = apply_filters( 'wpcp_post_meta', [], $this->campaign_id, $this->keyword );
        $post_tax       = apply_filters( 'wpcp_post_taxonomy', [], $this->campaign_id, $this->keyword );
        $post_time      = apply_filters( 'wpcp_post_time', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ), $this->campaign_id, $this->keyword );
        $comment_status = apply_filters( 'wpcp_post_comment_status', get_default_comment_status( $post_type ), $this->campaign_id, $this->keyword );
        $ping_status    = apply_filters( 'wpcp_post_ping_status', get_default_comment_status( $post_type, 'pingback' ), $this->campaign_id, $this->keyword );

        /**
         * Filter to manipulate postarr param before insert a post
         *
         * @since 1.0.3
         *
         * @param array
         */
        $postarr = apply_filters( 'wpcp_insert_post_postarr', [
            'post_title'     => $title,
            'post_author'    => $post_author,
            'post_excerpt'   => $post_excerpt,
            'post_type'      => $post_type,
            'post_status'    => $post_status,
            'post_date'      => $post_time,
            'post_date_gmt'  => get_gmt_from_date( $post_time ),
            'post_content'   => $post_content,
            'meta_input'     => $post_meta,
            'tax_input'      => $post_tax,
            'comment_status' => $comment_status,
            'ping_status'    => $ping_status,
        ],$article, $this->campaign_id );

        $post_id = wp_insert_post( $postarr, true );

        if ( is_wp_error( $post_id ) ) {
            wpcp_log( 'critical', __( 'Post insertion failed Reason: ' . $post_id->get_error_message() ) );
            do_action( 'wpcp_post_insertion_failed', $this->campaign_id, $this->keyword );

            return $post_id;
        }

        update_post_meta( $post_id, '_wpcp_campaign_generated_post', $this->campaign_id );

        do_action( 'wpcp_after_post_publish', $post_id, $this->campaign_id, $this->keyword, $postarr, $article );

        return $post_id;
    }
}
