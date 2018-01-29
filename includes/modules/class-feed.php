<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;
use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;
use andreskrey\Readability\Readability;

class Feed extends Item {
    public function __construct() {
        $this->campaign_type = 'articles';
        add_action( 'wp_feed_options', array( $this, 'set_feed_options' ) );
        add_action( 'http_response', array( $this, 'trim_feed_content' ) );

        $force_feed = wpcp_get_post_meta( $this->campaign_id, 'force_feed', 0 );
        if ( $force_feed ) {
            add_action( 'wp_feed_options', array( $this, 'force_feed' ), 10, 1 );
        }
    }

    /**
     * Set user agent to fix curl transfer
     * closed without complete data
     *
     * @since 1.0.0
     *
     * @param $args
     */
    public function set_feed_options( $args ) {
        $args->set_useragent( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 ' );
    }

    /**
     * Trim body to remove extra space
     *
     * @param $args
     *
     * @return mixed
     */
    public function trim_feed_content( $args ) {
        $args['body'] = trim( $args['body'] );

        return $args;
    }

    /**
     * Force feed with the given feedlink
     *
     * @param $feed
     */
    public function force_feed( $feed ) {
        $feed->force_feed( true );
    }


    function fetch_links() {
        $host = parse_url( $this->keyword, PHP_URL_HOST );
        include_once( ABSPATH . WPINC . '/feed.php' );

        $rss = fetch_feed( $this->keyword );
        if ( is_wp_error( $rss ) ) {
            return $rss;
        }
        if ( $this->is_result_like_last_time( $rss ) ) {
            $msg = __( 'result is same as last time', 'wpcp' );
            wpcp_log( 'log', $msg );

            return new \WP_Error( 'no-new-result', $msg );
        }

        $max_items = $rss->get_item_quantity();
        $rss_items = $rss->get_items( 0, $max_items );

        if ( ! isset( $max_items ) || $max_items == 0 ) {
            wpcp_disable_keyword( $this->campaign_id, $this->keyword, '_wpcp_feed_links' );
            $msg = __( 'Could not find any post so disabling url', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'fetch-links-failed', $msg );
        }


        $links = [];

        foreach ( $rss_items as $item ) {
            $url = esc_url( $item->get_permalink() );

            if ( stristr( $url, 'news.google' ) ) {
                $urlParts   = explode( 'url=', $url );
                $correctUrl = $urlParts[1];
                $url        = $correctUrl;

            }

            //Google alerts links correction
            if ( stristr( $this->keyword, 'alerts/feeds' ) && stristr( $this->keyword, 'google' ) ) {
                preg_match( '{url\=(.*?)[&]}', $url, $urlMatches );
                $correctUrl = $urlMatches[1];

                if ( trim( $correctUrl ) != '' ) {
                    $url = $correctUrl;
                }
            }

            $links[] = $url;

        }


        return apply_filters( 'wpcp_fetched_links', $links, $this->campaign_id, $this->campaign_type );

    }

    function fetch_post( $link ) {
        $request = $this->setup_request();
        $request->get( $link );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $readability = new Readability( new Configuration() );

        try {
            $readability->parse( $response );
        } catch ( ParseException $e ) {
            $msg = $e->getMessage();
            wpcp_log('log', $msg);
            return new \WP_Error( 'post-parse-failed', $msg );
        }

        $post = [
            'author'    => $readability->getAuthor(),
            'title'     => $readability->getTitle(),
            'except'    => $readability->getExcerpt(),
            'content'   => $readability->getContent(),
            'image'     => $readability->getImage(),
            'images'    => $readability->getImages(),
            'direction' => $readability->getDirection(),
        ];

        return $post;
    }

    function setup() {
        // TODO: Implement setup() method.
    }
}

