<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;

class Flickr extends Item {
    protected $api;

    function setup() {
        $api = wpcp_get_settings( 'flickr_api', '41e9c750fc20be6fe068c9adbcc12d87' );

        if ( empty( $api ) ) {
            $msg = __( 'Flickr API is not set. Please configure Flickr settings.', 'wpcp' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-flickr-settings', $msg );
        }

        $this->api = $api;
    }

    function fetch_links() {
        $page    = $this->get_page_number( '1' );
        $request = $this->setup_request();

        $request->get( 'https://api.flickr.com/services/rest/', array(
            'text'           => $this->keyword,
            'api_key'        => $this->api,
            'sort'           => 'relevance',
            'content_type'   => 'photos',
            'media'          => 'photos',
            'per_page'       => '50',
            'page'           => $page,
            'format'         => 'json',
            'nojsoncallback' => '1',
            'method'         => 'flickr.photos.search',
        ) );


        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( empty( $response->photos->photo ) ) {
            $msg = __( 'Could not find any links', 'wpcp' );
            wpcp_log( 'log', $msg );

            return new \WP_Error( 'no-links-in-response', $msg );
        }

        $links = [];
        foreach ( $response->photos->photo as $item ) {

            $url = esc_url_raw( "https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=API_KEY&photo_id={$item->id}&secret={$item->secret}&format=json&nojsoncallback=1}" );

            $identifier = serialize( [
                'id'     => $item->id,
                'secret' => $item->secret,
                'server' => $item->server,
            ] );

            $links[ $identifier ] = $url;
        }

        $next_page = intval( $page ) + 1;
        if ( $next_page == $response->photos->pages ) {
            wpcp_disable_keyword( $this->campaign_id, $this->keyword );
        } else {
            $this->set_page_number( intval( $response->photos->page ) + 1 );
        }

        return $links;
    }

    function fetch_post( $link ) {
        $request = $this->setup_request();
        $url     = str_replace( 'API_KEY', $this->api, $link->url );

        $request->get( $url );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }
        $content = @$response->photo->description->_content;

        $tags = [];
        if ( isset( $response->photo->tags->tag ) ) {
            $tags = wp_list_pluck( $response->photo->tags->tag, 'raw' );
        }
        $image = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}.jpg";

        $post = [
            'published'    => wpcp_parse_date_time( $response->photo->dates->posted, true, true, true ),
            'author'       => $response->photo->owner->username,
            'author_url'   => "https://www.flickr.com/photos/{$response->photo->owner->nsid}/",
            'title'        => $response->photo->title->_content,
            'content'      => $content,
            'image'        => $image,
            'images'       => (array) $image,
            'tags'         => $tags,
            'views'        => $response->photo->views,
            'user_id'      => $response->photo->owner->nsid,
            'image_thumb'  => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_t.jpg",
            'image_medium' => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_c.jpg",
            'image_large'  => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_k.jpg",
        ];

        return $post;
    }
}
