<?php
class WPCP_Automatic_Campaign extends WP_Background_Process {

	protected $action = 'wpcp_automatic_campaign';

	protected function task( $campaign_id ) {
		wpcp_log('Running WPCP_Automatic_Campaign');
		$campaign = wpcp_run_campaign( $campaign_id );
		if ( is_wp_error( $campaign ) ) {
			wpcp_log( __( 'Automatic campaign failed.', 'wp-content-pilot' ), 'dev' );
			wpcp_log( $campaign->get_error_message(), 'critical' );
		}
		return false;
	}


	protected function complete() {
		parent::complete();
	}

}
