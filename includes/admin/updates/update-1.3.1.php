<?php
function content_pilot_update_1_3_1() {
	$campaigns = get_posts(
		array(
			'post_type'      => 'wp_content_pilot',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'   => '_campaign_type',
					'value' => 'flickr',
				),
			),
		)
	);
	$campaigns = wp_list_pluck( $campaigns, 'ID' );
	if ( ! empty( $campaigns ) ) {
		foreach ( $campaigns as $campaign ) {
			$license = get_post_meta( $campaign, '_flickr_licenses[]', true );
			update_post_meta( $campaign, '_flickr_licenses', $license );
		}
	}
}

content_pilot_update_1_3_1();

