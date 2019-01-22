<?php
/**
 * Youtube Class
 *
 * @package     WP Content Pilot
 * @subpackage  Youtube
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCP_Youtube extends WPCP_Campaign {


	protected $api_key;

	/**
	 * WPCP_Youtube constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_after_campaign_keyword_input', array( $this, 'campaign_option_fields' ), 10, 2 );
		add_action( 'wpcp_update_campaign_settings', array( $this, 'update_campaign_settings' ), 10, 2 );
		add_action( 'wpcp_fetching_campaign_contents', array( $this, 'prepare_contents' ) );
	}

	/**
	 * Register article module
	 *
	 * @since 1.0.0
	 *
	 * @param $modules
	 *
	 * @return mixed
	 */
	public function register_module( $modules ) {
		$modules['youtube'] = [
			'title'       => __( 'Youtube', 'wp-content-pilot' ),
			'description' => __( 'Scraps videos based on keywords from youtube', 'wp-content-pilot' ),
			'supports'    => self::get_template_tags(),
			'callback'    => __CLASS__,
		];

		return $modules;
	}

	/**
	 * Supported template tags
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_template_tags() {
		return array(
			'title'     => __( 'Title', 'wp-content-pilot' ),
			'except'    => __( 'Summary', 'wp-content-pilot' ),
			'content'   => __( 'Content', 'wp-content-pilot' ),
			'image_url' => __( 'Main image url', 'wp-content-pilot' ),
			'source_url' => __( 'Source link', 'wp-content-pilot' ),
		);
	}

	/**
	 * Conditionally show meta fields
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id
	 * @param $campaign_type
	 *
	 * @return bool
	 */
	public function campaign_option_fields( $post_id, $campaign_type ) {

		if ( 'youtube' != $campaign_type ) {
			return false;
		}

		//		echo content_pilot()->elements->select( array(
		//			'name'             => '_youtube_search_type',
		//			'placeholder'      => '',
		//			'show_option_all'  => '',
		//			'show_option_none' => '',
		//			'label'            => __( 'Search Type', 'wp-content-pilot' ),
		//			'desc'             => __( 'Use global search for all result or use specific channel if you want to limit to that channel.', 'wp-content-pilot' ),
		//			'options'          => array(
		//				'global'  => __( 'Global', 'wp-content-pilot' ),
		//				'channel' => __( 'From Specific Channel', 'wp-content-pilot' ),
		//			),
		//			'double_columns'   => true,
		//			'selected'         => wpcp_get_post_meta( $post_id, '_youtube_search_type', 'global' ),
		//		) );

		echo content_pilot()->elements->select( array(
			'name'             => '_youtube_category',
			'placeholder'      => '',
			'show_option_all'  => '',
			'show_option_none' => '',
			'label'            => __( 'Category', 'wp-content-pilot' ),
			'options'          => wpcp_get_youtube_categories(),
			'double_columns'   => true,
			'selected'         => wpcp_get_post_meta( $post_id, '_youtube_category', 'all' ),
		) );

		echo content_pilot()->elements->select( array(

			'name'             => '_youtube_search_orderby',
			'label'            => __( 'Search Order By', 'wp-content-pilot' ),
			'placeholder'      => '',
			'show_option_all'  => '',
			'show_option_none' => '',
			'double_columns'   => true,

			'options' => array(
				'relevance' => __( 'Relevance', 'wp-content-pilot' ),
				'date'      => __( 'Date', 'wp-content-pilot' ),
				'title'     => __( 'Title', 'wp-content-pilot' ),
				'viewCount' => __( 'View Count', 'wp-content-pilot' ),
				'rating'    => __( 'Rating', 'wp-content-pilot' ),
			),

			'selected' => wpcp_get_post_meta( $post_id, '_youtube_search_orderby', 'relevance' ),
		) );

		echo content_pilot()->elements->select( array(
			'name'             => '_youtube_search_order',
			'label'            => __( 'Search Order', 'wp-content-pilot' ),
			'value'            => 'asc',
			'options'          => array(
				'asc'  => 'ASC',
				'desc' => 'DESC',
			),
			'placeholder'      => '',
			'show_option_all'  => '',
			'show_option_none' => '',
			'double_columns'   => true,
			'selected'         => wpcp_get_post_meta( $post_id, '_youtube_search_order', 'asc' ),
		) );


	}

	/**
	 * update campaign settings
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id
	 * @param $posted
	 */
	public function update_campaign_settings( $post_id, $posted ) {
		update_post_meta( $post_id, '_youtube_category', empty( $posted['_youtube_category'] ) ? 'all' : sanitize_text_field( $posted['_youtube_category'] ) );
		update_post_meta( $post_id, '_youtube_search_orderby', empty( $posted['_youtube_search_orderby'] ) ? '' : sanitize_key( $posted['_youtube_search_orderby'] ) );
		update_post_meta( $post_id, '_youtube_search_order', empty( $posted['_youtube_search_order'] ) ? '' : sanitize_key( $posted['_youtube_search_order'] ) );
	}

	/**
	 * Hook in background process and prepare contents
	 *
	 * @since 1.0.0
	 *
	 * @param $link
	 *
	 * @return bool
	 */
	public function prepare_contents( $link ) {

				if ( 'youtube' != $link->camp_type ) {
					return false;
				}

				$raw = maybe_unserialize($link->raw_content);

				wpcp_update_link( $link->id, array(
					'content' => trim( $link->description_html ),
					'score'   => wpcp_get_read_ability_score( isset($raw->description_html)?$raw->description_html: $link->content ),
					'status'  => 'ready',
				) );

	}

	public function setup() {

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_youtube', '' );

		error_log( $api_key );

		if ( empty( $api_key ) ) {

			$msg = __( 'Youtube API Key is not set, The campaign won\'t work without API Key.', 'wp-content-pilot' );
			wpcp_log( $msg );

			return new \WP_Error( 'invalid-api-settings', $msg );
		}

		$this->api_key = $api_key;

		return true;
	}

	public function discover_links() {

		$page     = $this->get_page_number( '' );
		$category = wpcp_get_post_meta( $this->campaign_id, '_youtube_category', 'all' );
		$orderby  = wpcp_get_post_meta( $this->campaign_id, '_youtube_search_orderby', 'relevance' );

		$query_args = array(
			'part'              => 'snippet',
			'type'              => 'video',
			'key'               => $this->api_key,
			'maxResults'        => 50,
			'q'                 => $this->keyword,
			'category'          => $category,
			'videoEmbeddable'   => 'true',
			'videoType'         => 'any',
			'relevanceLanguage' => 'en',
			'videoDuration'     => 'any',
			'order'             => $orderby,
			'pageToken'         => $page,
		);

		$request = wpcp_remote_get( 'https://www.googleapis.com/youtube/v3/search', $query_args );


		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$items = $response->items;

		$links = [];

		foreach ( $items as $item ) {

			$image = '';

			$url = esc_url( 'https://www.youtube.com/watch?v=' . $item->id->videoId );


			$title   = @ ! empty( $item->snippet->title ) ? @sanitize_text_field( $item->snippet->title ) : '';
			$content = @ ! empty( $item->snippet->description ) ? @esc_html( $item->snippet->description ) : '';

			if ( ! empty( $item->snippet->thumbnails ) && is_array( $item->snippet->thumbnails ) ) {
				$last_image = end( $item->snippet->thumbnails );

				$image = @ ! empty( $last_image->url ) ? esc_url( $last_image->url ) : '';

			}


			$links[] = array(
				'title'       => $title,
				'content'     => $content,
				'url'         => $url,
				'image'       => $image,
				'raw_content' => serialize( $item ),
				'score'       => '0',
				'gmt_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $item->snippet->publishedAt ) ),
				'status'      => 'fetched',
			);
		}

		$this->set_page_number( $response->nextPageToken );

		return $links;

	}

	public function get_post( $link ) {

	}

}
