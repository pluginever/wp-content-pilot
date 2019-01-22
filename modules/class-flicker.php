<?php
/**
 * Flicker Class
 *
 * @package     WP Content Pilot
 * @subpackage  Flicker
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCP_Flicker extends WPCP_Campaign {

	protected $api_key;

	/**
	 * WPCP_Flicker constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_after_campaign_keyword_input', array( $this, 'campaign_option_fields' ), 10, 2 );
		add_action( 'wpcp_update_campaign_settings', array( $this, 'update_campaign_settings' ), 10, 2 );
		add_action( 'wpcp_fetching_campaign_contents', array( $this, 'prepare_contents' ) );

		add_filter( 'wpcp_replace_template_tags', array( $this, 'replace_template_tags' ), 10, 2 );
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
		$modules['flicker'] = [
			'title'       => __( 'Flicker', 'wp-content-pilot' ),
			'description' => __( 'Scraps photos based on keywords from flicker', 'wp-content-pilot' ),
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
		return array();
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

		if ( 'flicker' != $campaign_type ) {
			return false;
		}

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
	}

	/**
	 * Hook in background process and prepare contents
	 *
	 * @since 1.0.0
	 *
	 * @param $link
	 *
	 * @return bool|\WP_Error
	 */
	public function prepare_contents( $link ) {

		if ( 'flicker' != $link->camp_type ) {
			return false;
		}

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_flickr', '' );

		$article = array();

		wpcp_update_link( $link->id, array(
			//'content'     => $description,
			'raw_content' => serialize( $article ),
			//'score'       => wpcp_get_read_ability_score( $description ),
			'status'      => 'ready',
		) );
	}

	/**
	 * Replace additional template tags
	 *
	 * @since 1.0.0
	 *
	 * @param $content
	 * @param $article
	 *
	 * @return mixed
	 */
	public function replace_template_tags( $content, $article ) {

		if ( 'flicker' !== $article['campaign_type'] ) {
			return $content;
		}

		$link        = wpcp_get_link( $article['link_id'] );
		$raw_content = maybe_unserialize( $link->raw_content );

		foreach ( $raw_content as $tag => $tag_content ) {
			$content = str_replace( "{{$tag}}", $tag_content, $content );
		}

		return $content;
	}

	public function setup() {

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_flickr', '' );

		if ( empty( $api_key ) ) {

			$msg = __( 'Flicker API Key is not set, The campaign won\'t work without API Key.', 'wp-content-pilot' );
			wpcp_log( $msg );

			return new \WP_Error( 'invalid-api-settings', $msg );
		}

		$this->api_key = $api_key;

		return true;
	}

	public function discover_links() {

		$total_page_uid = $this->get_uid('total_page');
		$total_page = wpcp_get_post_meta($this->campaign_id, $total_page_uid, 0);
		$page     = $this->get_page_number( '1' );
		$keywords = wpcp_get_post_meta( $this->campaign_id, '_keywords', '' );
		$per_page = 50;

		if($page > $total_page && !empty($total_page)){
			$msg = sprintf(__( 'Maximum page number reached for the keyword %s', 'wp-content-pilot' ), $keywords);
			wpcp_log( $msg );
			wpcp_disable_campaign($this->campaign_id);
			return new \WP_Error( 'max-page', $msg );
		}

		$query_args = array(
			'text'           => $this->keyword,
			'api_key'        => $this->api_key,
			'sort'           => 'relevance',
			'content_type'   => 'photos',
			'media'          => 'photos',
			'per_page'       => $per_page,
			'page'           => $page,
			'format'         => 'json',
			'nojsoncallback' => '1',
			'method'         => 'flickr.photos.search',
		);

		$request = wpcp_remote_get( 'https://api.flickr.com/services/rest/', $query_args );

		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response->photos->photo ) ) {

			$msg = sprintf(__( 'Could not find any result for the keyword %s', 'wp-content-pilot' ), $keywords);
			wpcp_log( $msg );

			return new \WP_Error( 'no-links-in-response', $msg );
		}

		$items = $response->photos;

		if(empty($total_page)){
			$total_page = ($items->total / $per_page);
			update_post_meta($this->campaign_id, $total_page_uid, $total_page);
		}

		$links = [];

		foreach ( $items as $item ) {

			$image = '';


		}

	}

	public function get_post( $link ) {

		$article = array(
			'title'         => $link->title,
			'content'       => $link->content,
			'image_url'     => $link->image,
			'source_url'    => $link->url,
			'date'          => $link->gmt_date ? get_date_from_gmt( $link->gmt_date ) : current_time( 'mysql' ),
			'score'         => $link->score,
			'campaign_id'   => $link->camp_id,
			'campaign_type' => $link->camp_type,
			'link_id'       => $link->id
		);

		return $article;
	}


}
