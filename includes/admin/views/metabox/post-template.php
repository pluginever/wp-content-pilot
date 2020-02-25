<?php
defined( 'ABSPATH' ) || exit();
global $post;

$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
if ( empty( $campaign_type ) ) {
	return false;
}

$template_tags    = content_pilot()->modules()->load( $campaign_type )->get_template_tags();
$default_template = content_pilot()->modules()->load( $campaign_type )->get_default_template();

echo WPCP_HTML::text_input( array(
	'label'    => __( 'Post Title', 'wp-content-pilot' ),
	'name'     => '_post_title',
	'default'  => '{title}',
	'required' => true,
) );
echo WPCP_HTML::textarea_input( array(
	'label'      => __( 'Post Template', 'wp-content-pilot' ),
	'name'       => '_post_template',
	'default'    => $default_template,
	'required'   => true,
	'css'        => 'min-height:200px;',
	'attributes' => array(
		'rows' => 5,
	),
) );

echo '<div class="wpcp-template-tags">';

echo sprintf( '<label>%s</label>', __( 'Supported Tags:', 'wp-content-pilot' ) );
foreach ( $template_tags as $tag => $description ) {
	echo sprintf( '<code class="wpcp-tooltip" data-tip="%s">{%s}</code>', wp_kses_post( $description ), $tag );
}
echo '</div>';
