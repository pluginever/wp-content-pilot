<?php
namespace Pluginever\WPCP\Core;

use Pluginever\WPCP\Traits\Hooker;

class CPT{
    use Hooker;
    public function __construct() {
        $this->action( 'init', 'register_custom_posts' );
        $this->action( 'manage_wp_content_pilot_posts_columns', 'wp_content_pilot_columns', 10 );
    }

    public function register_custom_posts(){
        $labels = array(
            'name'               => __( 'Campaigns', 'wpcp' ),
            'all_items'          => __( 'All Campaigns', 'wpcp' ),
            'singular_name'      => 'wp_content_pilot',
            'add_new'            => __( 'New campaign', 'wpcp' ),
            'add_new_item'       => __( 'Add New Campaign', 'wpcp' ),
            'edit_item'          => __( 'Edit Campaign', 'wpcp' ),
            'new_item'           => __( 'New Campaign', 'wpcp' ),
            'view_item'          => __( 'View Campaign', 'wpcp' ),
            'search_items'       => __( 'Search Campaigns', 'wpcp' ),
            'not_found'          => __( 'No Campaigns found', 'wpcp' ),
            'not_found_in_trash' => __( 'No Campaigns found in Trash', 'wpcp' ),
            'parent_item_colon'  => __( 'Parent Campaign:', 'wpcp' ),
            'menu_name'          => 'WP Content Pilot',
        );

        $args = array(
            'labels'              => $labels,
            'hierarchical'        => false,
            'description'         => __( 'Campaigns of Wordpress Content Pilot', 'wpcp' ),
            'supports'            => array( 'title' ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'menu_position'       => 100,
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
        $columns['status']    = __( 'Status', 'wpcp' );
        $columns['type']      = __( 'Type', 'wpcp' );
        $columns['goal']      = __( 'Goal', 'wpcp' );
        $columns['frequency'] = __( 'Frequency', 'wpcp' );
        $columns['last_run']  = __( 'Last Run', 'wpcp' );

        return $columns;
    }
}
