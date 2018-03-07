<?php

namespace Pluginever\WPCP\Module;

class Module {

    /**
     * Hold the modules
     *
     * @var
     */
    protected $modules;

    /**
     * Module constructor.
     */
    public function __construct() {
        $this->init_modules();
    }

    /**
     * Initialize the modules
     *
     * @return void
     */
    public function init_modules() {
        $this->modules = [
            'article' => [
                'title'       => __( 'Article', 'wpcp' ),
                'description' => __( 'Scraps articles with the predefined keywords', 'wpcp' ),
                'supports'    => array( 'author', 'title', 'except', 'content', 'image_url', 'image', 'images' ),
                'callback'    => 'Pluginever\WPCP\Module\Article',
            ],
            'feed'    => [
                'title'       => __( 'Feed', 'wpcp' ),
                'description' => __( 'Scraps articles from the feed urls', 'wpcp' ),
                'supports'    => array( 'author', 'title', 'except', 'content', 'image_url', 'image', 'images' ),
                'callback'    => 'Pluginever\WPCP\Module\Feed',
            ],
            'flickr'  => [
                'title'       => __( 'Flickr', 'wpcp' ),
                'description' => __( 'Scraps photo and contents by the predefined keywords', 'wpcp' ),
                'supports'    => array(
                    'published',
                    'author',
                    'author_url',
                    'title',
                    'description',
                    'content',
                    'image_url',
                    'image',
                    'images',
                    'tags',
                    'views',
                    'user_id',
                    'image_thumb_url',
                    'image_thumb',
                    'image_medium_url',
                    'image_medium',
                    'image_large_url',
                    'image_large',
                ),
                'callback'    => 'Pluginever\WPCP\Module\Flickr',
            ],
            'youtube' => [
                'title'       => __( 'Youtube', 'wpcp' ),
                'description' => __( 'Scraps youtube video and contents using the predefined keywords', 'wpcp' ),
                'supports'    => array(
                    'author',
                    'published',
                    'title',
                    'content',
                    'image',
                    'images',
                    'tags',
                    'video',
                    'video_url',
                    'video_shortcode',
                    'duration',
                    'view_count',
                    'like_count',
                    'dislike_count',
                    'favorite_count',
                    'comment_count',
                    'channel_url',
                    'channel_title'
                ),
                'callback'    => 'Pluginever\WPCP\Module\Youtube',
            ],
            'envato'  => [
                'title'       => __( 'Envato', 'wpcp' ),
                'description' => __( 'Scraps photo and contents by the predefined keywords', 'wpcp' ),
                'supports'    => array(
                    'published',
                    'author',
                    'author_url',
                    'title',
                    'description',
                    'summary',
                    'content',
                    'image_url',
                    'image',
                    'images',
                    'tags_raw',
                    'tags',
                    'categories_raw',
                    'categories',
                    'rating',
                    'attributes_raw',
                    'attributes',
                    'rating_count',
                    'number_of_sales',
                    'price',
                    'price_html',
                    'live_url',
                    'url',
                    'source',
                    'link',
                    'affiliate_url'
                ),
                'callback'    => 'Pluginever\WPCP\Module\Envato',
            ],
        ];

    }

    /**
     * Get all the registered modules
     *
     * @return array
     */
    public function get_modules() {
        return apply_filters( 'wpcp_get_modules', $this->modules );
    }

    /**
     * Get a module
     *
     * @param $module
     *
     * @return array|boolean
     */
    public function get_module( $module ) {
        if ( array_key_exists( $module, $this->modules ) ) {
            return $this->modules[ $module ];
        }

        return false;
    }

}
