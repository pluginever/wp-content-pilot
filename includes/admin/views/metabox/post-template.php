<?php
defined( 'ABSPATH' ) || exit();
global $post;

$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
if ( empty( $campaign_type ) ) {
	return false;
}
$module = content_pilot()->modules()->load( $campaign_type );

if ( ! is_callable( array( $module, 'get_template_tags' ) ) ) {
	esc_html_e( 'Could not find the module', 'wp-content-pilot' );
	return false;
}
$template_tags    = $module->get_template_tags();
$default_template = $module->get_default_template();

printf( '<label style="font-weight: 700;display: block;margin-bottom: 5px;">%s</label>', esc_html__( 'Spin Article', 'wp-content-pilot' ) );

echo WPCP_HTML::checkbox_input(
	array(
		'label'    => sprintf( /* translators: 1: Permalink, 2: HTML anchor end tag */ esc_html__( 'Spin article using using spinrewriter, if you do not have account please %1$ssign up%2$s and set in settings page.', 'wp-content-pilot' ), '<a href="https://bit.ly/spinrewriterpluginever" target="_blank">', '</a>' ),
		'name'     => '_spin_article',
		'required' => false,
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'    => esc_html__( 'Post Title', 'wp-content-pilot' ),
		'name'     => '_post_title',
		'default'  => '{title}',
		'required' => true,
	)
);

echo WPCP_HTML::textarea_input(
	array(
		'label'      => esc_html__( 'Post Template', 'wp-content-pilot' ),
		'name'       => '_post_template',
		'default'    => wp_kses_post( $default_template ),
		'required'   => true,
		'css'        => 'min-height:200px;',
		'attributes' => array(
			'rows' => 5,
		),
	),
);

echo '<div class="wpcp-template-tags">';
printf( '<label>%s</label>', esc_html__( 'Supported Tags:', 'wp-content-pilot' ) );
foreach ( $template_tags as $tag => $description ) {
	printf( '<code class="wpcp-tooltip" data-tip="%s">{%s}</code>', wp_kses_post( $description ), esc_html( $tag ) );
}
echo '</div>';
