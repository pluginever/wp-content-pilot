<?php
global $wpdb;
global $post_id;

if ( ! $post_id ) {
	esc_html_e( 'No post found from this campaign', 'wp-content-pilot' );
	return;
}

$args = array(
	'orderby'    => 'ID',
	'meta_query' => array(
		array(
			'key'     => '_wpcp_campaign_generated_post',
			'value'   => $post_id,
		),
	),
);

$posts = wpcp_get_posts( $args );

foreach ( $posts as $post ) {
	setup_postdata( $post );
	?>

	<a href="<?php echo get_the_permalink( $post->ID ) ?>"><?php echo get_the_title( $post->ID ) ?></a>
	<span class="description"><?php echo sprintf( __( 'Published at: %s', 'wp-content-pilot' ), $post->post_date ) ?></span>

<?php }
wp_reset_postdata();
