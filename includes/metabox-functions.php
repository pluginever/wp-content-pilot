<?php
/**
 * Metabox Functions
 *
 * @package     WP Content Pilot
 * @subpackage  metabox
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register metabox
 *
 * @since 1.0.0
 */
function wpcp_register_meta_boxes() {
	add_meta_box( 'campaign-type-selection', __( 'Campaign Type', 'wp-content-pilot' ), 'wpcp_campaign_type_metabox_callback', 'wp_content_pilot', 'normal', 'high' );
	add_meta_box( 'campaign-actions', __( 'Actions', 'wp-content-pilot' ), 'wpcp_campaign_action_metabox_callback', 'wp_content_pilot', 'side', 'high' );
	add_meta_box( 'campaign-options', __( 'Campaign Options', 'wp-content-pilot' ), 'wpcp_campaign_options_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'campaign-post-settings', __( 'Post Settings', 'wp-content-pilot' ), 'wpcp_campaign_post_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'campaign-advanced-settings', __( 'Advanced Settings', 'wp-content-pilot' ), 'wpcp_campaign_advance_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
}

add_action( 'add_meta_boxes', 'wpcp_register_meta_boxes', 99 );

/**
 * remove metaboxes
 *
 * @since 1.0.0
 */
function wpcp_remove_meta_boxes() {
	$post_type = 'wp_content_pilot';

	remove_meta_box( 'submitdiv', $post_type, 'side' );
	remove_meta_box( 'commentsdiv', $post_type, 'normal' );
	remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
	remove_meta_box( 'slugdiv', $post_type, 'normal' );
}

add_action( 'add_meta_boxes', 'wpcp_remove_meta_boxes', 10 );

/**
 * campaign actions
 *
 * @since 1.0.0
 *
 * @param $post
 */
function wpcp_campaign_action_metabox_callback( $post ) {


	echo content_pilot()->elements->select( array(
		'label'    => __( 'Campaign Frequency', 'wp-content-pilot' ),
		'name'     => '_campaign_status',
		'options'  => array(),
		'required' => true,
		'selected' => '',
	) );


	echo content_pilot()->elements->input( array(
		'label'       => __( 'Campaign Target', 'wp-content-pilot' ),
		'name'        => '_campaign_target',
		'type'        => 'number',
		'placeholder' => '10',
		'required'    => true,
	) );

	echo content_pilot()->elements->select( array(
		'label'    => __( 'Campaign Status', 'wp-content-pilot' ),
		'name'     => '_campaign_status',
		'options'  => array(),
		'required' => true,
		'selected' => '',
	) );

	echo content_pilot()->elements->select( array(
		'label'    => __( 'Action', 'wp-content-pilot' ),
		'name'     => 'action',
		'options'  => array(
			'update'   => __( 'Update', 'wp-content-pilot' ),
			'test_run' => __( 'Test Run', 'wp-content-pilot' ),
		),
		'required' => false,
		'selected' => 'update',
	) );

	echo '<button type="submit" class="button button-primary">' . __( 'Submit', 'wp-content-pilot' ) . '</button>';
}

/**
 * Render
 *
 * @since 1.2.0
 *
 * @param $post
 */
function wpcp_campaign_type_metabox_callback( $post ) {
	echo content_pilot()->elements->select( array(
		'label'            => __( 'Campaign Type', 'wp-content-pilot' ),
		'name'             => '_campaign_type',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'options'          => array(
			'articles' => 'Articles',
			'feeds'    => 'Feeds',
		),
		'required'         => true,
		'selected'         => 'feeds',
		'desc'             => __( 'Select Campaign type, depending your need', 'wp-content-pilot' ),
	) );
}

/**
 * Campaign options metabox
 *
 * @since 1.0.0
 *
 * @param $post
 */
function wpcp_campaign_options_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_options_metabox_fields( $post->ID, $campaign_type );

}

/**
 * campaign options metabox fields
 *
 * @since 1.0.0
 *
 * @param        $post_id
 * @param string $campaign_type
 */
function wpcp_campaign_options_metabox_fields( $post_id, $campaign_type = 'feeds' ) {

	do_action( 'wpcp_before_campaign_keyword_input', $post_id, $campaign_type );

	$keyword_input_args = apply_filters( 'wpcp_campaign_keyword_input_args', array(
		'label'    => __( 'Keywords', 'wp-content-pilot' ),
		'name'     => '_keywords',
		'required' => true,
	), $campaign_type );

	echo apply_filters( 'wpcp_campaign_keyword_input', content_pilot()->elements->textarea( $keyword_input_args ), $campaign_type );

	do_action( 'wpcp_after_campaign_keyword_input', $post_id, $campaign_type );

	$content_type_select_args = apply_filters( 'wpcp_campaign_content_type_select_args', array(
		'label'          => __( 'Content Type', 'wp-content-pilot' ),
		'name'           => '_content_type',
		'selected'       => 'html',
		'double_columns' => true,
		'options'        => array(
			'html'  => __( 'HTML', 'wp-content-pilot' ),
			'plain' => __( 'Plain Text', 'wp-content-pilot' ),
		),
		'required'       => false,
	), $campaign_type );

	echo apply_filters( 'wpcp_campaign_content_type_select_field', content_pilot()->elements->select( $content_type_select_args ), $campaign_type );

	$additional_settings_args = apply_filters( 'wpcp_campaign_additional_settings_field_args', array(
		'label'          => __( 'Additional Settings', 'wp-content-pilot' ),
		'selected'       => 'html',
		'double_columns' => true,
		'options'        => array(
			'_set_featured_image'   => __( 'Use first image as featured image', 'wp-content-pilot' ),
			'_remove_images'        => __( 'Remove all images from the article', 'wp-content-pilot' ),
			'_excerpt'              => __( 'Use summary as excerpt', 'wp-content-pilot' ),
			'_strip_links'          => __( 'Remove hyperlinks found in the article', 'wp-content-pilot' ),
			'_skip_duplicate_title' => __( 'Skip post with duplicate title', 'wp-content-pilot' ),
			'_allow_comments'       => __( 'Allow comments', 'wp-content-pilot' ),
			'_allow_pingbacks'      => __( 'Allow Pingbacks', 'wp-content-pilot' ),
		),
		'required'       => false,
	), $campaign_type );

	echo apply_filters( 'wpcp_campaign_additional_settings_field', content_pilot()->elements->checkboxes( $additional_settings_args ), $campaign_type );


	do_action( 'wpcp_campaign_options_metabox', $post_id );
}


function wpcp_campaign_post_settings_metabox_callback( $post ) {

}

function wpcp_campaign_advance_settings_metabox_callback( $post ) {

}
