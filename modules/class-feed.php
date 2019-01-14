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

class WPCP_Feed extends WPCP_Campaign {

	/**
	 * WPCP_Feed constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_filter( 'wpcp_campaign_keyword_input_args', array( $this, 'campaign_keyword_input' ), 10, 2 );
		add_action( 'wpcp_after_campaign_keyword_input', array( $this, 'campaign_option_fields' ), 10, 2 );
	}

	public function register_module( $modules ) {
		$modules['feeds'] = [
			'title'       => __( 'Feed', 'wp-content-pilot' ),
			'description' => __( 'Scraps articles from the feed urls', 'wp-content-pilot' ),
			'supports'    => self::get_template_tags(),
			'callback'    => __CLASS__,
		];

		return $modules;
	}


	public static function get_template_tags() {
		$tags = array( 'author', 'title', 'except', 'content', 'image_url', 'image', 'images' );

		return $tags;
	}


	public function campaign_keyword_input( $attr, $campaign_type ) {
		if ( $campaign_type == 'feeds' ) {
			$attr['label'] = __( 'Feed Links', 'wp-content-pilot' );
			$attr['name']  = '_feed_links';
			$attr['desc']  = __( 'Input feed links separate by comma', 'wp-content-pilot' );
		}

		return $attr;
	}

	public function campaign_option_fields( $attr, $campaign_type ) {
		if ( 'feeds' != $campaign_type ) {
			return false;
		}

		echo content_pilot()->elements->select( array(
			'label'            => __( 'Force Feed', 'wp-content-pilot' ),
			'name'             => '_force_feed',
			'placeholder'      => '',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => array(
				'yes' => __( 'Yes', 'wp-content-pilot' ),
				'no'  => __( 'No', 'wp-content-pilot' ),
			),
			'required'         => true,
			'double_columns'   => true,
			'selected'         => 'no',
			'desc'             => __( 'If you are putting exact feed link then set this to yes, otherwise feed links will be auto discovered', 'wp-content-pilot' ),
		) );

	}

	public function setup() {
		// TODO: Implement setup() method.
	}

	public function discover_links() {
		// TODO: Implement discover_links() method.
	}

	public function fetch_post( $link ) {
		// TODO: Implement fetch_post() method.
	}


}
