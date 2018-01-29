<?php

namespace Pluginever\WPCP\Module;

use MarcL\AmazonAPI;
use MarcL\AmazonUrlBuilder;
use Pluginever\WPCP\Core\Item;

require "module-categories.php";

class Amazon extends Item {
    protected $site_extension;
    protected $category;
    protected $api_key;
    protected $api_secret;
    protected $associate_tag;

    public function setup() {
        $extension   = wpcp_get_post_meta( $this->campaign_id, '_amazon_extension', 'us' );
        $category_id = wpcp_get_post_meta( $this->campaign_id, '_amazon_category', '0' );
        $category    = wpcp_get_amazon_categories( $category_id );

        $this->site_extension = $extension;
        $this->category       = $category;

        $api_key       = wpcp_get_settings( 'amazon_api_key', 'AKIAJPIJU4VFLHKZHO2A' );
        $api_secret    = wpcp_get_settings( 'amazon_api_secret', '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' );
        $associate_tag = wpcp_get_settings( 'amazon_associate_tag', 'spgadgets111-20' );

        if ( empty( $api_key ) || empty( $api_secret ) || empty( $associate_tag ) ) {
            $msg = __( 'Amazon api is not configured. Please configure Amazon settings.', 'wpcp' );
            wpcp_log( 'critical', $msg );
            wpcp_disable_campaign( $this->campaign_id );
            new \WP_Error( 'invalid-api-settings', $msg );
        }

        $this->api_key       = $api_key;
        $this->api_secret    = $api_secret;
        $this->associate_tag = $associate_tag;

    }

    function fetch_links() {
        $min_price = wpcp_get_post_meta( $this->campaign_id, '_min_price', '0' );
        $max_price = wpcp_get_post_meta( $this->campaign_id, '_max_price', '100000000000000' );
        $page      = $this->get_page_number( '1' );

        $url = new AmazonUrlBuilder(
            $this->api_key,
            $this->api_secret,
            $this->associate_tag,
            $this->site_extension
        );

        $amazonAPI = new AmazonAPI( $url, 'simple' );

        try {
            $items = $amazonAPI->ItemSearch( $this->keyword, $this->category, null, 'New', $page, $min_price, $max_price );
        } catch ( \Exception $e ) {
            wpcp_log( 'critical', $e->getMessage() );
            wpcp_disable_campaign( $this->campaign_id );
            new \WP_Error( 'error-in-response', $e->getMessage() );
        }
        $links = [];
        foreach ( $items as $item ) {
            $links[ $item['asin'] ] = $item['url'];
        }

        $this->set_page_number( intval( $page ) + 1 );

        return $links;
    }

    function fetch_post( $link ) {

        $url = new AmazonUrlBuilder(
            $this->api_key,
            $this->api_secret,
            $this->associate_tag,
            $this->site_extension
        );

        $amazonAPI = new AmazonAPI( $url, 'simple' );
        try {
            $item = $amazonAPI->ItemLookup( $link->identifier );
        } catch ( \Exception $e ) {
            wpcp_log( 'critical', $e->getMessage() );
            new \WP_Error( 'error-in-response', $e->getMessage() );
        }

        $item = ! empty( $item ) && is_array( $item ) ? array_pop( $item ) : $item;


        $features = '';
        if ( ! empty( $item['features'] ) ) {
            $features .= '<ul>';
            foreach ( $item['features'] as $feature ) {
                $features .= '<li>' . $feature . '</li>';
            }
            $features .= '</ul>';
        }


        $post = [
            'title'    => $item['title'],
            'content'  => $item['description'],
            'features' => $features,
            'image'    => (string) $item['largeImage'],
            'images'   => (array) $item['largeImage'],
            'tags'     => array_filter( $item['tags'], 'strlen' ),
            'price'    => $item['lowestPrice'],
            'url'      => $item['url'],
            'brand'    => $item['brand'],
            'model'    => $item['model'],
            'reviews'    => '<iframe src="'.$item['review'].'" frameborder="0" class="wpcp-amazon-reviews"></iframe>',
        ];

        echo $post['reviews'];


    }


}
