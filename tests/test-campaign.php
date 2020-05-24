<?php

class WPCP_Campaign_Tests extends WP_UnitTestCase {
	/**
	 * @var WP_Post
	 */
	protected $campaign;

	public function setUp() {
		parent::setUp();
		$campaign_id = WPCP_Helper_Campaign::create_campaign( [] );
		$this->campaign = get_post( $campaign_id );
	}

	public function test_post_props() {
		$this->assertNotNull( $this->campaign->ID, 'Message' );
//		$this->assertSame( 'Test Campaign', wp_update_post( [
//			'post_title' => 'Test Campaign',
//			'ID'         => $this->campaign->ID
//		] ) );
	}

	public function tearDown() {
		parent::tearDown();
	}
}
