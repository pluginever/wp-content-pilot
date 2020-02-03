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

defined( 'ABSPATH' ) || exit();


/**
 * Register metabox
 *
 * @since 1.0.0
 */
function wpcp_register_meta_boxes() {
	add_meta_box( 'campaign-selection', __( 'Select a campaign to start', 'wp-content-pilot' ), 'wpcp_campaign_selection_metabox_callback', 'wp_content_pilot', 'normal', 'high' );
	add_meta_box( 'wpcp-campaign-status', __( 'Campaign Status', 'wp-content-pilot' ), 'wpcp_campaign_status_metabox_callback', 'wp_content_pilot', 'normal', 'high' );
	add_meta_box( 'wpcp-campaign-options', __( 'Campaign Options', 'wp-content-pilot' ), 'wpcp_campaign_options_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-template', __( 'Post Template', 'wp-content-pilot' ), 'wpcp_post_template_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-settings', __( 'Post Settings', 'wp-content-pilot' ), 'wpcp_post_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-filters', __( 'Posts Filter', 'wp-content-pilot' ), 'wpcp_posts_filter_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-advanced-settings', __( 'Advanced Settings', 'wp-content-pilot' ), 'wpcp_advanced_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-campaign-actions', __( 'Actions', 'wp-content-pilot' ), 'wpcp_campaign_action_metabox_callback', 'wp_content_pilot', 'side', 'high' );
}

add_action( 'add_meta_boxes', 'wpcp_register_meta_boxes', 10 );

/**
 * Conditionally render metabox
 *
 * @param $post_type
 * @param $context
 * @param $post
 */
function wpcp_conditional_metabox_remove( $post_type, $context, $post ) {
	$post_type = 'wp_content_pilot';
	remove_meta_box( 'submitdiv', 'wp_content_pilot', 'side' );
	remove_meta_box( 'commentsdiv', 'wp_content_pilot', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'wp_content_pilot', 'normal' );
	remove_meta_box( 'slugdiv', 'wp_content_pilot', 'normal' );

	if ( $post_type !== 'wp_content_pilot' ) {
		return false;
	}
	if ( isset($post->ID) && empty( get_post_meta( $post->ID, '_campaign_type', true ) ) ) {
		remove_meta_box( 'wpcp-campaign-status', $post_type, 'normal' );
		remove_meta_box( 'wpcp-campaign-options', $post_type, 'normal' );
		remove_meta_box( 'wpcp-post-template', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-post-settings', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-post-filters', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-advanced-settings', 'wp_content_pilot', 'normal' );
	} else {
		remove_meta_box( 'campaign-selection', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-campaign-actions', 'wp_content_pilot', 'normal' );
	}
}

add_action( 'do_meta_boxes', 'wpcp_conditional_metabox_remove', 10, 3 );

/**
 * Campaign selection metabox
 *
 * @param $post
 */
function wpcp_campaign_selection_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-selection.php', array( 'post' => $post ) );
}

/**
 * Render campaign status
 *
 * @param $post
 */
function wpcp_campaign_status_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-status.php', array( 'post' => $post ) );
}

function wpcp_campaign_options_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	do_action( 'wpcp_campaign_options_meta_fields', $campaign_type, $post );
	do_action( 'wpcp_campaign_' . esc_attr__( $campaign_type ) . '_options_meta_fields', $post );
}

function wpcp_post_template_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-template.php' );
}

function wpcp_post_settings_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-settings.php' );
}

function wpcp_posts_filter_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-filter.php' );
}

function wpcp_advanced_settings_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/advanced-settings.php' );
}

function wpcp_campaign_action_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/action-metabox.php' );
}


function wpcp_update_campaign_settings( $post_id ) {
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	//save post meta
	$posted = empty( $_POST ) ? [] : $_POST;
	if ( empty( $posted['_campaign_type'] ) ) {
		return false;
	}

	$frequency_unit = ! empty( $posted['_frequency_unit'] ) && in_array( $posted['_frequency_unit'], [
		'minutes',
		'hours',
		'days'
	] ) ? sanitize_key( $posted['_frequency_unit'] ) : 'hours';

	if ( $frequency_unit == 'minutes' ) {
		$every = MINUTE_IN_SECONDS;
	} elseif ( $frequency_unit == 'hours' ) {
		$every = HOUR_IN_SECONDS;
	} else {
		$every = DAY_IN_SECONDS;
	}


	$campaign_target    = empty( $posted['_campaign_target'] ) ? 10 : absint( $posted['_campaign_target'] );
	$campaign_frequency = empty( $posted['_campaign_frequency'] ) ? 5 : absint( $posted['_campaign_frequency'] );

	update_post_meta( $post_id, '_campaign_type', empty( $posted['_campaign_type'] ) ? 'feed' : sanitize_text_field( $posted['_campaign_type'] ) );
	update_post_meta( $post_id, '_campaign_target', $campaign_target );
	update_post_meta( $post_id, '_campaign_frequency', $campaign_frequency );
	update_post_meta( $post_id, '_frequency_unit', $frequency_unit );
	update_post_meta( $post_id, '_run_every', ( $every * absint( $posted['_campaign_frequency'] ) ) );
	update_post_meta( $post_id, '_campaign_status', empty( $posted['_campaign_status'] ) ? 'inactive' : sanitize_text_field( $posted['_campaign_status'] ) );

	update_post_meta( $post_id, '_keywords', empty( $posted['_keywords'] ) ? '' : sanitize_text_field( $posted['_keywords'] ) );
	update_post_meta( $post_id, '_content_type', empty( $posted['_content_type'] ) ? 'html' : sanitize_text_field( $posted['_content_type'] ) );

	update_post_meta( $post_id, '_set_featured_image', empty( $posted['_set_featured_image'] ) ? '' : sanitize_text_field( $posted['_set_featured_image'] ) );
	update_post_meta( $post_id, '_remove_images', empty( $posted['_remove_images'] ) ? '' : sanitize_text_field( $posted['_remove_images'] ) );
	update_post_meta( $post_id, '_excerpt', empty( $posted['_excerpt'] ) ? '' : sanitize_text_field( $posted['_excerpt'] ) );
	update_post_meta( $post_id, '_strip_links', empty( $posted['_strip_links'] ) ? '' : sanitize_text_field( $posted['_strip_links'] ) );
	update_post_meta( $post_id, '_allow_comments', empty( $posted['_allow_comments'] ) ? '' : sanitize_text_field( $posted['_allow_comments'] ) );
	update_post_meta( $post_id, '_allow_pingbacks', empty( $posted['_allow_pingbacks'] ) ? '' : sanitize_text_field( $posted['_allow_pingbacks'] ) );
	update_post_meta( $post_id, '_use_original_date', empty( $posted['_use_original_date'] ) ? '' : sanitize_text_field( $posted['_use_original_date'] ) );
	update_post_meta( $post_id, '_skip_no_image', empty( $posted['_skip_no_image'] ) ? '' : sanitize_text_field( $posted['_skip_no_image'] ) );
	update_post_meta( $post_id, '_skip_duplicate_title', empty( $posted['_skip_duplicate_title'] ) ? '' : sanitize_text_field( $posted['_skip_duplicate_title'] ) );

	update_post_meta( $post_id, '_post_title', empty( $posted['_post_title'] ) ? '' : sanitize_text_field( $posted['_post_title'] ) );
	update_post_meta( $post_id, '_post_template', empty( $posted['_post_template'] ) ? '' : wp_kses_post( $posted['_post_template'] ) );
	update_post_meta( $post_id, '_post_type', empty( $posted['_post_type'] ) ? 'post' : wp_kses_post( $posted['_post_type'] ) );
	update_post_meta( $post_id, '_post_status', empty( $posted['_post_status'] ) ? 'publish' : wp_kses_post( $posted['_post_status'] ) );
	update_post_meta( $post_id, '_author', empty( $posted['_author'] ) ? '' : intval( $posted['_author'] ) );
	update_post_meta( $post_id, '_categories', empty( $posted['_categories'] ) ? '' : $posted['_categories'] );
	update_post_meta( $post_id, '_tags', empty( $posted['_tags'] ) ? '' : $posted['_tags'] );

	update_post_meta( $post_id, '_title_limit', empty( $posted['_title_limit'] ) ? '' : esc_attr( $posted['_title_limit'] ) );
	update_post_meta( $post_id, '_content_limit', empty( $posted['_content_limit'] ) ? '' : esc_attr( $posted['_content_limit'] ) );
	do_action( 'wpcp_update_campaign_settings', $post_id, $posted );
	do_action( 'wpcp_update_campaign_settings_' . esc_attr__( $posted['_campaign_type'] ), $post_id, $posted );
}

add_action( 'save_post_wp_content_pilot', 'wpcp_update_campaign_settings' );


function wpcp_keyword_field() {
	echo WPCP_HTML::textarea_input( array(
		'label'       => __( 'Keywords', 'wp-content-pilot' ),
		'name'        => '_keywords',
		'placeholder' => 'Bonsai tree care',
		'desc'        => __( 'Separate keywords by comma.', 'wp-content-pilot' ),
		'attrs'       => array(
			'rows'     => 3,
			'required' => 'required'
		),
	) );
}

function wpcp_keyword_suggestion_field() {
	echo WPCP_HTML::text_input( array(
		'label'         => __( 'Keyword Suggestion', 'wp-content-pilot' ),
		'name'          => '_keyword_suggestion',
		'placeholder'   => 'Enter Keyword Here',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}


function wpcp_strip_links_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'   => __( 'Strip original links from the post', 'wp-content-pilot' ),
		'name'    => '_strip_links',
		'tooltip' => __( 'Remove hyperlinks found in the article', 'wp-content-pilot' ),
	) );
}

function wpcp_external_link_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Make permalink link directly to the source', 'wp-content-pilot' ),
		'name'          => '_external_post',
		'tooltip'       => __( 'Make post link directly to the source site, Posts will not load at your site.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_canonical_link_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Add Canonical Tag with the original post link ', 'wp-content-pilot' ),
		'name'          => '_canonical_tag',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_featured_image_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Set First image as featured image', 'wp-content-pilot' ),
		'name'  => '_set_featured_image',
	) );
}

function wpcp_featured_image_random_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Set random featured image if no image exists', 'wp-content-pilot' ),
		'name'          => '_random_featured_image',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_use_original_date_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Use original date if possible', 'wp-content-pilot' ),
		'name'          => '_use_original_date',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_use_excerpt_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Use summary as excerpt', 'wp-content-pilot' ),
		'name'          => '_excerpt',
	) );
}

