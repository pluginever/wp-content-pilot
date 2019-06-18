<?php
/**
 * Flickr Class
 *
 * @package     WP Content Pilot
 * @subpackage  Flickr
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCP_Flickr extends WPCP_Campaign {

	protected $api_key;

	/**
	 * WPCP_Flicker constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_fetching_campaign_contents', array( $this, 'prepare_contents' ) );

		add_filter( 'wpcp_replace_template_tags', array( $this, 'replace_template_tags' ), 10, 2 );
	}

	/**
	 * Get WPCP_Envato default template tags
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_default_template() {
		$template
			= <<<EOT
<img src="{image_url}" alt="">
<br>
<a href="{image_url}">Posted</a> by <a href="http://flicker.com/{author_url}">{author}</a>
<br>
{tags}
<br>
<a href="{source_url}">Source</a>
EOT;

		return $template;
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
		$modules['flickr'] = [
			'title'       => __( 'Flickr', 'wp-content-pilot' ),
			'description' => __( 'Scraps photos based on keywords from flickr', 'wp-content-pilot' ),
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
			'title'      => __( 'Title', 'wp-content-pilot' ),
			'content'    => __( 'Content', 'wp-content-pilot' ),
			'date'       => __( 'Published date', 'wp-content-pilot' ),
			'image_url'  => __( 'Main image url', 'wp-content-pilot' ),
			'source_url' => __( 'Source link', 'wp-content-pilot' ),
			'author'     => __( 'Author Name', 'wp-content-pilot' ),
			'author_url' => __( 'Author Url', 'wp-content-pilot' ),
			'tags'       => __( 'Photo Tags', 'wp-content-pilot' ),
			'views'      => __( 'Photo Views', 'wp-content-pilot' ),
			'user_id'    => __( 'User Id', 'wp-content-pilot' ),
		);
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

		if ( 'flickr' != $link->camp_type ) {
			return false;
		}

		$request = wpcp_remote_get( $link->url );

		$response = wpcp_retrieve_body( $request );

		$title       = $response->photo->title->_content;
		$description = @$response->photo->description->_content;
		$tags        = ! empty( $response->photo->tags->tag ) ? implode( ', ', wp_list_pluck( @$response->photo->tags->tag, 'raw' ) ) : '';
		$image_url   = "http://farm{$response->photo->farm}.staticflickr.com/{$response->photo->server}/{$response->photo->id}_{$response->photo->secret}.jpg";
		$source_url  = $response->photo->urls->url[0]->_content;

		$article = array(
			'author'     => $response->photo->owner->username,
			'author_url' => "https://www.flickr.com/photos/{$response->photo->owner->nsid}/",
			'tags'       => $tags,
			'views'      => $response->photo->views,
			'user_id'    => $response->photo->owner->nsid,
		);

		wpcp_update_link( $link->id, array(
			'title'       => $title,
			'url'         => $source_url,
			'gmt_date'    => gmdate( 'Y-m-d H:i:s', $response->photo->dates->posted ),
			'content'     => $description,
			'image'       => $image_url,
			'raw_content' => serialize( $article ),
			'score'       => wpcp_get_read_ability_score( $description ),
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

		if ( 'flickr' !== $article['campaign_type'] ) {
			return $content;
		}

		$link        = wpcp_get_link( $article['link_id'] );
		$raw_content = maybe_unserialize( $link->raw_content );

		foreach ( $raw_content as $tag => $tag_content ) {
			$content = str_replace( '{' . $tag . '}', $tag_content, $content );
		}

		return $content;
	}

	public function setup() {

		$api_key = wpcp_get_settings( 'api_key', 'wpcp_settings_flickr', '' );

		if ( empty( $api_key ) ) {

			$msg = __( 'Flickr API Key is not set, The campaign won\'t work without API Key.', 'wp-content-pilot' );
			wpcp_log( $msg );

			return new \WP_Error( 'invalid-api-settings', $msg );
		}

		$this->api_key = $api_key;

		return true;
	}

	public function discover_links() {

		$total_page_uid = $this->get_uid( 'total_page' );
		$total_page     = wpcp_get_post_meta( $this->campaign_id, $total_page_uid, 0 );
		$page           = $this->get_page_number( '1' );
		$keywords       = wpcp_get_post_meta( $this->campaign_id, '_keywords', '' );
		$per_page       = 50;

		if ( $page > $total_page && ! empty( $total_page ) ) {
			$msg = sprintf( __( 'Maximum page number reached for the keyword %s', 'wp-content-pilot' ), $keywords );
			wpcp_log( $msg );
			wpcp_disable_campaign( $this->campaign_id );

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

			$msg = sprintf( __( 'Could not find any result for the keyword %s', 'wp-content-pilot' ), $keywords );
			wpcp_log( $msg );

			return new \WP_Error( 'no-links-in-response', $msg );
		}

		$items = $response->photos;

		if ( empty( $total_page ) ) {
			$total_page = ( $items->total / $per_page );
			update_post_meta( $this->campaign_id, $total_page_uid, $total_page );
		}

		$links = [];

		foreach ( $items->photo as $item ) {

			$title = ! empty( $item->title ) ? sanitize_text_field( $item->title ) : '';

			$url = esc_url_raw( "https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key={$this->api_key}&photo_id={$item->id}&secret={$item->secret}&format=json&nojsoncallback=1}" );

			$links[] = array(
				'title' => $title,
				'url'   => $url
			);

		}

		$next_page = intval( $page ) + 1;

		if ( $next_page == $response->photos->pages ) {
			wpcp_disable_keyword( $this->campaign_id, $this->keyword );
		} else {
			$this->set_page_number( intval( $response->photos->page ) + 1 );
		}

		return $links;

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
