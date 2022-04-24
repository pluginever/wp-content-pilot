<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::select_input( array(
	'label'          => __( 'Post type', 'wp-content-pilot' ),
	'name'           => '_post_type',
	'placeholder'    => '',
	'double_columns' => true,
	'options'        => apply_filters( 'wpcp_campaign_post_types', array(
		'post' => __( 'Post', 'wp-content-pilot' ),
		'page' => __( 'Page', 'wp-content-pilot' ),
	) ),
) );

echo WPCP_HTML::select_input( array(
	'label'    => __( 'Status', 'wp-content-pilot' ),
	'name'     => '_post_status',
	'options'  => apply_filters( 'wpcp_campaign_post_statuses', array(
		'publish' => __( 'Published', 'wp-content-pilot' ),
		'private' => __( 'Private', 'wp-content-pilot' ),
		'draft'   => __( 'Draft', 'wp-content-pilot' ),
		'pending' => __( 'Pending', 'wp-content-pilot' ),
	) ),
	'required' => true,
) );

echo WPCP_HTML::select_input( array(
	'label'       => __( 'Post Author', 'wp-content-pilot' ),
	'name'        => '_author',
	'placeholder' => '',
	'options'     => wpcp_get_authors(),
	'required'    => true,
	'tooltip'     => __( 'Select author', 'wp-content-pilot' ),
) );

//echo WPCP_HTML::text_input( array(
//	'label'       => __( 'Post Format', 'wp-content-pilot' ),
//	'name'        => '_post_format',
//	'placeholder' => 'audio',
//	'tooltip'     => __( 'Select post format', 'wp-content-pilot' ),
//) );

echo WPCP_HTML::select_input( array(
	'label'   => __( 'Post Category', 'wp-content-pilot' ),
	'name'    => '_categories[]',
	'options' => wpcp_get_post_categories(),
	'class'   => 'wpcp-select2',
	'tooltip' => __( 'Select category for the post', 'wp-content-pilot' ),
	'attrs'         => array(
		'multiple' => 'multiple',
	)
) );

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Keyword to category', 'wp-content-pilot' ),
	'name'          => '_keyword_to_category',
	'required'      => true,
	'placeholder'   => __( 'Separate with (,) comma', 'wp-content-pilot' ),
	'tooltip'       => __( 'This option will search the content for the keyword and if exists, it will assign & set category to the post', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::select_input( array(
	'label'   => __( 'Post Tags', 'wp-content-pilot' ),
	'name'    => '_tags[]',
	'class'   => 'wpcp-select2',
	'options' => wpcp_get_post_tags(),
	'tooltip' => __( 'Select tags for the post', 'wp-content-pilot' ),
	'attrs'         => array(
		'multiple' => 'multiple',
	)
) );

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Keyword to tag', 'wp-content-pilot' ),
	'name'          => '_keyword_to_tag',
	'required'      => true,
	'placeholder'   => __( 'Separate with (,) comma', 'wp-content-pilot' ),
	'tooltip'       => __( 'This option will search the content for the keyword and if exists, it will assign & set tag to the post', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

//echo WPCP_HTML::checkbox_input( array(
//	'label' => __( 'Allow Comment', 'wp-content-pilot' ),
//	'name'  => '_allow_comments',
//) );
//
//
//echo WPCP_HTML::checkbox_input( array(
//	'label' => __( 'Allow pingbacks', 'wp-content-pilot' ),
//	'name'  => '_allow_pingbacks',
//) );
//
//echo WPCP_HTML::checkbox_input( array(
//	'label' => __( 'Add excerpt', 'wp-content-pilot' ),
//	'name'  => '_excerpt',
//) );
