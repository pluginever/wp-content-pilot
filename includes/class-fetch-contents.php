<?php

class WPCP_Fetch_Contents extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wpcp_fetch_contents';

	protected function task( $link_id ) {
		$link = wpcp_get_link( $link_id );

		if ( empty($link) || $link->status !== 'fetched' ) {
			return false;
		}

		do_action( 'wpcp_fetching_campaign_contents', $link );

		return false;
	}


	protected function complete() {
		wpcp_log('Fetch content complete');
		parent::complete();
	}

}
