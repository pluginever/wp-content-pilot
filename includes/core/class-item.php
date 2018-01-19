<?php

namespace Pluginever\WPCP\Core;

abstract class Item {
    /**
     * The campaign id
     * @var integer
     */
    protected $campaign_id;

    /**
     * The campaign keyword
     * @var string
     */
    protected $keyword;

    /**
     * Type of campaign
     * @var string
     */
    protected $campaign_type;

    /**
     * The post
     * @var array
     */
    protected $post = array();


    /**
     * Run the campaign
     *
     * @since 1.0.0
     */
    public function run() {
        $link = $this->get_link();

        if ( ! $link ) {
            $this->fetch_links();
            $link = $this->get_link();
            if ( ! $link ) {
                return new \WP_Error( 'no-valid-links-found', __( 'Could not retrieve any valid links', 'content-pilot' ) );
            }
        }


        $post = $this->get_post();

        return array_merge( $this->post, $post );

    }

    /**
     * Get new link
     *
     * @since 1.0.0
     *
     * @return object|bool
     */
    protected function get_link() {
        global $wpdb;
        $table  = $wpdb->prefix . 'wpcp_links';
        $sql    = "select * from {$table} where keyword = '{$this->keyword}' and camp_id  = '$this->campaign_id' and camp_type='{$this->campaign_type}' and status = '0'";
        $result = $wpdb->get_row( $sql );

        if ( empty( $result ) ) {
            return false;
        }

        return $result;
    }

    /**
     * Get unique string for the campaign
     *
     * @since 1.0.0
     *
     * @return string
     *
     */
    private function get_uid() {
        $string = $this->campaign_id . '-' . $this->campaign_type . '-' . $this->keyword;

        return sanitize_title( $string );
    }

    /**
     * Get last page
     *
     * @since 1.0.0
     *
     * @param int $default
     *
     * @return int|mixed
     *
     */
    public function get_last_page_number( $default = 0 ) {
        $page = get_post_meta( $this->campaign_id, "last-page-" . $this->get_uid(), true );

        return ! empty( $page ) ? $page : $default;
    }

    /**
     * create the request object
     * @since 1.0.0
     *
     * @param null $url
     *
     * @return \Curl\Curl
     *
     */
    public function setup_request( $url = null ) {
        return cp_setup_request( $this->campaign_type, $url, $this->campaign_id );
    }

    /**
     * fetch links
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    abstract function fetch_links();

    /**
     * Get post
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    abstract function get_post( $link );

}
