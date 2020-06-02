<?php

/**
 * Class WPCP_Helper_Campaign.
 *
 * Helper class to create and delete a payment easily.
 */
class WPCP_Helper_Campaign extends WP_UnitTestCase {

	public static function create_campaign( $args ) {
		$defaults = array(
			'post_type' => 'wp_content_pilot'
		);

		return wp_insert_post( wp_parse_args( $args, $defaults ) );
	}

	public static function run_campaign( $campaign_id ) {
		$campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );

		return content_pilot()->modules()->load( $campaign_type )->process_campaign( $campaign_id, '', 'user' );
	}


	public static function get_random_keyword(){
		$keywords = [
			'costa rica travel',
			'kids',
			'baby products',
			'baby toys',
			'anime',
			'seo',
			'seo for local businesses',
		];
		return $keywords[array_rand($keywords)];
	}

	//http://www.crunchyroll.com/newsrss?lang=esES
}
