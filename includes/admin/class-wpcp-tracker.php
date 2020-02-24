<?php
defined( 'ABSPATH' ) || exit();

class WPCP_Tracker extends Pluginever_Insights {

	public function __construct() {
		$notice = __( 'Want to help make <strong>WP Content Pilot</strong> even more awesome? Allow PluginEver to collect non-sensitive diagnostic data and usage information.', 'wp-content-pilot' );

		parent::__construct( 'wp-content-pilot', 'WP Content Pilot', WPCP_FILE, $notice );
	}

	/**
	 * Get the extra data
	 *
	 * @return array
	 */
	protected function get_extra_data() {
		global $wpdb;
		$campaigns = $wpdb->get_results( "select meta_value as type, count(meta_value) count from $wpdb->postmeta where meta_key='_campaign_type' AND post_id in (select ID from $wpdb->posts where post_type='wp_content_pilot') group by meta_value order by count desc" );
		$data      = array(
			'total_campaign' => $this->get_post_count( 'wp_content_pilot' ),
			'is_pro'         => defined( 'WPCP_PRO_VERSION' ) ? 'yes' : 'no',
			'campaigns'      => $campaigns,
		);

		return $data;
	}


	/**
	 * Explain the user which data we collect
	 *
	 * @return array
	 */
	protected function data_we_collect() {
		$data = array(
			'Server environment details (php, mysql, server, WordPress versions)',
			'Number of Campaigns',
			'Site language',
			'Number of active and inactive plugins',
			'Site name and url',
			'Your name and email address',
		);

		return $data;
	}

}

new WPCP_Tracker();
