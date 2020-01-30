<?php
defined('ABSPATH') || exit();
global $post;

echo WPCP_HTML::text_input( array(
	'label'   => __( 'Limit Title', 'wp-content-pilot' ),
	'type'    => 'number',
	'name'    => '_title_limit',
	'tooltip' => 'Input the number of words to limit the title. Default full title.',
) );

echo WPCP_HTML::text_input( array(
	'label'   => __( 'Limit Content', 'wp-content-pilot' ),
	'type'    => 'number',
	'name'    => '_content_limit',
	'tooltip' => 'Input the number of words to limit content. Default full content.',
) );

echo WPCP_HTML::select_input( array(
	'label'         => __( 'Translate To', 'wp-content-pilot' ),
	'name'          => '_translate_to',
	'options'       => array(
		'' => __( 'No Translation', 'wp-content-pilot' )
	),
	'tooltip'       => __( 'Select a language to translate.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );
echo WPCP_HTML::textarea_input( array(
	'label'         => __( 'Search Replace', 'wp-content-pilot' ),
	'name'          => '_wpcp_search_n_replace',
	'placeholder'   => __( 'Apple|Mango', 'wp-content-pilot' ),
	'desc'          => __( 'One per line', 'wp-content-pilot' ),
	'style'         => 'min-height:100px;',
	'tooltip'       => __( 'Search and replace contents with text or regular expression.Must be one per line.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::textarea_input( array(
	'label'         => __( 'Post Meta', 'wp-content-pilot' ),
	'name'          => '_wpcp_custom_meta_field',
	'placeholder'   => __( 'title|{title}', 'wp-content-pilot' ),
	'desc'          => __( 'One per line', 'wp-content-pilot' ),
	'style'         => 'min-height:100px;',
	'tooltip'       => __( 'Add custom post meta for posts. Must be one per line.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );
