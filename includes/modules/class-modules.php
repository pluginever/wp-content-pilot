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
                'supports'    => array( 'title', 'author', 'image', 'images', 'content', 'excerpt' ),
                'callback'    => 'Pluginever\WPCP\Module\Article',
            ],
            'feed'    => [
                'title'       => __( 'Feed', 'wpcp' ),
                'description' => __( 'Scraps articles from the feed urls', 'wpcp' ),
                'supports'    => array( 'title', 'author', 'image', 'images', 'content', 'excerpt' ),
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
                    'content',
                    'image',
                    'images',
                    'tags',
                    'views',
                    'user_id',
                    'image_thumb',
                    'image_medium',
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
            'amazon' => [
                'title'       => __( 'AMAZON', 'wpcp' ),
                'description' => __( 'Scraps youtube video and contents using the predefined keywords', 'wpcp' ),
                'supports'    => array(
                ),
                'callback'    => 'Pluginever\WPCP\Module\Amazon',
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
