<?php

class WPCP_Article_Campaign_Tests extends WP_UnitTestCase {
	/**
	 * @var int
	 */
	protected $campaign_id;
	/**
	 * @var WP_Post
	 */
	protected $campaign;

	public function setUp() {
		parent::setUp();
		$keyword = WPCP_Helper_Campaign::get_random_keyword();
		$campaign_id       = WPCP_Helper_Campaign::create_campaign( [
			'title'      => 'Article Campaign',
			'meta_input' => array(
				'_keywords'           => $keyword,
				'_campaign_type'      => 'article',
				'_campaign_target'    => '1',
				'_campaign_frequency' => '10',
				'_frequency_unit'     => 'hours',
				'_post_title'         => '{title}',
				'_post_template'      => '{content}',
				'_post_type'          => 'post',
				'_post_status'        => 'publish',
			)
		] );
		$this->campaign_id = $campaign_id;
		$this->campaign    = get_post( $campaign_id );
		echo "Using Keyword {$keyword} ..." . PHP_EOL;
	}

	public function test_campaign_run() {
		$this->assertInternalType( "int", WPCP_Helper_Campaign::run_campaign( $this->campaign->ID ));
	}

	public function test_campaign_options(){
		update_post_meta($this->campaign_id, '_set_featured_image', 'on');
		$post_id = WPCP_Helper_Campaign::run_campaign( $this->campaign->ID );
		$this->assertNotFalse(has_post_thumbnail($post_id));

		$dom = wpcp_str_get_html(post_content);
		$dom->find('a');
	}

	public function test_post_template() {

	}

	public function test_post_settings() {
		update_post_meta( $this->campaign_id, '_post_status', 'draft' );
		update_post_meta( $this->campaign_id, '_post_type', 'page' );
		$post_id = WPCP_Helper_Campaign::run_campaign( $this->campaign->ID );
		$this->assertEquals( 'draft', get_post_field( 'post_status', $post_id ) );
		$this->assertEquals( 'page', get_post_field( 'post_type', $post_id ) );
	}


	public function test_advance_settings() {
		update_post_meta( $this->campaign_id, '_title_limit', '3' );
		$post_id = WPCP_Helper_Campaign::run_campaign( $this->campaign->ID );
		$this->assertEquals( 3, str_word_count( get_post_field( 'post_title', $post_id ) ) );
	}

	public function tearDown() {
		parent::tearDown();
	}
}
