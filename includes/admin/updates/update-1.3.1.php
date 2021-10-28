<?php
function content_pilot_update_1_3_1() {
	global $wpdb;
	$campaigns = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts where post_type= %s AND post_status= %s", 'wp_content_pilot', 'publish' ) );
	$campaigns = wp_list_pluck( $campaigns, 'ID' );
	if ( ! empty( $campaigns ) ) {
		foreach ( $campaigns as $campaign ) {
			$campaign_type = get_post_meta( $campaign, '_campaign_type', true );
			if ( ! empty( $campaign_type ) && 'flickr' == $campaign_type ) {
				$license = get_post_meta( $campaign, '_flickr_licenses[]', true );
				update_post_meta( $campaign, '_flickr_licenses', $license );
			}
		}
	}
}

content_pilot_update_1_3_1();

