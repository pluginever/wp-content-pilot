<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Min Words', 'wp-content-pilot' ),
	'name'          => '_min_words',
	'type'          => 'number',
	'placeholder'   => 500,
	'tooltip'       => __( 'Min required words, otherwise posts will be rejected.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Required Words', 'wp-content-pilot' ),
	'name'          => '_required_words',
	'placeholder'   => __( 'Fashion, Secret, Awesome', 'wp-content-pilot' ),
	'tooltip'       => __( 'Must contain words, otherwise posts will be rejected.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Banned Words', 'wp-content-pilot' ),
	'name'          => '_banned_words',
	'placeholder'   => __( 'YouTube, Wikipedia, Google', 'wp-content-pilot' ),
	'tooltip'       => __( 'If a post contains the above words it will be rejected.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );
