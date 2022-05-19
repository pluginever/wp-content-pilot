<?php
/**
 * Post Type Functions
 *
 * @package     WP Content Pilot
 * @subpackage  Functions
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

defined( 'ABSPATH' ) || exit();
/**
 * Register our content pilot post type.
 * since 1.0.0
 */
function wpcp_setup_wpcp_post_types() {
	$campaign_labels = array(
		'name'                  => _x( 'Campaigns', 'campaign post type name', 'wp-content-pilot' ),
		'singular_name'         => _x( 'Campaign', 'singular campaign post type name', 'wp-content-pilot' ),
		'add_new'               => __( 'Add New', 'wp-content-pilot' ),
		'add_new_item'          => __( 'Add New Campaign', 'wp-content-pilot' ),
		'edit_item'             => __( 'Edit Campaign', 'wp-content-pilot' ),
		'new_item'              => __( 'New Campaign', 'wp-content-pilot' ),
		'all_items'             => __( 'All Campaigns', 'wp-content-pilot' ),
		'view_item'             => __( 'View Campaign', 'wp-content-pilot' ),
		'search_items'          => __( 'Search Campaigns', 'wp-content-pilot' ),
		'not_found'             => __( 'No Campaigns found', 'wp-content-pilot' ),
		'not_found_in_trash'    => __( 'No Campaigns found in Trash', 'wp-content-pilot' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( 'Content Pilot', 'wp content pilot post type menu name', 'wp-content-pilot' ),
		'attributes'            => __( 'Campaign Attributes', 'wp-content-pilot' ),
		'filter_items_list'     => __( 'Filter Campaigns list', 'wp-content-pilot' ),
		'items_list_navigation' => __( 'Campaigns list navigation', 'wp-content-pilot' ),
		'items_list'            => __( 'Campaigns list', 'wp-content-pilot' ),
	);
	$campaign_args   = array(
		'labels'             => $campaign_labels,
		'description'        => __( 'Campaigns of Wordpress Content Pilot', 'wp-content-pilot' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'menu_icon'          => WPCP_ASSETS_URL . '/images/icons/logo.svg',
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title' ),
	);
	register_post_type( 'wp_content_pilot', apply_filters( 'wpcp_campaign_post_type_args', $campaign_args ) );//phpcs:ignore
}

add_action( 'init', 'wpcp_setup_wpcp_post_types', 1 );

/**
 * Change default post types message.
 * since 1.0.0
 *
 * @param $messages array
 *
 * @return mixed
 */
function wpcp_post_types_messages( $messages ) {
	global $post, $post_ID;

	$messages['wp_content_pilot'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Campaign updated.', 'wp-content-pilot' ),
		2  => __( 'Custom field updated.', 'wp-content-pilot' ),
		3  => __( 'Custom field deleted.', 'wp-content-pilot' ),
		4  => __( 'Campaign updated.', 'wp-content-pilot' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Campaign restored to revision from %s', 'wp-content-pilot' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Campaign updated.', 'wp-content-pilot' ),
		7  => __( 'Campaign saved.', 'wp-content-pilot' ),
		8  => __( 'Campaign submitted.', 'wp-content-pilot' ),
		9  => sprintf( __( 'Campaign scheduled for: <strong>%1$s</strong>.', 'wp-content-pilot' ),
			// translators: Publish box date format, see http://php.net/date.
			date_i18n( __( 'M j, Y @ G:i', 'wp-content-pilot' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => __( 'Campaign draft updated.', 'wp-content-pilot' ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'wpcp_post_types_messages' );
/**
 * Change default "Enter title here" input
 *
 * @param string $title Default title placeholder text
 *
 * @return string $title New placeholder text
 * @since 1.0.0
 *
 */
function wpcp_change_default_title( $title ) {
	$screen = get_current_screen();

	if ( 'wp_content_pilot' == $screen->post_type ) {
		$title = __( 'Campaign Title', 'wp-content-pilot' );
	}

	return $title;
}
add_filter( 'enter_title_here', 'wpcp_change_default_title' );

/**
 * Admin column
 * since 1.0.0
 * @param $columns
 *
 * @return mixed
 */
function wp_content_pilot_columns( $columns ) {
	unset( $columns['date'] );
	$columns['status']    = __( 'Status', 'wp-content-pilot' );
	$columns['type']      = __( 'Type', 'wp-content-pilot' );
	$columns['target']    = __( 'Posts/Target', 'wp-content-pilot' );
	$columns['frequency'] = __( 'Frequency', 'wp-content-pilot' );
	$columns['last_run']  = __( 'Last Run', 'wp-content-pilot' );

	return $columns;
}

add_action( 'manage_wp_content_pilot_posts_columns', 'wp_content_pilot_columns', 10 );

/**
 * Admin column content
 * since 1.0.0
 * @param $column_name
 * @param $post_ID
 */
function wp_content_pilot_column_content( $column_name, $post_ID ) {
	switch ( $column_name ) {
		case 'status':
			$active = wpcp_get_post_meta( $post_ID, '_campaign_status', 0 );
			if ( $active == 'active' ) {
				echo '<span style="background: green;color: #fff;padding: 2px 10px;box-shadow: 2px 2px 2px;">' . __( 'Active', 'wp-content-pilot' ) . '</span>';
			} else {
				echo '<span style="background: red;color: #fff;padding: 2px 10px;box-shadow: 2px 2px 2px;">' . __( 'Disabled', 'wp-content-pilot' ) . '</span>';
			}
			break;
		case 'type':
			$campaign_type = wpcp_get_post_meta( $post_ID, '_campaign_type' );
			echo ucfirst( str_replace('_', ' ', $campaign_type) );
			break;
		case 'target':
			$target    = wpcp_get_post_meta( $post_ID, '_campaign_target', 0 );
			$completed = wpcp_get_post_meta( $post_ID, '_post_count', 0 );
			if ( $target && $completed ) {
				echo $completed . '/' . $target;
			} else {
				echo $target . '/ - ';
			}
			break;
		case 'frequency':
			$frenquency      = wpcp_get_post_meta( $post_ID, '_campaign_frequency', 0 );
			$frenquency_unit = wpcp_get_post_meta( $post_ID, '_frequency_unit', 'hour' );

			if ( $frenquency ) {
				echo sprintf( 'Every %d %s', $frenquency, $frenquency_unit );
			} else {
				echo ' - ';
			}
			break;
		case 'last_run':
			$last_run = wpcp_get_post_meta( $post_ID, '_last_run', 0 );
			if ( $last_run ) {
				echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime($last_run) );
			} else {
				echo ' - ';
			}
			break;
	}
}

add_action( 'manage_wp_content_pilot_posts_custom_column', 'wp_content_pilot_column_content', 10, 2 );
