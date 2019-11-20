<?php

class WPCP_Automatic_Campaign extends WP_Background_Process {

	protected $action = 'wpcp_automatic_campaign';

	protected function task( $campaign_id ) {
		wpcp_log( 'Running WPCP_Automatic_Campaign' );


		//check campaign status
		$last_run     = wpcp_get_post_meta( $campaign_id, '_last_run', 0 );
		$frequency    = wpcp_get_post_meta( $campaign_id, '_campaign_frequency', 0 );
		$target       = wpcp_get_post_meta( $campaign_id, '_campaign_target', 0 );
		$posted       = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
		$current_time = current_time( 'timestamp' );
		$diff         = $current_time - $last_run;
		if ( $diff < $frequency ) {
			return true;
		}

		if ( $posted >= $target ) {
			wpcp_disable_campaign( $campaign_id );
			wpcp_log( __( "#$campaign_id this campaign reach the limit", 'wp-content-pilot' ), 'dev' );
			exit;
		}

		$campaign = wpcp_run_campaign( $campaign_id );
		if ( is_wp_error( $campaign ) ) {
			wpcp_log( __( 'Automatic campaign failed.', 'wp-content-pilot' ), 'dev' );
			wpcp_log( $campaign->get_error_message(), 'critical' );
			exit;
		}

		return false;
	}


	protected function complete() {
		parent::complete();
	}

}
