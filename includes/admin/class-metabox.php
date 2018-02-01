<?php

namespace Pluginever\WPCP\Admin;

use Pluginever\WPCP\Traits\Hooker;

class Metabox {
    use Hooker;

    public function __construct() {
        $this->action( 'admin_init', 'register_campaign_settings_metabox' );
        $this->action( 'admin_init', 'register_campaign_options_metabox' );
        $this->action( 'admin_init', 'register_post_settings_metabox' );
        $this->action( 'admin_init', 'register_advanced_settings_metabox' );

        //settings fields
        $this->filter( 'wpcp_campaign_settings_fields', 'add_campaign_settings_fields' );

        //default main fields
        $this->filter( 'wpcp_module_main_fields', 'add_default_main_fields', 99 );

        //default additional fields
        $this->filter( 'wpcp_module_additional_fields', 'add_default_additional_fields', 99 );


        //feed
        $this->filter( 'wpcp_module_main_fields', 'add_feed_main_fields' );


        //article
        $this->filter( 'wpcp_module_main_fields', 'add_article_main_fields' );
        $this->filter( 'wpcp_module_additional_fields', 'add_article_additional_fields' );

        //youtube
        $this->filter( 'wpcp_module_main_fields', 'add_youtube_main_fields' );
        $this->filter( 'wpcp_module_additional_fields', 'add_youtube_additional_fields' );

        //post settings
        $this->filter( 'wpcp_post_settings_fields', 'add_default_post_settings_fields' );

        //advanced settings
        $this->filter( 'wpcp_advanced_settings_fields', 'add_default_advanced_settings_fields' );
    }

    /**
     * Register campaign settings metabox
     * @since 1.0.0
     */
    public function register_campaign_settings_metabox() {
        $metabox = new \Pluginever\Framework\Metabox( 'wpcp_campaign_settings_metabox' );
        $config  = array(
            'title'        => __( 'Campaign Settings', 'wpcp' ),
            'screen'       => 'wp_content_pilot',
            'context'      => 'side',
            'priority'     => 'high',
            'lazy_loading' => 'true',
            'fields'       => array()
        );

        $config['fields'] = apply_filters( 'wpcp_campaign_settings_fields', [] );
        $metabox->init( apply_filters( 'wpcp_campaign_settings_metabox', $config ) );
    }

    public function register_campaign_options_metabox() {
        $metabox = new \Pluginever\Framework\Metabox( 'wpcp_campaign_options_metabox' );
        $config  = array(
            'title'        => __( 'Campaign Options', 'wpcp' ),
            'screen'       => 'wp_content_pilot',
            'context'      => 'normal',
            'priority'     => 'high',
            'lazy_loading' => 'true',
            'fields'       => array()
        );

        $modules     = [];
        $all_modules = wpcp_get_modules();
        foreach ( $all_modules as $key => $model_details ) {
            $modules[ $key ] = $model_details['title'];
        }

        $module_fields = array(
            array(
                'type'     => 'select',
                'name'     => '_campaign_type',
                'label'    => 'Campaign Type',
                'value'    => 'feed',
                'tooltip'  => __( 'Select campaign type', 'wpcp' ),
                'sanitize' => 'sanitize_key',
                'required' => 'true',
                'options'  => $modules
            )
        );

        $module_main_fields = apply_filters( 'wpcp_module_main_fields', [] );

        $additional_title_fields = array(
            array(
                'type'          => 'title',
                'name'          => 'additional_fields-sep',
                'wrapper_class' => 'divider',
            ),
            array(
                'type'          => 'title',
                'name'          => '_additional_fields',
                'wrapper_class' => 'additional_fields',
                'label'         => 'Additional Settings',
            )
        );

        $additional_fields = apply_filters( 'wpcp_module_additional_fields', [] );

        $fields = array_merge( $module_fields, $module_main_fields, $additional_title_fields, $additional_fields );

        $config['fields'] = $fields;
        $metabox->init( apply_filters( 'wpcp_campaign_options_metabox', $config ) );
    }

    /**
     * Register post settings metabox
     * @since 1.0.0
     */
    public function register_post_settings_metabox() {
        $metabox = new \Pluginever\Framework\Metabox( 'wpcp_post_settings_metabox' );
        $config  = array(
            'title'        => __( 'Post Settings', 'wpcp' ),
            'screen'       => 'wp_content_pilot',
            'context'      => 'normal',
            'priority'     => 'high',
            'lazy_loading' => 'true',
            'fields'       => array()
        );

        $config['fields'] = apply_filters( 'wpcp_post_settings_fields', [] );
        $metabox->init( apply_filters( 'wpcp_post_settings_metabox', $config ) );
    }

    /**
     * Register advanced settings metabox
     *
     * @since 1.0.0
     */
    public function register_advanced_settings_metabox() {
        $metabox = new \Pluginever\Framework\Metabox( 'wpcp_advanced_settings_metabox' );
        $config  = array(
            'title'        => __( 'Advanced Settings', 'wpcp' ),
            'screen'       => 'wp_content_pilot',
            'context'      => 'normal',
            'priority'     => 'high',
            'lazy_loading' => 'true',
            'fields'       => array()
        );

        $config['fields'] = apply_filters( 'wpcp_advanced_settings_fields', [] );
        $metabox->init( apply_filters( 'wpcp_advanced_settings_metabox', $config ) );
    }

    public function add_campaign_settings_fields( $fields ) {
        $add_fields = [
            [
                'type'  => 'checkbox',
                'label' => __( 'Active', 'wpcp' ),
                'title' => 'Yes',
                'name'  => '_active',
            ],
            [
                'type'        => 'number',
                'label'       => __( 'Campaign Target', 'wpcp' ),
                'name'        => '_campaign_target',
                'sanitize'    => 'intval',
                'required'    => 'true',
                'placeholder' => 100
            ],
            [
                'type'    => 'select',
                'label'   => __( 'Frequency (Every)', 'wpcp' ),
                'name'    => '_frequency',
                'help' => __( 'The campaign will run every X hour until the campaign target reach.', 'wpcp' ),
                'options' => wpcp_get_campaign_schedule_options()
            ]
        ];

        return array_merge( $fields, $add_fields );
    }

    public function add_default_main_fields( $fields ) {
        $add_fields = [
            [
                'type'     => 'number',
                'name'     => '_min_words',
                'label'    => 'Min Words',
                'tooltip'  => __( 'if grabbed post content less than following words then post will be ignored. Default 0', 'wpcp' ),
                'sanitize' => 'intval',
            ],
            [
                'type'    => 'radio',
                'name'    => '_content_type',
                'label'   => 'Content Type',
                'value'   => 'html',
                'tooltip' => __( 'If content type is HTML then HTML content will be posted otherwise normal text will be posted.', 'wpcp' ),
                'options' => array(
                    'html' => 'HTML',
                    'text' => 'Plain Text',
                )
            ]
        ];

        return array_merge( $fields, $add_fields );
    }

    public function add_default_additional_fields( $fields ) {
        $add_fields = [
            [
                'type'  => 'checkbox',
                'name'  => '_set_featured_image',
                'label' => ' ',
                'title' => __( 'Use first image as featured image', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_remove_images',
                'label' => ' ',
                'title' => __( 'Remove all images from the article', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_excerpt',
                'label' => ' ',
                'title' => __( 'Use summary as excerpt', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_strip_links',
                'label' => ' ',
                'title' => __( 'Remove hyperlinks found in the article', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_skip_no_image',
                'label' => ' ',
                'title' => __( 'Skip post if no image found in the article', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_skip_not_eng',
                'label' => ' ',
                'title' => __( 'Skip post if language is not english', 'wpcp' ),
            ],
            [
                'type'  => 'checkbox',
                'name'  => '_skip_duplicate_title',
                'label' => ' ',
                'title' => __( 'Skip post with duplicate title', 'wpcp' ),
            ],
        ];

        return array_merge( $fields, $add_fields );
    }


    public function add_default_post_settings_fields( $fields ) {
        $add_fields = [
            [
                'type'  => 'text',
                'label' => __( 'Post Title', 'wpcp' ),
                'name'  => '_post_title',
                'required'  => 'true',
                'value' => '{original_title}',
            ],
            [
                'type'  => 'textarea',
                'label' => __( 'Post Template', 'wpcp' ),
                'name'  => '_post_template',
                'class' => 'min-h-150',
                'value' => '{content}',
                'required'  => 'true',
                'help'  => 'Supported Tags:',
            ],
            [
                'type'    => 'select',
                'name'    => '_post_type',
                'label'   => __( 'Post Type', 'wpcp' ),
                'value'   => 'post',
                'required'  => 'true',
                'options' => wpcp_get_post_types()
            ],
            [
                'type'    => 'select',
                'label'   => __( 'Post Author', 'wpcp' ),
                'name'    => '_post_author',
                'required'  => 'true',
                'options' => wpcp_get_authors()
            ],
            [
                'type'    => 'select',
                'label'   => __( 'Post Status', 'wpcp' ),
                'name'    => '_post_status',
                'value'   => 'publish',
                'options' => array(
                    'publish' => 'Published',
                    'private' => 'Private',
                    'draft'   => 'Draft',
                    'pending' => 'Pending'
                )
            ]
        ];

        return array_merge( $fields, $add_fields );
    }

    public function add_default_advanced_settings_fields( $fields ) {
        $add_fields = [
            [
                'type'    => 'number',
                'label'   => __( 'Limit Title', 'wpcp' ),
                'name'    => '_title_limit',
                'value'   => '',
                'tooltip' => __( 'If you want to limit the title to x characters, input the number above. Default full title.', 'wpcp' )
            ],
            [
                'type'    => 'number',
                'label'   => __( 'Limit Content', 'wpcp' ),
                'name'    => '_content_limit',
                'value'   => '',
                'tooltip' => __( 'If you want to limit the post content to x characters, input the number above. Default full content.', 'wpcp' )
            ]
        ];

        return array_merge( $fields, $add_fields );
    }

    public function add_feed_main_fields( $fields ) {
        $add_fields = [
            [
                'type'      => 'textarea',
                'name'      => '_feed_links',
                'label'     => 'Feed Links',
                'tooltip'   => __( 'Put links from where you grab the posts. Single links per line.', 'wpcp' ),
                'sanitize'  => 'wpcp_sanitize_feed_links',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'feed',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'checkbox',
                'name'      => '_force_feed',
                'label'     => 'Force Feed',
                'title'     => 'Yes',
                'tooltip'   => __( 'If you are putting the exact feed links then set force feed otherwise feed link will be auto discovered.', 'wpcp' ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'feed',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'checkbox',
                'name'      => '_parse_html',
                'label'     => 'Parse HTML',
                'tooltip'   => __( 'Post process html to fix broken links, image paths. NOTE: Takes longer time to process.', 'wpcp' ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'feed',
                    'depend_cond'  => '==',
                ]
            ],
        ];

        return array_merge( $fields, $add_fields );
    }

    public function add_article_main_fields( $fields ) {
        $add_fields = [
            [
                'type'      => 'textarea',
                'name'      => '_keywords',
                'label'     => 'Keywords',
                'tooltip'   => __( 'Put keywords using those the post will be grabbed. Separate words by comma.', 'wpcp' ),
                'sanitize'  => 'wpcp_sanitize_keywords',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'feed',
                    'depend_cond'  => '!=',
                ]
            ],
            [
                'type'      => 'checkbox',
                'name'      => '_parse_html',
                'label'     => 'Parse HTML',
                'tooltip'   => __( 'Post process html to fix broken links, image paths. NOTE: Takes longer time to process.', 'wpcp' ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'article',
                    'depend_cond'  => '==',
                ]
            ],
        ];

        return array_merge( $fields, $add_fields );
    }

    function add_article_additional_fields( $fields ) {
        $add_fields = [
            [
                'type'      => 'checkbox',
                'name'      => '_skip_base_domain',
                'label'     => ' ',
                'title'     => 'Skip Fetching post from base domain',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'article',
                    'depend_cond'  => '==',
                ]
            ],
        ];

        return array_merge( $fields, $add_fields );
    }



    public function add_youtube_main_fields( $fields ) {
        $add_fields = [
            [
                'type'      => 'select',
                'name'      => '_youtube_search_type',
                'label'     => 'Search Type',
                'tooltip'   => __( 'Use global search for all result or use specific channel if you want to limit to that channel.', 'wpcp' ),
                'options'   => array(
                    'global'  => 'Global',
                    'channel' => 'From Specific Channel',
                ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'url',
                'name'      => '_youtube_channel_id',
                'label'     => 'Channel ID',
                'tooltip'   => __( 'eg. channel id is UCIQOOX3ReApm-KTZ66eMVzQ for https://www.youtube.com/channel/UCIQOOX3ReApm-KTZ66eMVzQ', 'wpcp' ),
                'condition' => [
                    'depend_on'    => '_youtube_search_type',
                    'depend_value' => 'channel',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'select',
                'name'      => '_youtube_category',
                'label'     => 'Youtube Category',
                'options'   => wpcp_get_youtube_categories(),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'select',
                'name'      => '_youtube_search_orderby',
                'label'     => 'Search Order By',
                'options'   => array(
                    'relevance' => 'Relevance',
                    'date'      => 'Date',
                    'title'     => 'Title',
                    'viewCount' => 'View Count',
                    'rating'    => 'Rating',
                ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'select',
                'name'      => '_youtube_search_order',
                'label'     => 'Search Order',
                'value'     => 'asc',
                'options'   => array(
                    'asc'  => 'ASC',
                    'desc' => 'DESC',
                ),
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
        ];

        return array_merge( $fields, $add_fields );
    }


    function add_youtube_additional_fields( $fields ) {
        $add_fields = [
            [
                'type'      => 'checkbox',
                'name'      => '_youtube_autoplay',
                'label'     => ' ',
                'title'     => 'Auto play video',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'checkbox',
                'name'      => '_youtube_disable_suggestion',
                'label'     => ' ',
                'title'     => 'Disable Suggestion at the end of video',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
            [
                'type'      => 'checkbox',
                'name'      => '_youtube_hide_logo',
                'label'     => ' ',
                'title'     => 'Hide Youtube logo',
                'condition' => [
                    'depend_on'    => '_campaign_type',
                    'depend_value' => 'youtube',
                    'depend_cond'  => '==',
                ]
            ],
        ];

        return array_merge( $fields, $add_fields );
    }


}
