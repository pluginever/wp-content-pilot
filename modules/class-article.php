<?php
/**
 * Feed Class
 *
 * @package     WP Content Pilot
 * @subpackage  Feed
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCP_Article {

	/**
	 * WPCP_Feed constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_filter( 'wpcp_campaign_keyword_input_args', array( $this, 'campaign_keyword_input' ), 10, 2 );
	}

	public function register_module( $modules ) {
		$modules['article'] = [
			'title'       => __( 'Article', 'wp-content-pilot' ),
			'description' => __( 'Scraps articles based on links', 'wp-content-pilot' ),
			'supports'    => array( 'author', 'title', 'except', 'content', 'image_url', 'image', 'images' ),
			'callback'    => __CLASS__,
		];

		return $modules;
	}


	public function campaign_keyword_input( $attr, $campaign_type ) {
//		if ( $campaign_type == 'feeds' ) {
//			$attr['label'] = __( 'Feed Links', 'wp-content-pilot' );
//			$attr['name']  = '_feed_links';
//		}

		return $attr;
	}


}
