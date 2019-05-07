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
	add_meta_box( 'campaign-posted-posts', __( 'Posted Posts', 'wp-content-pilot' ), 'wpcp_campaign_posted_posts_metabox_callback', 'wp_content_pilot', 'side', 'low' );
	add_meta_box( 'campaign-logs', __( 'Logs', 'wp-content-pilot' ), 'wpcp_campaign_logs_metabox_callback', 'wp_content_pilot', 'side', 'low' );
	add_meta_box( 'campaign-meta-actions', __( 'Actions', 'wp-content-pilot' ), 'wpcp_campaign_meta_actions_metabox_callback', 'wp_content_pilot', 'side', 'low' );
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
 * @param $post
 *
 * @since 1.0.0
 *
 */
function wpcp_campaign_action_metabox_callback( $post ) {
	require WPCP_VIEWS . '/metabox/action-metabox.php';
}

/**
 * Render
 *
 * @param $post
 *
 * @since 1.2.0
 *
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
 * @param $post
 *
 * @since 1.0.0
 *
 */
function wpcp_campaign_options_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_options_metabox_fields( $post->ID, $campaign_type );

}

/**
 * campaign options metabox fields
 *
 * @param        $post_id
 * @param string $campaign_type
 *
 * @since 1.0.0
 *
 */
function wpcp_campaign_options_metabox_fields( $post_id, $campaign_type = 'article' ) {
	do_action( 'wpcp_before_campaign_keyword_input', $post_id, $campaign_type );
	if ( in_array( $campaign_type, wpcp_get_keyword_suggestion_supported_modules() ) ) {
		echo content_pilot()->elements->input( apply_filters( 'wpcp_keyword_suggester_input_args', array(
			'label'       => __( 'Keyword Suggester', 'wp-content-pilot' ),
			'name'        => '_keyword_suggestion',
			'placeholder' => __( 'How to cook noddles', 'wp-content-pilot' ),
			'desc'        => __( 'Type something to find better related keywords', 'wp-content-pilot-pro' ),
			'disabled'    => true,
		) ) );
	}

	$keywords = wpcp_get_post_meta( $post_id, '_keywords', '' );

	$keyword_input_args = apply_filters( 'wpcp_campaign_keyword_input_args', array(
		'label'    => __( 'Keywords', 'wp-content-pilot' ),
		'name'     => '_keywords',
		'required' => true,
		'desc'     => __( 'Separate keywords by comma.', 'wp-content-pilot' ),
		'value'    => $keywords,
		'attrs'    => array(
			'rows' => 3,
		),
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
	), $campaign_type, $post_id );

	echo apply_filters( 'wpcp_campaign_additional_settings_field', content_pilot()->elements->checkboxes( $additional_settings_args ), $campaign_type );


	do_action( 'wpcp_campaign_options_metabox', $post_id );
}


function wpcp_campaign_post_settings_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_post_settings_metabox_fields( $post->ID, $campaign_type );
}

function wpcp_campaign_post_settings_metabox_fields( $post_id, $campaign_type ) {

	echo content_pilot()->elements->input( array(
		'label'    => __( 'Post Title', 'wp-content-pilot' ),
		'name'     => '_post_title',
		'required' => true,
		'value'    => wpcp_get_post_meta( $post_id, '_post_title', '{title}' )
	) );


	$post_template = wpcp_get_post_meta( $post_id, '_post_template', '' );
	if ( empty( $post_template ) ) {
		$module        = content_pilot()->modules->get_module( $campaign_type );
		$module_class  = $module['callback'];
		$post_template = $module_class::get_default_template();
	}


	$template_input_args = apply_filters( 'wpcp_campaign_template_input_args', array(
		'label'    => __( 'Post Template', 'wp-content-pilot' ),
		'name'     => '_post_template',
		'required' => true,
		'attrs'    => array(
			'rows' => 5,
		),
		'value'    => $post_template
	), $post_id, $campaign_type );


	echo apply_filters( 'wpcp_campaign_template_input', content_pilot()->elements->textarea( $template_input_args ), $post_id, $campaign_type );

	echo content_pilot()->elements->select( array(
		'label'            => __( 'Post type', 'wp-content-pilot' ),
		'name'             => '_post_type',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'double_columns'   => true,
		'options'          => apply_filters( 'wpcp_campaign_post_types', array(
			'post' => __( 'Post', 'wp-content-pilot' ),
			'page' => __( 'Page', 'wp-content-pilot' ),
		) ),
		'required'         => true,
		'selected'         => wpcp_get_post_meta( $post_id, '_post_type', 'post' ),
	) );

	echo content_pilot()->elements->select( array(
		'label'            => __( 'Status', 'wp-content-pilot' ),
		'name'             => '_post_status',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'double_columns'   => true,
		'options'          => apply_filters( 'wpcp_campaign_post_statuses', array(
			'publish' => __( 'Published', 'wp-content-pilot' ),
			'private' => __( 'Private', 'wp-content-pilot' ),
			'draft'   => __( 'Draft', 'wp-content-pilot' ),
			'pending' => __( 'Pending', 'wp-content-pilot' ),
		) ),
		'required'         => true,
		'multiple'         => false,
		'selected'         => wpcp_get_post_meta( $post_id, '_post_status', 'publish' ),
	) );

	echo content_pilot()->elements->select( array(
		'label'            => __( 'Categories', 'wp-content-pilot' ),
		'name'             => '_categories',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'selected'         => wpcp_get_post_meta( $post_id, '_categories', [] ),
		'options'          => wpcp_get_post_categories(),
		'required'         => false,
		'multiple'         => true,
		'chosen'           => true,
		'desc'             => __( 'Select categories from aviallbe categories', 'wp-content-pilot' ),
	) );

	echo content_pilot()->elements->input( apply_filters( 'wpcp_custom_categories_args', array(
		'label'       => __( 'Custom Categories', 'wp-content-pilot' ),
		'name'        => '_custom_categories',
		'placeholder' => __( 'Fashion, Sports, Tech', 'wp-content-pilot' ),
		'desc'        => __( 'Input any number of custom categories separate by comma (PRO) ', 'wp-content-pilot' ),
		'disabled'    => true,
	), $post_id ) );

	echo content_pilot()->elements->select( array(
		'label'            => __( 'Tags', 'wp-content-pilot' ),
		'name'             => '_tags',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'selected'         => wpcp_get_post_meta( $post_id, '_tags', [] ),
		'options'          => wpcp_get_post_tags(),
		'required'         => false,
		'multiple'         => true,
		'chosen'           => true,
		'desc'             => __( 'Select tags from aviallbe tags', 'wp-content-pilot' ),
	) );

	echo content_pilot()->elements->input( apply_filters( 'wpcp_custom_tags_args', array(
		'label'       => __( 'Custom Tags', 'wp-content-pilot' ),
		'name'        => '_custom_tags',
		'placeholder' => __( 'Fashion, Sports, Tech', 'wp-content-pilot' ),
		'desc'        => __( 'Input any number of custom tags separate by comma (PRO) ', 'wp-content-pilot' ),
		'disabled'    => true,
	), $post_id ) );

	echo content_pilot()->elements->select( array(
		'label'            => __( 'Post Author', 'wp-content-pilot' ),
		'name'             => '_author',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'double_columns'   => true,
		'options'          => wpcp_get_authors(),
		'required'         => true,
		'multiple'         => false,
		'chosen'           => false,
		'desc'             => __( 'Select categories from aviallbe categories', 'wp-content-pilot' ),
		'selected'         => wpcp_get_post_meta( $post_id, '_author', '' ),
	) );
}

function wpcp_campaign_advance_settings_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	$campaign_type = empty( $campaign_type ) ? 'feeds' : $campaign_type;
	wpcp_campaign_advance_settings_metabox_fields( $post->ID, $campaign_type );
}

function wpcp_campaign_advance_settings_metabox_fields( $post_id, $campaign_type ) {
	do_action( 'wpcp_start_campaign_advance_settings_metabox', $post_id, $campaign_type );
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

	echo content_pilot()->elements->input( apply_filters( 'wpcp_min_words_args', array(
		'label'       => __( 'Words Count', 'wp-content-pilot' ),
		'name'        => '_min_words',
		'type'        => 'number',
		'placeholder' => 500,
		'desc'        => __( 'Min Words required, otherwise post will be rejected. (PRO) ', 'wp-content-pilot' ),
		'disabled'    => true,
	), $post_id ) );

	echo content_pilot()->elements->input( apply_filters( 'wpcp_required_words_args', array(
		'label'       => __( 'Required Words', 'wp-content-pilot' ),
		'name'        => '_required_words',
		'placeholder' => __( 'Fashion, Secret, Awesome', 'wp-content-pilot' ),
		'desc'        => __( 'Must contain words, otherwise post will be rejected. (PRO) ', 'wp-content-pilot' ),
		'disabled'    => true,
	), $post_id ) );

	echo content_pilot()->elements->input( apply_filters( 'wpcp_banned_words_args', array(
		'label'       => __( 'Banned Words', 'wp-content-pilot' ),
		'name'        => '_banned_words',
		'placeholder' => __( 'youtube, wikipedia, google', 'wp-content-pilot' ),
		'desc'        => __( 'If contains above words post will be rejected. (PRO) ', 'wp-content-pilot' ),
		'disabled'    => true,
	), $post_id ) );

	echo content_pilot()->elements->select( apply_filters( 'wpcp_translate_to_args', array(
		'label'            => __( 'Translate To', 'wp-content-pilot' ),
		'name'             => '_translate_to',
		'placeholder'      => '',
		'show_option_all'  => '',
		'show_option_none' => '',
		'double_columns'   => true,
		'options'          => array(
			'' => __( 'No Translation', 'wp-content-pilot' )
		),
		'disabled'         => true,
		'multiple'         => false,
		'chosen'           => false,
		'desc'             => __( 'Select language to translate. (PRO)', 'wp-content-pilot' ),
		'selected'         => '',
	), $post_id ) );


	echo content_pilot()->elements->repeatable( apply_filters( 'wpcp_custom_meta_args', array(
		'label'    => __( 'Custom Meta (Pro)', 'wp-content-pilot' ),
		'name'     => '_wpcp_custom_meta_field',
		'desc'     => __( 'Add custom meta for posts. (PRO) ', 'wp-content-pilot' ),
		'disabled' => true,
		'fields'   => array(
			array(
				'name'        => 'meta_key',
				'placeholder' => __( 'Meta Key', 'wp-content-pilot' ),
				'type'        => 'text',
				'class'       => 'long',
				'attrs'       => array(
					'pattern' => '^[a-zA-Z0-9_-]+',
					'title'   => __( 'Valid mata key no space and no spacial key. Only \'_\' and \'-\' allowed.', 'wp-content-pilot' ),
				)
			),
			array(
				'name'        => 'meta_value',
				'placeholder' => __( 'Meta Value', 'wp-content-pilot' ),
				'type'        => 'text',
				'class'       => 'long'
			)
		),
	), $post_id ) );

	echo content_pilot()->elements->repeatable( apply_filters( 'wpcp_search_n_replace_args', array(
		'label'    => __( 'Search & Replace (Pro)', 'wp-content-pilot' ),
		'name'     => '_wpcp_search_n_replace',
		'desc'     => __( 'Search and replace content with text or regular expression. (PRO) ', 'wp-content-pilot' ),
		'disabled' => true,
		'fields'   => array(
			array(
				'name'        => 'search',
				'placeholder' => __( 'Search', 'wp-content-pilot' ),
				'type'        => 'text',
				'class'       => 'long',
			),
			array(
				'name'        => 'replace',
				'placeholder' => __( 'Replace', 'wp-content-pilot' ),
				'type'        => 'text',
				'class'       => 'long'
			)
		),
	), $post_id ) );

	do_action( 'wpcp_end_campaign_advance_settings_metabox', $post_id, $campaign_type );
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

function wpcp_campaign_posted_posts_metabox_callback( $post, $campaign_type ) {
	ob_start();
	include WPCP_VIEWS . '/metabox/posted-posts.php';
	$html = ob_get_clean();
	echo $html;
}

function wpcp_campaign_logs_metabox_callback( $post, $campaign_type ) {
	ob_start();
	include WPCP_VIEWS . '/metabox/logs.php';
	$html = ob_get_clean();
	echo $html;
}

function wpcp_campaign_meta_actions_metabox_callback( $post, $campaign_type ) {
	ob_start();
	include WPCP_VIEWS . '/metabox/meta-actions.php';
	$html = ob_get_clean();
	echo $html;
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
	update_post_meta( $post_id, '_campaign_type', empty( $posted['_campaign_type'] ) ? 'feed' : sanitize_text_field( $posted['_campaign_type'] ) );
	update_post_meta( $post_id, '_campaign_target', empty( $posted['_campaign_target'] ) ? '' : intval( $posted['_campaign_target'] ) );
	update_post_meta( $post_id, '_campaign_frequency', empty( $posted['_campaign_frequency'] ) ? '' : intval( $posted['_campaign_frequency'] ) );
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
	update_post_meta( $post_id, '_author', empty( $posted['_author'] ) ? '' : intval( $posted['_author'] ) );
	update_post_meta( $post_id, '_categories', empty( $posted['_categories'] ) ? '' : $posted['_categories'] );
	update_post_meta( $post_id, '_tags', empty( $posted['_tags'] ) ? '' : $posted['_tags'] );


	update_post_meta( $post_id, '_title_limit', empty( $posted['_title_limit'] ) ? '' : esc_attr( $posted['_title_limit'] ) );
	update_post_meta( $post_id, '_content_limit', empty( $posted['_content_limit'] ) ? '' : esc_attr( $posted['_content_limit'] ) );

	do_action( 'wpcp_update_campaign_settings', $post_id, $posted );
}

add_action( 'save_post_wp_content_pilot', 'wpcp_update_campaign_settings' );


function wpcp_repeat_search_replace_row( $args, $post_id, $index ) {
	$args = wp_parse_args( $args, array(
		'search'  => '',
		'replace' => '',
	) );

	?>
	<!--	<td>-->
	<!--		<input type="text" name="_search_replace[--><?php //echo intval($index);
	?><!--]['search']" disabled="disabled"><br>-->
	<!--		<span class="ever-field-description">--><?php //_e( 'Search Word' );
	?><!--</span>-->
	<!--	</td>-->
	<!--	<td>-->
	<!--		<input type="text" name="_search_replace[--><?php //echo intval($index);
	?><!--]['replace']" value="--><?php //echo $args['replace'];
	?><!--" disabled="disabled"><br>-->
	<!--		<span class="ever-field-description">--><?php //_e( 'Replace Word' );
	?><!--</span>-->
	<!--	</td>-->
	<!--	<td>-->
	<!--		<a href="#" class="add-field disabled"><i class="dashicons dashicons-plus"></i></a>-->
	<!--		<a href="#" class="remove-field disabled"><i class="dashicons dashicons-minus"></i></a>-->
	<!--	</td>-->
	<?php
}

//add_action('wpcp_repeat_meta_field_row', 'wpcp_repeat_search_replace_row', 10, 3);

function wpcp_repeat_meta_field_row( $args, $post_id, $index ) {
	$args = wp_parse_args( $args, array(
		'key'   => '',
		'value' => '',
	) );

	?>
	<!--	<td>-->
	<!--		<input type="text" name="_meta_fields[--><?php //echo intval($index);
	?><!--]['key']" disabled="disabled"><br>-->
	<!--		<span class="ever-field-description">--><?php //_e( 'Search Word' );
	?><!--</span>-->
	<!--	</td>-->
	<!--	<td>-->
	<!--		<input type="text" name="_meta_fields[--><?php //echo intval($index);
	?><!--]['replace']" value="--><?php //echo $args['replace'];
	?><!--" disabled="disabled"><br>-->
	<!--		<span class="ever-field-description">--><?php //_e( 'Replace Word' );
	?><!--</span>-->
	<!--	</td>-->
	<!--	<td>-->
	<!--		<a href="#" class="add-field disabled"><i class="dashicons dashicons-plus"></i></a>-->
	<!--		<a href="#" class="remove-field disabled"><i class="dashicons dashicons-minus"></i></a>-->
	<!--	</td>-->
	<?php
}
//add_action('wpcp_render_repeat_meta_field_row', 'wpcp_repeat_meta_field_row', 10, 3);
