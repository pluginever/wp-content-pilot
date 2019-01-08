<?php

namespace Pluginever\WPCP\Module;

use Pluginever\WPCP\Core\Item;

class Flickr extends Item {
    protected $api;
    protected $settings;

    function setup() {
        $this->settings = get_option('wpcp_settings_flickr', []);
        $api = $this->settings['api_key'];

        if ( empty( $api ) ) {
            $msg = __( 'Flickr API is not set. Please configure Flickr settings.', 'wp-content-pilot' );
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
            $msg = __( 'Could not find any links', 'wp-content-pilot' );
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

    /**
     * Fetch post
     *
     * @since 1.0.0
     *
     * @param $link
     *
     * @return array|mixed|null|\WP_Error
     *
     */
    function fetch_post( $link ) {
        $request = $this->setup_request();
        $url     = str_replace( 'API_KEY', $this->api, $link->url );

        $request->get( $url );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }
        $description = @$response->photo->description->_content;

        $tags = [];
        if ( isset( $response->photo->tags->tag ) ) {
            $tags = wp_list_pluck( $response->photo->tags->tag, 'raw' );
        }
        $image_url = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}.jpg";
        $tags_html = implode( ', ', $tags );

        $contents = [
            __( 'Image', 'wp-content-pilot' )       => wpcp_html_make_image_tag( $image_url ),
            __( 'Author', 'wp-content-pilot' )      => "<a href='https://www.flickr.com/photos/{$response->photo->owner->nsid}/'>{$response->photo->owner->username}</a>",
            __( 'Description', 'wp-content-pilot' ) => $description,
            __( 'Views', 'wp-content-pilot' )       => $response->photo->views,
            __( 'tags', 'wp-content-pilot' )        => $tags_html,
        ];

        $html = '';
        foreach ( $contents as $label => $content ) {
            $html .= "<strong>{$label}:</strong><br>";
            $html .= "{$content}<br>";
        }

        $post = [
            'published'        => wpcp_parse_date_time( $response->photo->dates->posted, true, true, true ),
            'author'           => $response->photo->owner->username,
            'author_url'       => "https://www.flickr.com/photos/{$response->photo->owner->nsid}/",
            'title'            => $response->photo->title->_content,
            'description'      => $description,
            'content'          => $html,
            'image_url'        => $image_url,
            'image'            => wpcp_html_make_image_tag( $image_url ),
            'images'           => wpcp_html_make_image_tag( $image_url ),
            'tags_raw'         => $tags,
            'tags'             => implode( ' ', $tags ),
            'views'            => $response->photo->views,
            'user_id'          => $response->photo->owner->nsid,
            'image_thumb_url'  => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_t.jpg",
            'image_thumb'      => wpcp_html_make_image_tag( "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_t.jpg" ),
            'image_medium_url' => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_c.jpg",
            'image_medium'     => wpcp_html_make_image_tag( "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_c.jpg" ),
            'image_large_url'  => "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_k.jpg",
            'image_large'      => wpcp_html_make_image_tag( "https://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}_k.jpg" ),
        ];

        return $post;
    }
}
