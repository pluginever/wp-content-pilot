<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::select_input(
	array(
		'label'          => esc_html__( 'Post type', 'wp-content-pilot' ),
		'name'           => '_post_type',
		'placeholder'    => '',
		'double_columns' => true,
		'options'        => apply_filters(
			'wpcp_campaign_post_types',
			array(
				'post' => esc_html__( 'Post', 'wp-content-pilot' ),
				'page' => esc_html__( 'Page', 'wp-content-pilot' ),
			)
		),
	)
);

echo WPCP_HTML::select_input(
	array(
		'label'    => esc_html__( 'Status', 'wp-content-pilot' ),
		'name'     => '_post_status',
		'options'  => apply_filters(
			'wpcp_campaign_post_statuses',
			array(
				'publish' => esc_html__( 'Published', 'wp-content-pilot' ),
				'private' => esc_html__( 'Private', 'wp-content-pilot' ),
				'draft'   => esc_html__( 'Draft', 'wp-content-pilot' ),
				'pending' => esc_html__( 'Pending', 'wp-content-pilot' ),
			)
		),
		'required' => true,
	)
);

echo WPCP_HTML::select_input(
	array(
		'label'       => esc_html__( 'Post Author', 'wp-content-pilot' ),
		'name'        => '_author',
		'placeholder' => '',
		'options'     => wpcp_get_authors(),
		'required'    => true,
		'tooltip'     => esc_html__( 'Select author', 'wp-content-pilot' ),
	)
);

// phpcs:disable
// echo WPCP_HTML::text_input( array(
// 'label'       => __( 'Post Format', 'wp-content-pilot' ),
// 'name'        => '_post_format',
// 'placeholder' => 'audio',
// 'tooltip'     => __( 'Select post format', 'wp-content-pilot' ),
// ) );
// phpcs:enable

echo WPCP_HTML::select_input(
	array(
		'label'   => esc_html__( 'Post Category', 'wp-content-pilot' ),
		'name'    => '_categories[]',
		'options' => wpcp_get_post_categories(),
		'class'   => 'wpcp-select2',
		'tooltip' => esc_html__( 'Select category for the post', 'wp-content-pilot' ),
		'attrs'   => array(
			'multiple' => 'multiple',
		),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Keyword to category', 'wp-content-pilot' ),
		'name'          => '_keyword_to_category',
		'required'      => true,
		'placeholder'   => esc_html__( 'Separate with (,) comma', 'wp-content-pilot' ),
		'tooltip'       => esc_html__( 'This option will search the content for the keyword and if exists, it will assign & set category to the post', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);

echo WPCP_HTML::select_input(
	array(
		'label'   => esc_html__( 'Post Tags', 'wp-content-pilot' ),
		'name'    => '_tags[]',
		'class'   => 'wpcp-select2',
		'options' => wpcp_get_post_tags(),
		'tooltip' => esc_html__( 'Select tags for the post', 'wp-content-pilot' ),
		'attrs'   => array(
			'multiple' => 'multiple',
		),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Keyword to tag', 'wp-content-pilot' ),
		'name'          => '_keyword_to_tag',
		'required'      => true,
		'placeholder'   => esc_html__( 'Separate with (,) comma', 'wp-content-pilot' ),
		'tooltip'       => esc_html__( 'This option will search the content for the keyword and if exists, it will assign & set tag to the post', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);
// phpcs:disable
// echo WPCP_HTML::checkbox_input( array(
// 'label' => __( 'Allow Comment', 'wp-content-pilot' ),
// 'name'  => '_allow_comments',
// ) );
//
//
// echo WPCP_HTML::checkbox_input( array(
// 'label' => __( 'Allow pingbacks', 'wp-content-pilot' ),
// 'name'  => '_allow_pingbacks',
// ) );
//
// echo WPCP_HTML::checkbox_input( array(
// 'label' => __( 'Add excerpt', 'wp-content-pilot' ),
// 'name'  => '_excerpt',
// ) );
// phpcs:enable
