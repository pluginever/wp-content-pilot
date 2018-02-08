<?php

namespace Pluginever\WPCP\Module;

use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;
use andreskrey\Readability\Readability;
use Pluginever\WPCP\Core\Item;
use Pluginever\WPCP\Traits\Hooker;

class Article extends Item {
    use Hooker;

    /**
     * Article constructor.
     */
    public function setup() {
        $this->action( 'wpcp_fetched_links', 'wpcp_article_skip_base_domain_url' );
        //$this->action( 'wpcp_post_content', 'html_treatment', 10, 2 );
    }


    function fetch_links() {

        $page = $this->get_page_number( 0 );

        $request = $this->setup_request( 'https://www.bing.com' );

        $request->get( 'search', array(
            'q'     => $this->keyword,
            'count' => 10,
            'loc'   => 'en',
            'first' => ( $page * 10 ),
        ) );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $links = wpcp_get_html_links( $response );
        $request->close();
        $this->set_page_number( $page + 1 );

        return $links;
    }

    function fetch_post( $link ) {

        //search for live  site
        $request = $this->setup_request();
        $request->get( $link->url );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $readability = new Readability( new Configuration() );

        try {
            $readability->parse( $response );
        } catch ( ParseException $e ) {
            wpcp_log( 'critical', $e->getMessage() );

            return new \WP_Error( $e->getCode(), $e->getMessage() );
        }

        $post = [
            'author'    => $readability->getAuthor(),
            'title'     => $readability->getTitle(),
            'except'    => $readability->getExcerpt(),
            'content'   => $readability->getContent(),
            'image_url' => $readability->getImage(),
            'image'     => wpcp_html_make_image_tag( $readability->getImage() ),
            'images'    => wpcp_html_make_image_tag( $readability->getImages() ),
            'direction' => $readability->getDirection(),
        ];

        return $post;

    }


    /*HOOKED FUNCTIONS*/
    /**
     * Remove posts if its home page of a site
     * @since 1.0.0
     *
     * @param $links
     *
     * @return mixed
     *
     */
    public function wpcp_article_skip_base_domain_url( $links ) {
        if ( empty( wpcp_get_post_meta( $this->campaign_id, '_skip_base_domain', true ) ) ) {
            return $links;
        }

        foreach ( $links as $key => $link ) {
            $url_parts = wp_parse_url( $link );

            if ( strlen( $url_parts['path'] ) < 5 ) {
                unset( $links[ $key ] );
            }
        }

        return $links;
    }


    /**
     * Check for relative links and fix those
     * @since 1.0.0
     *
     * @param $content
     * @param $article
     *
     * @return mixed
     *
     */
    public function html_treatment( $content, $article ) {
        if ( empty( get_post_meta( $this->campaign_id, '_parse_html' ) ) ) {
            return $content;
        }
        //$content = wpcp_html_remove_bad_tags( $content );
        $content = wpcp_html_fix_links( $content, $article['host'], true );

        return $content;
    }


}
