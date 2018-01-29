<?php

namespace Pluginever\WPCP\Module;

use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;
use andreskrey\Readability\Readability;
use Pluginever\WPCP\Core\Item;

class Article extends Item {

    /**
     * Article constructor.
     */
    public function __construct() {
        $this->campaign_type = 'articles';
    }


    function fetch_links() {

        $page = $this->get_page_number( 0 );

        $request = $this->setup_request();

        $request->get( 'https://www.bing.com/search', array(
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
        $request->get( $link );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $readability = new Readability( new Configuration() );

        try {
            $readability->parse( $response );
        } catch ( ParseException $e ) {
            return new \WP_Error( $e->getCode(), $e->getMessage() );
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
