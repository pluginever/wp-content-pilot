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

	add_meta_box( 'campaign-template-tags', __( 'Template Tags', 'wp-content-pilot' ), 'wpcp_campaign_template_tags_metabox_callback', 'wp_content_pilot', 'side', 'low' );
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
	require WPCP_VIEWS . '/metabox/action-metabox.php';
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
		'double_columns'   => true,
		'options'          => wpcp_get_modules(),
		'required'         => true,
		'selected'         => wpcp_get_post_meta( $post->ID, '_campaign_type', 'feed' ),
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
function wpcp_campaign_options_metabox_fields( $post_id, $campaign_type = 'article' ) {

	do_action( 'wpcp_before_campaign_keyword_input', $post_id, $campaign_type );

	$keywords = wpcp_get_post_meta( $post_id, '_keywords', '' );

	$keyword_input_args = apply_filters( 'wpcp_campaign_keyword_input_args', array(
		'label'    => __( 'Keywords', 'wp-content-pilot' ),
		'name'     => '_keywords',
		'required' => true,
		'desc'     => __( 'Separate keywords by comma.', 'wp-content-pilot' ),
		'value'    => $keywords
	), $post_id, $campaign_type );

	echo apply_filters( 'wpcp_campaign_keyword_input', content_pilot()->elements->textarea( $keyword_input_args ), $post_id, $campaign_type );

	do_action( 'wpcp_after_campaign_keyword_input', $post_id, $campaign_type );

	$content_type_select_args = apply_filters( 'wpcp_campaign_content_type_select_args', array(
		'label'            => __( 'Content Type', 'wp-content-pilot' ),
		'name'             => '_content_type',
		'selected'         => wpcp_get_post_meta( $post_id, '_content_type', 'html' ),
		'show_option_all'  => '',
		'show_option_none' => '',
		'double_columns'   => true,
		'options'          => array(
			'html'  => __( 'HTML', 'wp-content-pilot' ),
			'plain' => __( 'Plain Text', 'wp-content-pilot' ),
		),
		'required'         => false,
	), $campaign_type );

	echo apply_filters( 'wpcp_campaign_content_type_select_field', content_pilot()->elements->select( $content_type_select_args ), $campaign_type );

	$additional_settings   = [];
	$_set_featured_image   = get_post_meta( $post_id, '_set_featured_image', true );
	$_remove_images        = get_post_meta( $post_id, '_remove_images', true );
	$_excerpt              = get_post_meta( $post_id, '_excerpt', true );
	$_strip_links          = get_post_meta( $post_id, '_strip_links', true );
	$_skip_duplicate_title = get_post_meta( $post_id, '_skip_duplicate_title', true );
	$_allow_comments       = get_post_meta( $post_id, '_allow_comments', true );
	$_allow_pingbacks      = get_post_meta( $post_id, '_allow_pingbacks', true );
	$_skip_no_image        = get_post_meta( $post_id, '_skip_no_image', true );
	$_use_original_date    = get_post_meta( $post_id, '_use_original_date', true );

	if ( 'on' == $_set_featured_image ) {
		$additional_settings[] = '_set_featured_image';
	}
	if ( 'on' == $_remove_images ) {
		$additional_settings[] = '_remove_images';
	}
	if ( 'on' == $_excerpt ) {
		$additional_settings[] = '_excerpt';
	}
	if ( 'on' == $_strip_links ) {
		$additional_settings[] = '_strip_links';
	}
	if ( 'on' == $_skip_duplicate_title ) {
		$additional_settings[] = '_skip_duplicate_title';
	}
	if ( 'on' == $_allow_comments ) {
		$additional_settings[] = '_allow_comments';
	}
	if ( 'on' == $_allow_pingbacks ) {
		$additional_settings[] = '_allow_pingbacks';
	}
	if ( 'on' == $_skip_no_image ) {
		$additional_settings[] = '_skip_no_image';
	}

	if ( 'on' == $_use_original_date ) {
		$additional_settings[] = '_use_original_date';
	}


	$additional_settings_args = apply_filters( 'wpcp_campaign_additional_settings_field_args', array(
		'label'            => __( 'Additional Settings', 'wp-content-pilot' ),
		'value'            => $additional_settings,
		'double_columns'   => true,
		'show_option_all'  => '',
		'show_option_none' => '',
		'options'          => array(
			'_set_featured_image'   => __( 'Use first image as featured image', 'wp-content-pilot' ),
			'_remove_images'        => __( 'Remove all images from the article', 'wp-content-pilot' ),
			'_excerpt'              => __( 'Use summary as excerpt', 'wp-content-pilot' ),
			'_strip_links'          => __( 'Remove hyperlinks found in the article', 'wp-content-pilot' ),
			'_allow_comments'       => __( 'Allow comments', 'wp-content-pilot' ),
			'_allow_pingbacks'      => __( 'Allow pingbacks', 'wp-content-pilot' ),
			'_use_original_date'    => __( 'Use original post date', 'wp-content-pilot' ),
			'_skip_duplicate_title' => __( 'Skip post with duplicate title', 'wp-content-pilot' ),
			'_skip_no_image'        => __( 'Skip post if no image found in the article', 'wp-content-pilot' ),
		),
		'required'         => false,
	), $campaign_type );

	echo apply_filters( 'wpcp_campaign_additional_settings_field', content_pilot()->elements->checkboxes( $additional_settings_args ), $campaign_type );


	do_action( 'wpcp_campaign_options_metabox', $post_id );
}


function wpcp_campaign_post_settings_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_settings_metabox_fields( $post->ID, $campaign_type );
}

function wpcp_campaign_settings_metabox_fields( $post_id, $campaign_type ) {

	echo content_pilot()->elements->input( array(
		'label'          => __( 'Post Title', 'wp-content-pilot' ),
		'name'           => '_post_title',
		'required'       => true,
		'double_columns' => true,
		'value'          => wpcp_get_post_meta( $post_id, '_post_title', '{title}' )
	) );

	$template_input_args = apply_filters( 'wpcp_campaign_template_input_args', array(
		'label'    => __( 'Post Template', 'wp-content-pilot' ),
		'name'     => '_post_template',
		'required' => true,
		'value'    => wpcp_get_post_meta( $post_id, '_post_template', '{content} <br> <a href="{source_url}" target="_blank">Source</a>' )
	), $post_id, $campaign_type );

	echo apply_filters( 'wpcp_campaign_template_input', content_pilot()->elements->textarea( $template_input_args ), $post_id, $campaign_type );

}

function wpcp_campaign_advance_settings_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_advance_settings_metabox_fields( $post->ID, $campaign_type );
}

function wpcp_campaign_advance_settings_metabox_fields( $post_id, $campaign_type ) {
	echo content_pilot()->elements->input( array(
		'label' => __( 'Limit Title', 'wp-content-pilot' ),
		'type'  => 'number',
		'name'  => '_title_limit',
		'value' => wpcp_get_post_meta( $post_id, '_title_limit', '' ),
		'desc'  => 'Input the number of word to limit title. Default full title.',
	) );

	echo content_pilot()->elements->input( array(
		'label' => __( 'Limit Content', 'wp-content-pilot' ),
		'type'  => 'number',
		'name'  => '_content_limit',
		'value' => wpcp_get_post_meta( $post_id, '_content_limit', '' ),
		'desc'  => 'Input the number of word to limit content. Default full content.',
	) );
}

function wpcp_campaign_template_tags_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_template_tags_metabox_fields( $post->ID, $campaign_type );
}

function wpcp_campaign_template_tags_metabox_fields( $post_id, $campaign_type ) {
	$module = content_pilot()->modules->get_module( $campaign_type );
	if ( empty( $module ) || is_wp_error( $module ) ) {
		return new WP_Error( 'invalid-module-type', __( 'Invalid module type' ) );
	}

	$tags = $module['callback']::get_template_tags();
	?>
	<table class="fixed striped widefat">
		<tr>
			<th><?php _e( 'Tag', 'wp-content-pilot' ); ?></th>
			<th><?php _e( 'Description', 'wp-content-pilot' ); ?></th>
		</tr>
		<?php foreach ( $tags as $tag => $description ): ?>
			<tr>
				<td><code>{<?php echo esc_html( $tag ); ?>}</code></td>
				<td><?php echo esc_html( $description ); ?> </td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php

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
	update_post_meta( $post_id, '_campaign_type', empty( $posted['_campaign_type'] ) ? 'feed' : esc_attr( $posted['_campaign_type'] ) );
	update_post_meta( $post_id, '_campaign_target', empty( $posted['_campaign_target'] ) ? '' : intval( $posted['_campaign_target'] ) );
	update_post_meta( $post_id, '_campaign_frequency', empty( $posted['_campaign_frequency'] ) ? '' : intval( $posted['_campaign_frequency'] ) );
	update_post_meta( $post_id, '_campaign_status', empty( $posted['_campaign_status'] ) ? 'inactive' : esc_attr( $posted['_campaign_status'] ) );

	update_post_meta( $post_id, '_keywords', empty( $posted['_keywords'] ) ? '' : esc_attr( $posted['_keywords'] ) );
	update_post_meta( $post_id, '_content_type', empty( $posted['_content_type'] ) ? 'html' : esc_attr( $posted['_content_type'] ) );

	update_post_meta( $post_id, '_set_featured_image', empty( $posted['_set_featured_image'] ) ? '' : esc_attr( $posted['_set_featured_image'] ) );
	update_post_meta( $post_id, '_remove_images', empty( $posted['_remove_images'] ) ? '' : esc_attr( $posted['_remove_images'] ) );
	update_post_meta( $post_id, '_excerpt', empty( $posted['_excerpt'] ) ? '' : esc_attr( $posted['_excerpt'] ) );
	update_post_meta( $post_id, '_strip_links', empty( $posted['_strip_links'] ) ? '' : esc_attr( $posted['_strip_links'] ) );
	update_post_meta( $post_id, '_allow_comments', empty( $posted['_allow_comments'] ) ? '' : esc_attr( $posted['_allow_comments'] ) );
	update_post_meta( $post_id, '_allow_pingbacks', empty( $posted['_allow_pingbacks'] ) ? '' : esc_attr( $posted['_allow_pingbacks'] ) );
	update_post_meta( $post_id, '_use_original_date', empty( $posted['_use_original_date'] ) ? '' : esc_attr( $posted['_use_original_date'] ) );
	update_post_meta( $post_id, '_skip_no_image', empty( $posted['_skip_no_image'] ) ? '' : esc_attr( $posted['_skip_no_image'] ) );
	update_post_meta( $post_id, '_skip_duplicate_title', empty( $posted['_skip_duplicate_title'] ) ? '' : esc_attr( $posted['_skip_duplicate_title'] ) );

	update_post_meta( $post_id, '_post_title', empty( $posted['_post_title'] ) ? '' : esc_attr( $posted['_post_title'] ) );
	update_post_meta( $post_id, '_post_template', empty( $posted['_post_template'] ) ? '' : wp_kses_post( $posted['_post_template'] ) );

	update_post_meta( $post_id, '_title_limit', empty( $posted['_title_limit'] ) ? '' : esc_attr( $posted['_title_limit'] ) );
	update_post_meta( $post_id, '_content_limit', empty( $posted['_content_limit'] ) ? '' : esc_attr( $posted['_content_limit'] ) );

	do_action( 'wpcp_update_campaign_settings', $post_id, $posted );
}

add_action( 'save_post_wp_content_pilot', 'wpcp_update_campaign_settings' );

