<?php
global $wpdb;
global $post_id;

$args = array(
	'meta_key'   => '_wpcp_campaign_generated_post',
	'meta_value' => $post_id,
	'meta_value' => $post_id,
);

$posts = wpcp_get_posts( $args );

foreach ( $posts as $post ) {
	setup_postdata( $post );
	?>

	<a href="<?php echo get_the_permalink( $post->ID ) ?>"><?php echo get_the_title( $post->ID ) ?></a>
	<span class="description"><?php echo sprintf( __( 'Published at: %s', 'wp-content-pilot' ), $post->post_date ) ?></span>

<?php }
