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


}
