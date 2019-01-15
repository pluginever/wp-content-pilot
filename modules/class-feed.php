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
		//campaign settings
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_filter( 'wpcp_campaign_keyword_input_args', array( $this, 'campaign_keyword_input' ), 99, 3 );
		add_action( 'wpcp_after_campaign_keyword_input', array( $this, 'campaign_option_fields' ), 10, 2 );
		add_action( 'wpcp_update_campaign_settings', array( $this, 'update_campaign_settings' ), 10, 2 );

		//campaign actions
		add_action( 'wp_feed_options', array( $this, 'set_feed_options' ) );
		add_action( 'http_response', array( $this, 'trim_feed_content' ) );
		add_action( 'wp_feed_options', array( $this, 'force_feed' ), 10, 1 );
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


	public function campaign_keyword_input( $attr, $post_id, $campaign_type ) {
		if ( $campaign_type == 'feeds' ) {
			$attr['label'] = __( 'Feed Links', 'wp-content-pilot' );
			$attr['name']  = '_feed_links';
			$attr['desc']  = __( 'Input feed links separate comma', 'wp-content-pilot' );
			$attr['value'] = wpcp_get_post_meta( $post_id, '_feed_links');
		}

		return $attr;
	}

	public function campaign_option_fields(  $post_id, $campaign_type ) {
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
			'selected'         => wpcp_get_post_meta( $post_id, '_force_feed', 'no'),
			'desc'             => __( 'If you are putting exact feed link then set this to yes, otherwise feed links will be auto discovered', 'wp-content-pilot' ),
		) );

	}

	public function update_campaign_settings( $post_id, $posted ) {

		$raw_links       = empty( $posted['_feed_links'] ) ? '' : esc_html( $posted['_feed_links'] );
		$links           = explode( PHP_EOL, $raw_links );
		$sanitized_links = [];

		foreach ( $links as $link ) {
			$sl = trim( $link );
			if ( filter_var( $link, FILTER_VALIDATE_URL ) === false ) {
				continue;
			}

			$sanitized_links[] = $sl;
		}

		$sanitized_links = array_unique($sanitized_links);
		$sanitized_links = array_filter($sanitized_links);

		$str_links = implode( PHP_EOL, $sanitized_links );


		update_post_meta( $post_id, '_feed_links', $str_links );
		update_post_meta( $post_id, '_force_feed', empty( $posted['_force_feed'] ) ? 'no' : esc_attr( $posted['_force_feed'] ) );
	}

	/**
	 * Sanitize links from string
	 *
	 * @since 1.0.0
	 *
	 * @param $string_links
	 *
	 * @return string
	 *
	 */
	public static function sanitize_feed_links( $string_links ) {
		$links           = explode( PHP_EOL, $string_links );
		$sanitized_links = [];

		foreach ( $links as $link ) {
			$sl = trim( $link );
			if ( filter_var( $link, FILTER_VALIDATE_URL ) === false ) {
				continue;
			}

			$sanitized_links[] = $sl;
		}

		return implode( PHP_EOL, $sanitized_links );
	}

	/**
	 * Set user agent to fix curl transfer
	 * closed without complete data
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 */
	public function set_feed_options( $args ) {
		$args->set_useragent( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 ' );
	}

	/**
	 * Trim body to remove extra space
	 *
	 * since 1.0.0
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function trim_feed_content( $args ) {
		$args['body'] = trim( $args['body'] );

		return $args;
	}

	/**
	 * Force feed with the given feedlink
	 *
	 * since 1.0.0
	 *
	 * @param $feed
	 */
	public function force_feed( $feed ) {
		if ( 'yes' == wpcp_get_post_meta( $this->campaign_id, '_force_feed', 'no' ) ) {
			$feed->force_feed( true );
		}
	}


	/**
	 * Nothing to check
	 * since 1.0.0
	 *
	 * @return bool
	 */
	public function setup() {
		return true;
	}

	public function discover_links() {
		include_once( ABSPATH . WPINC . '/feed.php' );
		$rss = fetch_feed( $this->keyword );

		if ( is_wp_error( $rss ) ) {
			return $rss;
		}

		if ( $this->is_result_like_last_time( $rss ) ) {
			$msg = __( sprintf( 'Could not discover any new post to for the url . Please try letter.', $this->keyword ), 'wp-content-pilot' );
			wpcp_log( $msg, 'log' );

			return new \WP_Error( 'no-new-result', $msg );
		}

		$max_items = $rss->get_item_quantity();
		$rss_items = $rss->get_items( 0, $max_items );

		if ( ! isset( $max_items ) || $max_items == 0 ) {
			wpcp_disable_keyword( $this->campaign_id, $this->keyword, '_feed_links' );
			$msg = __( 'Could not find any post so disabling url', 'wp-content-pilot' );
			wpcp_log( $msg, 'critical' );

			return new \WP_Error( 'fetch-links-failed', $msg );
		}


		$links = [];
		foreach ( $rss_items as $rss_item ) {
			$url = esc_url( $rss_item->get_permalink() );

			if ( stristr( $url, 'news.google' ) ) {
				$urlParts   = explode( 'url=', $url );
				$correctUrl = $urlParts[1];
				$url        = $correctUrl;

			}

			//Google alerts links correction
			if ( stristr( $this->keyword, 'alerts/feeds' ) && stristr( $this->keyword, 'google' ) ) {
				preg_match( '{url\=(.*?)[&]}', $url, $urlMatches );
				$correctUrl = $urlMatches[1];

				if ( trim( $correctUrl ) != '' ) {
					$url = $correctUrl;
				}
			}

			$host    = wpcp_get_host( $url );
			$content = wpcp_fix_html_links( $rss_item->get_content(), $host );

			$link = array(
				'title'       => $rss_item->get_title(),
				'content'     => $content,
				'url'         => $url,
				'image'       => '',
				'raw_content' => $content,
				'score'       => '0',
				'status'      => 'ready',
			);

			$link = apply_filters( 'wpcp_before_insert_feed_link', $link, $this->campaign_id );

			$links[] = $link;
		}

		return $links;

	}

	public function get_post( $link ) {

		$images = wpcp_get_all_image_urls( $link->raw_content );

		$article = [
			'title'       => $link->title,
			'content'     => $link->content,
			'raw_content' => $link->raw_content,
			'image_url'   => ! empty( $images ) ? '' : $images[0],
		];


		return apply_filters( 'wpcp_feed_article', $article, $this->campaign_id );
	}


}
