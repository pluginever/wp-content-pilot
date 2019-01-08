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
    }


    function fetch_links() {
        $page = $this->get_page_number( 0 );


        if( ! $page ){
            for ($page = 0 ; $page <= 10; $page++){

                $links = $this->bing_search($this->keyword, $page);

                if( !empty( $links ) ){
                    break;
                }
            }
        }else{
            $links =  $this->bing_search($this->keyword, $page);
            if( empty( $links )){
                $links =  $this->bing_search($this->keyword, $page + 1);
            }
        }


        $this->set_page_number( $page + 1 );

        return wp_list_pluck($links, 'link', 'pubDate');
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


}
