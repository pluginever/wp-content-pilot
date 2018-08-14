<?php

namespace Pluginever\WPCP\Core;

abstract class Item {
    /**
     * The campaign id
     *
     * @var integer
     */
    public $campaign_id;

    /**
     * The campaign keyword
     *
     * @var string
     */
    public $keyword;

    /**
     * Type of campaign
     *
     * @var string
     */
    protected $campaign_type;

    /**
     * url from garbing the post
     *
     * @var string
     */
    protected $final_url;

    /**
     * The post
     *
     * @var array
     */
    protected $post = array();


    /**
     * Run the campaign
     *
     * @since 1.0.0
     */
    public function run() {
        $caller              = basename( str_replace( '\\', '/', get_called_class() ) );
        $this->campaign_type = strtolower( $caller );
        $link                = $this->get_link();
        wpcp_log( 'dev', '========FROM ITEM=====' );
        wpcp_log( 'dev', $caller );
        wpcp_log( 'dev', $this->campaign_type );
        wpcp_log( 'dev', $link );
        if ( ! $link ) {
            $links = $this->fetch_links();
            if ( is_wp_error( $links ) ) {
                return $links;
            }

            //hook here for any link to subtract
            $links = apply_filters( 'wpcp_fetched_links', $links, $this->campaign_id, $this->campaign_type );

            if ( empty( $links ) ) {
                return new \WP_Error( 'no-links-found', __( 'Could not retrieve any valid links', 'wpcp' ) );
            }

            //check the result
            $str_links = implode( ' ', $links );

            if ( $this->is_result_like_last_time( $str_links ) ) {
                $msg = __( sprintf( 'Could not discover any new links to grab contents for the keyword "%s". Please try letter.', $this->keyword ), 'wpcp' );
                wpcp_log( 'log', $msg );

                return new \WP_Error( 'no-new-result', $msg );
            }


            $inserted = $this->inset_links( $links );

            wpcp_log( 'log', __( sprintf( 'Total %d links inserted', $inserted ), 'wpcp' ) );

            $link = $this->get_link();
            if ( ! $link ) {
                return new \WP_Error( 'no-valid-links-found', __( 'Could not retrieve any valid links', 'content-pilot' ) );
            }
        }
        wpcp_log( 'dev', sprintf( __( 'Fetching post from %s', 'wpcp' ), $link->url ) );

        do_action( 'wpcp_before_using_link', $link );
        $post = $this->fetch_post( $link );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        do_action( 'wpcp_after_using_link', $link );

        if ( empty( $this->post['url'] ) ) {
            $this->post['url'] = $link->url;
        }

        if ( empty( $this->post['source'] ) ) {
            $this->post['source'] = $this->post['url'];
        }

        if ( empty( $this->post['host'] ) ) {
            $this->post['host'] = wpcp_get_host( $this->post['url'] );
        }

        if ( empty( $this->post['link'] ) ) {
            $this->post['link'] = $link->url;
        }

        return array_merge( $this->post, $post );
    }


    public function bing_search( $keywords, $page = 0, $result_group = 'channel.item' ) {

        $request = $this->setup_request( 'https://www.bing.com' );
        $request->get( 'search', array(
            'q'      => $keywords,
            'count'  => 100,
            'loc'    => 'en',
            'format' => 'rss',
            'first'  => ( $page * 10 ),
        ) );

        $response = wpcp_is_valid_response( $request );
        $request->close();

        if ( ! $response ) {
            return [];

        }

        if ( ! $response instanceof \SimpleXMLElement ) {
            $response = simplexml_load_string( $response );
        }

        $deJson    = json_encode( $response );
        $xml_array = json_decode( $deJson, true );
        if ( ! $xml_array ) {
            return [];
        }

        $response_array = $xml_array;

        $result_group_arr = explode( '.', $result_group );
        foreach ( $result_group_arr as $key ) {
            if ( empty( $response_array[ $key ] ) ) {
                return [];
                break;
            }
            $response_array = $response_array[ $key ];

        }

        return $response_array;

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
        $sql    = $wpdb->prepare( "select * from {$table} where keyword = %s and camp_id  = %s and camp_type= %s and status = '0'",
            $this->keyword,
            $this->campaign_id,
            $this->campaign_type
        );
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
    private function get_uid( $string = '' ) {
        $string = '_wpcp_' . $this->campaign_id . '-' . $this->campaign_type . '-' . $this->keyword . '-' . $string;

        return sanitize_title( $string );
    }

    /**
     * Checks the result if its like the last run
     *
     * @since 1.0.0
     *
     * @param $html
     *
     * @return bool
     *
     */
    protected function is_result_like_last_time( $html ) {
        $hash      = @md5( (string) $html );
        $last_feed = wpcp_get_post_meta( $this->campaign_id, $this->get_uid( 'last-feed' ), '' );
        if ( $hash == $last_feed ) {
            return true;
        }

        update_post_meta( $this->campaign_id, $this->get_uid( 'last-feed' ), $hash );

        return false;
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
    public function get_page_number( $default = 0 ) {
        $page = get_post_meta( $this->campaign_id, "page-" . $this->get_uid( 'page-number' ), true );

        return ! empty( $page ) ? $page : $default;
    }

    /**
     * set the page number from where next query will be
     *
     * @since 1.0.0
     *
     * @param $number
     *
     */
    public function set_page_number( $number ) {
        update_post_meta( $this->campaign_id, "page-" . $this->get_uid( 'page-number' ), $number );
    }

    /**
     * create the request object
     *
     * @since 1.0.0
     *
     * @param null $url
     *
     * @return \Curl\Curl
     *
     */
    public function setup_request( $url = null ) {
        return wpcp_setup_request( $this->campaign_type, $url, $this->campaign_id );
    }

    /**
     * Insert links
     *
     * @since 1.0.0
     *
     * @param $links
     *
     * @return int
     *
     */
    protected function inset_links( $links ) {
        $counter = 0;
        foreach ( $links as $indentifier => $link ) {
            $id = wpcp_insert_link( array(
                'camp_id'    => $this->campaign_id,
                'url'        => $link,
                'keyword'    => $this->keyword,
                'identifier' => $indentifier,
                'camp_type'  => $this->campaign_type,
                'status'     => 0,
            ) );

            if ( $id ) {
                $counter ++;
            }
        }

        return $counter;
    }

    protected function get_caller_class() {
        $caller_class     = get_called_class();
        $caller_class_arr = explode( "\\", $caller_class );
        $this->campaign_type = $caller_class_arr[(count($caller_class_arr)-1)];
    }

    /**
     * Set the parameter
     *
     * @since 1.0.0
     *
     * @return true|\WP_Error
     *
     */
    abstract function setup();

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
    abstract function fetch_post( $link );

}
