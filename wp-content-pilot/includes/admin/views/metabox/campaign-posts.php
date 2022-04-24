<?php
$posts = get_posts( array(
	'post_type'   => wpcp_get_post_meta( $campaign_id, '_post_type', 'post' ),
	'numberposts' => 10,
	'meta_key'    => '_campaign_id',
	'meta_value'  => $campaign_id,
	'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
) );

if ( empty( $posts ) ) {
	echo sprintf( '<p>%s</p>', __( 'No posts generated yet', 'wp-content-pilot' ) );
} else {
	echo '<ul class="wpcp-campaign-posts">';
	foreach ( $posts as $post ) {
		echo sprintf( '<li><a href="%s">%s</a></li>', get_the_permalink( $post ), get_the_title( $post ) );
	}

	echo '</ul>';
}
