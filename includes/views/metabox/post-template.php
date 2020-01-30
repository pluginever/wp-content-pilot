<?php
defined('ABSPATH') || exit();
global $post;

$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
if ( empty( $campaign_type ) ) {
	return false;
}
$module = content_pilot()->modules->get_module( $campaign_type );
if ( empty( $module ) ) {
	return false;
}
$module_class  = $module['callback'];
$post_template = $module_class::get_default_template();

echo WPCP_HTML::text_input( array(
	'label'    => __( 'Post Title', 'wp-content-pilot' ),
	'name'     => '_post_title',
	'default'  => '{title}',
	'required' => true,
) );
echo WPCP_HTML::textarea_input( array(
	'label'      => __( 'Post Template', 'wp-content-pilot' ),
	'name'       => '_post_template',
	'default'    => $post_template,
	'required'   => true,
	'css'        => 'min-height:200px;',
	'attributes' => array(
		'rows' => 5,
	),
) );

echo '<div class="wpcp-template-tags">';
$module = content_pilot()->modules->get_module( $campaign_type );
if ( empty( $module ) || is_wp_error( $module ) ) {
	return new WP_Error( 'invalid-module-type', __( 'Invalid module type', 'wp-content-pilot' ) );
}

$tags = $module['callback']::get_template_tags();
echo sprintf( '<label>%s</label>', __( 'Supported Tags:', 'wp-content-pilot' ) );
foreach ( $tags as $tag => $description ) {
	echo sprintf( '<code class="wpcp-tooltip" data-tip="%s">{%s}</code>', wp_kses_post( $description ), $tag );
}
echo '</div>';
