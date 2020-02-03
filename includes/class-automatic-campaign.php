<?php

defined('ABSPATH')|| exit();

class WPCP_Automatic_Campaign extends WP_Background_Process {

	protected $action = 'wpcp_automatic_campaign';

	protected function task( $campaign_id ) {
		wpcp_logger()->debug( 'Running WPCP_Automatic_Campaign' );
		$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', '');
		if(!empty( $campaign_type)){
			content_pilot()->modules()->load( $campaign_type)->process_campaign( $campaign_id, '', 'cron');
		}
	}


	protected function complete() {
		parent::complete();
	}

}

//new WPCP_Automatic_Campaign();
