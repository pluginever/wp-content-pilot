<?php

namespace Pluginever\WPCP\Module;

use Curl\Curl;
use Pluginever\WPCP\Core\Item;
use Pluginever\WPCP\Traits\Hooker;

class Envato extends Item {
    use Hooker;
    protected $settings;
    protected $token;
    protected $user_name;

    /**
     * setup environment
     *
     * @since 1.0.1
     *
     * @return true|\WP_Error
     *
     */
    function setup() {
        $this->settings = get_option( 'wpcp_settings_envato', [] );
        $token     = $this->settings['token'];
        $user_name = $this->settings['user_name'];

        if ( empty( $token ) ) {
            $msg = __( 'Flickr API is not set. Please configure Flickr settings.', 'wp-content-pilot' );
            wpcp_log( 'critical', $msg );

            return new \WP_Error( 'invalid-flickr-settings', $msg );
        }

        $this->token = $token;

        $this->user_name = empty( $user_name ) ? '' : $user_name;

        $this->filter( 'content_pilot_setup_request', 'add_token', 10, 2 );
    }

    /**
     * fetch links of the items
     *
     * @since 1.0.1
     *
     * @return mixed|null|\WP_Error
     *
     */
    function fetch_links() {
        $page           = $this->get_page_number( '1' );
        $site           = wpcp_get_post_meta( $this->campaign_id, '_envato_platform', null );
        $sort_by        = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_by', 'relevance' );
        $sort_direction = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_direction', 'asc' );
        $price_range    = wpcp_get_post_meta( $this->campaign_id, '_price_range', '' );

        $price_range = explode( '|', $price_range );
        $min_price   = ! empty( $price_range[0] ) ? trim( $price_range[0] ) : 0;
        $max_price   = ! empty( $price_range[1] ) ? trim( $price_range[1] ) : 0;

        $query_args = [
            'site'           => $site,
            'term'           => $this->keyword,
            'category'       => '',
            'page'           => $page,
            'page_size'      => 50,
            'sort_by'        => $sort_by,
            'sort_direction' => $sort_direction,
        ];

        if ( ! empty( $min_price ) && ! empty( $max_price ) ) {
            $query_args['price_min'] = $min_price;
            $query_args['price_max'] = $max_price;
        }

        $query = add_query_arg( $query_args, 'https://api.envato.com/v1/discovery/search/search/item' );

        $request = $this->setup_request();

        $request->get( esc_url_raw( $query ) );

        $response = wpcp_is_valid_response( $request );


        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $items = $response->matches;
        $links = [];

        foreach ( $items as $item ) {
            $links[ $item->id ] = esc_url_raw( "https://api.envato.com/v3/market/catalog/item?id={$item->id}" );
        }

        $this->set_page_number( intval( $page ) + 1 );

        return $links;
    }

    function fetch_post( $link ) {
        $request = $this->setup_request();

        $request->get( $link->url );

        $response = wpcp_is_valid_response( $request );

        if ( is_wp_error( $response ) ) {
            return $response;
        }


        $tags           = $response->tags;
        $categories_raw = explode( '/', $response->classification );
        $categories     = implode( ', ', $categories_raw );
        $attributes     = wp_list_pluck( $response->attributes, 'value', 'label' );

        foreach ( $attributes as $attr_key => $attr_val ) {
            if ( is_array( $attr_val ) ) {
                $attributes[ $attr_key ] = implode( ', ', $attr_val );
            }
        }

        $attributes_html = '';
        foreach ( $attributes as $label => $value ) {
            $attributes_html .= "<li><strong>$label: </strong>$value</li>";
        }

        if ( ! empty( $attributes_html ) ) {
            $attributes_html = "<ul>{$attributes_html}</ul>";
        }


        $post = [
            'published'       => wpcp_parse_date_time( $response->published_at, true, true, true ),
            'author'          => $response->author_username,
            'author_url'      => $response->author_url,
            'title'           => $response->name,
            'description'     => $response->description,
            'summary'         => $response->summary,
            'content'         => '',
            'image_url'       => @$response->previews->landscape_preview->landscape_url,
            'image'           => wpcp_html_make_image_tag( @$response->previews->landscape_preview->landscape_url ),
            'images'          => wpcp_html_make_image_tag( @$response->previews->landscape_preview->landscape_url ),
            'tags_raw'        => $tags,
            'tags'            => implode( ', ', $tags ),
            'categories_raw'  => $categories_raw,
            'categories'      => $categories,
            'rating'          => $response->rating,
            'attributes_raw'  => $attributes,
            'attributes'      => $attributes,
            'rating_count'    => $response->rating_count,
            'number_of_sales' => $response->number_of_sales,
            'price'           => $response->price_cents / 100,
            'price_html'      => sprintf( '$%s', ( $response->price_cents / 100 ) ),
            'live_url'        => (string) esc_url_raw( $response->site . $response->previews->live_site->href ),
            'url'             => $response->url,
            'source'          => $response->url,
            'link'            => $response->url,
            'affiliate_url'   => add_query_arg( [ 'ref' => $this->user_name ], $response->url ),
        ];

        $contents = [
            __( 'Image', 'wp-content-pilot' )       => $post['image'],
            __( 'Author', 'wp-content-pilot' )      => sprintf( "<a href='%s'>%s</a>", $response->author_url, $post['author'] ),
            __( 'Description', 'wp-content-pilot' ) => $response->description,
            __( 'Price', 'wp-content-pilot' )       => $post['price_html'],
            __( 'Rating', 'wp-content-pilot' )      => $response->rating,
            __( 'tags', 'wp-content-pilot' )        => $post['tags'],
            __( 'categories', 'wp-content-pilot' )  => $post['categories'],
            __( 'Total Sales', 'wp-content-pilot' ) => $post['number_of_sales'],
            __( 'Live demo', 'wp-content-pilot' )   => $post['live_url'],
            __( 'Attributes', 'wp-content-pilot' )  => $attributes_html,
            __( 'Buy Now', 'wp-content-pilot' )     => sprintf( "<a href='%s'>%s</a>", $post['affiliate_url'], __( 'Buy Now', 'wp-content-pilot' ) ),
        ];

        $html = '';
        foreach ( $contents as $label => $content ) {
            $html .= "<strong>{$label}:</strong><br>";
            $html .= "{$content}<br>";
        }

        $post['content'] = $html;

        return $post;
    }

    /**
     * Add token
     *
     * @since 1.0.1
     *
     * @param \Curl\Curl $curl
     * @param $campaign_id
     *
     * @return \Curl\Curl
     *
     */
    public function add_token( $curl, $campaign_id ) {
        if ( $campaign_id !== $this->campaign_id ) {
            return;
        }

        $curl->setHeader( 'Authorization', 'bearer ' . trim( $this->token ) );

        return $curl;
    }

}
