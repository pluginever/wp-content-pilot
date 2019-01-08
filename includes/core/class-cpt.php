<?php
namespace Pluginever\WPCP\Core;

use Pluginever\WPCP\Traits\Hooker;

class CPT{
    use Hooker;
    public function __construct() {
        $this->action( 'init', 'register_custom_posts' );
        $this->action( 'manage_wp_content_pilot_posts_columns', 'wp_content_pilot_columns', 10 );
        $this->action('manage_wp_content_pilot_posts_custom_column', 'wp_content_pilot_column_content', 10, 2);
    }

    public function register_custom_posts(){
        $labels = array(
            'name'               => __( 'Campaigns', 'wp-content-pilot' ),
            'all_items'          => __( 'All Campaigns', 'wp-content-pilot' ),
            'singular_name'      => 'wp_content_pilot',
            'add_new'            => __( 'New campaign', 'wp-content-pilot' ),
            'add_new_item'       => __( 'Add New Campaign', 'wp-content-pilot' ),
            'edit_item'          => __( 'Edit Campaign', 'wp-content-pilot' ),
            'new_item'           => __( 'New Campaign', 'wp-content-pilot' ),
            'view_item'          => __( 'View Campaign', 'wp-content-pilot' ),
            'search_items'       => __( 'Search Campaigns', 'wp-content-pilot' ),
            'not_found'          => __( 'No Campaigns found', 'wp-content-pilot' ),
            'not_found_in_trash' => __( 'No Campaigns found in Trash', 'wp-content-pilot' ),
            'parent_item_colon'  => __( 'Parent Campaign:', 'wp-content-pilot' ),
            'menu_name'          => 'WP Content Pilot',
        );

        $args = array(
            'labels'              => $labels,
            'hierarchical'        => false,
            'description'         => __( 'Campaigns of Wordpress Content Pilot', 'wp-content-pilot' ),
            'supports'            => array( 'title' ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'menu_position'       => 5,
            'menu_icon'       => WPCP_ASSETS .'/images/logo.svg',
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
            'capability_type'     => 'post',

        );

        register_post_type( 'wp_content_pilot', $args );
    }


    function wp_content_pilot_columns( $columns ) {
        unset( $columns['date'] );
        $columns['status']    = __( 'Status', 'wp-content-pilot' );
        $columns['type']      = __( 'Type', 'wp-content-pilot' );
        $columns['target']      = __( 'Target', 'wp-content-pilot' );
        $columns['frequency'] = __( 'Frequency', 'wp-content-pilot' );
        $columns['last_run']  = __( 'Last Run', 'wp-content-pilot' );

        return $columns;
    }

    public function wp_content_pilot_column_content($column_name, $post_ID){
        switch ($column_name){
            case 'status':
                $active = wpcp_get_post_meta($post_ID, '_active', 0);
                if($active == '1'){
                    echo '<span style="background: green;color: #fff;padding: 2px 10px;box-shadow: 2px 2px 2px;">'.__('Active', 'wp-content-pilot').'</span>';
                }else{
                    echo '<span style="background: red;color: #fff;padding: 2px 10px;box-shadow: 2px 2px 2px;">'.__('Disabled', 'wp-content-pilot').'</span>';
                }
                break;
            case 'type':
                $campaign_type = wpcp_get_post_meta($post_ID, '_campaign_type');
                echo ucfirst($campaign_type);
                break;
            case 'target':
                $target = wpcp_get_post_meta($post_ID, '_campaign_target', 0);
                $completed = wpcp_get_post_meta($post_ID, '_post_count', 0);
                if( $target && $completed ){
                    echo $completed.'/'.$target;
                }else{
                    echo ' - ';
                }
                break;
            case 'frequency':
                $frenquency = wpcp_get_post_meta($post_ID, '_frequency', 0);
                if( $frenquency ){
                    echo 'Every '. $frenquency/3600 . ' Hour(s)';
                } else{
                    echo ' - ';
                }
                break;
            case 'last_run':
                $last_run = wpcp_get_post_meta($post_ID, '_last_run', 0);
                if( $last_run ){
                    echo date_i18n( get_option( 'date_format' ) .' '. get_option('time_format'), $last_run );
                }else{
                    echo ' - ';
                }
                break;
        }
    }
}
