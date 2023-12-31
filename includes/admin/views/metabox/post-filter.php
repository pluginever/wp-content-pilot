<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Min Words', 'wp-content-pilot' ),
		'name'          => '_min_words',
		'type'          => 'number',
		'placeholder'   => 500,
		'tooltip'       => esc_html__( 'Min required words, otherwise posts will be rejected.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Required Words', 'wp-content-pilot' ),
		'name'          => '_required_words',
		'placeholder'   => esc_html__( 'Fashion, Secret, Awesome', 'wp-content-pilot' ),
		'tooltip'       => esc_html__( 'Must contain words, otherwise posts will be rejected.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Banned Words', 'wp-content-pilot' ),
		'name'          => '_banned_words',
		'placeholder'   => esc_html__( 'YouTube, Wikipedia, Google', 'wp-content-pilot' ),
		'tooltip'       => esc_html__( 'If a post contains the above words it will be rejected.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);
