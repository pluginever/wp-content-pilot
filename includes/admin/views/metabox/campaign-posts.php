<?php
$posts = get_posts(
	array(
		'post_type'   => wpcp_get_post_meta( $campaign_id, '_post_type', 'post' ),
		'numberposts' => 10,
		'meta_key'    => '_campaign_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'  => $campaign_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
	)
);

if ( empty( $posts ) ) {
	printf( '<p>%s</p>', esc_html__( 'No posts generated yet', 'wp-content-pilot' ) );
} else {
	echo '<ul class="wpcp-campaign-posts">';
	foreach ( $posts as $post ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( get_the_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
	}

	echo '</ul>';
}
