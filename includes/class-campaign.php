<?php
/**
 * Campaign Class
 *
 * @package     WP Content Pilot
 * @subpackage  Campaign
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPCP_Campaign {
	/**
	 * This is actually a post ID
	 *
	 * @var int
	 */
	protected $campaign_id;

	/**
	 * @var string
	 */
	protected $campaign_type;

	/**
	 * @var string
	 */
	protected $keyword;

	/**
	 * @var string
	 */
	protected $campaign_title;


	abstract function register_module( $modules );

	abstract function setup();

	abstract function discover_links();

	abstract function fetch_post( $link );

	/**
	 * setup campaign id
	 *
	 * @since 1.0.0
	 *
	 * @param $campaign_id
	 */
	public function set_campaign_id( $campaign_id ) {
		$this->campaign_id   = intval( $campaign_id );
		$this->campaign_type = get_post_type( $campaign_id );
	}

	/**
	 * keyword for the campaign
	 *
	 * @since 1.0.0
	 *
	 * @param $keyword
	 */
	public function set_keyword( $keyword ) {
		$this->keyword = strip_tags( $keyword );
	}

	/**
	 * set campaign type
	 *
	 * @since 1.0.0
	 *
	 * @param $campaign_type
	 */
	public function set_campaign_type( $campaign_type ) {
		$this->campaign_type = $campaign_type;
	}


	/**
	 * run the campaign
	 *
	 * @since 1.0.0
	 * @return int|\WP_Error
	 */
	public function run() {
		if ( empty( $this->campaign_id ) || empty( $this->keyword ) || empty( $this->campaign_type ) ) {
			return new WP_Error( 'doing-wrong', __( 'Campaign is not initiated correctly, missing ID||keyword', 'wp-content-pilot' ) );
		}


		$link = $this->get_link();
		if ( ! $link ) {
			$links = $this->discover_links();

			if ( is_wp_error( $links ) ) {
				return $links;
			}

			//hook here for any link to subtract
			$links = apply_filters( 'wpcp_fetched_links', $links, $this->campaign_id, $this->campaign_type );

			if ( empty( $links ) ) {
				return new \WP_Error( 'no-links-found', __( 'Could not retrieve any valid links', 'wp-content-pilot' ) );
			}

			//check the result

			$urls = wp_list_pluck($links, 'url');
			$string_urls = implode(',', $urls);

			if ( $this->is_result_like_last_time( $string_urls ) ) {
				$msg = __( sprintf( 'Could not discover any new links to grab contents for the keyword "%s". Please try letter.', $this->keyword ), 'wp-content-pilot' );
				wpcp_log( $msg, 'log' );

				return new \WP_Error( 'no-new-result', $msg );
			}


			$inserted = $this->inset_links( $links );

			wpcp_log( __( sprintf( 'Total %d links inserted', $inserted ), 'wp-content-pilot' ), 'log' );

			$link = $this->get_link();
			if ( $link ) {
				return new \WP_Error( 'no-valid-links-found', __( 'Could not retrieve any valid links. Please wait to generate new links.', 'content-pilot' ) );
			}
		}

		//set link as failed if run till end then mark as success
		wpcp_update_link( $link->id, [ 'status' => 'failed' ] );

		$article = $this->fetch_post( $link );
		if ( is_wp_error( $article ) ) {
			return $article;
		}

		//check for acceptance of the article

		//post
		do_action( 'wpcp_before_post_insert', $this->campaign_id, $article, $this->keyword );

		$content = wpcp_remove_unauthorized_html( $article['content'] );
		$content = wpcp_remove_empty_tags_recursive( $content );

		$title          = apply_filters( 'wpcp_post_title', $article['title'], $this->campaign_id, $article, $this->keyword );
		$post_content   = apply_filters( 'wpcp_post_content', $content, $this->campaign_id, $article, $this->keyword );
		$summary        = wp_trim_words( $article['content'], 55 );
		$summary        = strip_shortcodes( strip_tags( $summary ) );
		$post_excerpt   = apply_filters( 'wpcp_post_excerpt', $summary, $this->campaign_id, $article, $this->keyword );
		$author_id      = get_post_field( 'post_author', $this->campaign_id, $this->keyword );
		$post_author    = apply_filters( 'wpcp_post_author', $author_id, $this->campaign_id, $article, $this->keyword );
		$post_type      = apply_filters( 'wpcp_post_type', 'post', $this->campaign_id, $article, $this->keyword );
		$post_status    = apply_filters( 'wpcp_post_status', 'publish', $this->campaign_id, $article, $this->keyword );
		$post_meta      = apply_filters( 'wpcp_post_meta', [], $this->campaign_id, $article, $this->keyword );
		$post_tax       = apply_filters( 'wpcp_post_taxonomy', [], $this->campaign_id, $article, $this->keyword );
		$post_time      = apply_filters( 'wpcp_post_time', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ), $this->campaign_id, $article, $this->keyword );
		$comment_status = apply_filters( 'wpcp_post_comment_status', get_default_comment_status( $post_type ), $this->campaign_id, $article, $this->keyword );
		$ping_status    = apply_filters( 'wpcp_post_ping_status', get_default_comment_status( $post_type, 'pingback' ), $this->campaign_id, $article, $this->keyword );

		/**
		 * Filter to manipulate postarr param before insert a post
		 *
		 * @since 1.0.3
		 *
		 * @param array
		 */
		$postarr = apply_filters( 'wpcp_insert_post_postarr', [
			'post_title'     => $title,
			'post_author'    => $post_author,
			'post_excerpt'   => $post_excerpt,
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'post_date'      => $post_time,
			'post_date_gmt'  => get_gmt_from_date( $post_time ),
			'post_content'   => $post_content,
			'meta_input'     => $post_meta,
			'tax_input'      => $post_tax,
			'comment_status' => $comment_status,
			'ping_status'    => $ping_status,
		], $this->campaign_id, $article );

		$post_id = wp_insert_post( $postarr, true );

		if ( is_wp_error( $post_id ) ) {
			wpcp_log( __( 'Post insertion failed Reason: ' . $post_id->get_error_message() ), 'critical' );
			do_action( 'wpcp_post_insertion_failed', $this->campaign_id, $this->keyword );

			return $post_id;
		}

		update_post_meta( $post_id, '_wpcp_campaign_generated_post', $this->campaign_id );

		do_action( 'wpcp_after_post_publish', $post_id, $this->campaign_id, $article, $this->keyword );

		//we are here that means the url was success
		wpcp_update_link( $link->id, [ 'status' => 'success', 'post_id' => $post_id ] );

		return $post_id;

	}

	/**
	 * Get new link
	 *
	 * @since 1.0.0
	 *
	 * @return object|bool
	 */
	protected function get_link() {
		global $wpdb;
		$table  = $wpdb->prefix . 'wpcp_links';
		$sql    = $wpdb->prepare( "select * from {$table} where keyword = %s and camp_id  = %s and camp_type= %s and status = 'fetched'",
			$this->keyword,
			$this->campaign_id,
			$this->campaign_type
		);
		$result = $wpdb->get_row( $sql );

		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}


	/**
	 * Checks the result if its like the last run
	 *
	 * @since 1.0.0
	 *
	 * @param $html
	 *
	 * @return bool
	 *
	 */
	protected function is_result_like_last_time( $html ) {
		$hash      = @md5( (string) $html );
		$last_feed = wpcp_get_post_meta( $this->campaign_id, $this->get_uid( 'last-result' ), '' );
		if ( $hash == $last_feed ) {
			return true;
		}

		update_post_meta( $this->campaign_id, $this->get_uid( 'last-result' ), $hash );

		return false;
	}

	protected function insert_link( $args ) {
		$id = wpcp_insert_link( array(
			'camp_id'     => $this->campaign_id,
			'post_id'     => empty( $args['post_id'] ) ? null : intval( $args['post_id'] ),
			'keyword'     => $this->keyword,
			'camp_type'   => $this->campaign_type,
			'status'      => empty( $args['status'] ) ? 'fetched' : esc_attr( $args['status'] ),
			'url'         => $args['url'],
			'title'       => empty( $args['title'] ) ? null : esc_attr( $args['title'] ),
			'image'       => empty( $args['image'] ) ? null : esc_attr( $args['image'] ),
			'content'     => empty( $args['content'] ) ? null : esc_attr( $args['content'] ),
			'raw_content' => empty( $args['raw_content'] ) ? '' : $args['raw_content'],
			'score'       => empty( $args['score'] ) ? null : esc_attr( $args['raw_content'] ),
		) );
		if ( $id ) {
			return true;
		}

		return false;
	}


	/**
	 * Insert links
	 *
	 * @since 1.0.0
	 *
	 * @param $links
	 *
	 * @return int
	 *
	 */
	protected function inset_links( $links ) {
		$counter = 0;
		foreach ( $links as $link ) {

			if ( $this->insert_link( $link ) ) {
				$counter ++;
			}
		}

		return $counter;
	}


	/**
	 * Get unique string for the campaign
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function get_uid( $string = '' ) {
		$string = '_wpcp_' . $this->campaign_id . '-' . $this->campaign_type . '-' . $this->keyword . '-' . $string;

		return sanitize_title( $string );
	}

	/**
	 * Get last page
	 *
	 * @since 1.0.0
	 *
	 * @param int $default
	 *
	 * @return int|mixed
	 *
	 */
	public function get_page_number( $default = 0 ) {
		$page = get_post_meta( $this->campaign_id, "page-" . $this->get_uid( 'page-number' ), true );

		return ! empty( $page ) ? $page : $default;
	}

	/**
	 * set the page number from where next query will be
	 *
	 * @since 1.0.0
	 *
	 * @param $number
	 *
	 */
	public function set_page_number( $number ) {
		update_post_meta( $this->campaign_id, "page-" . $this->get_uid( 'page-number' ), $number );
	}

	/**
	 * setup request
	 *
	 * @since 1.0.0
	 *
	 * @param null $url
	 *
	 * @return \Curl\Curl
	 * @throws \ErrorException
	 */
	public function setup_request( $url = null ) {
		return wpcp_setup_request( $this->campaign_type, $url, $this->campaign_id );
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param        $keywords
	 * @param int    $page
	 * @param string $result_group
	 *
	 * @return array|mixed|object
	 * @throws \ErrorException
	 */
	public function bing_search( $keywords, $page = 0, $result_group = 'channel.item' ) {
		try{
			$request = $this->setup_request( 'https://www.bing.com' );
		}catch (Exception $e){
			return new WP_Error('request-error', $e->getMessage());
		}

		$request->get( 'search', array(
			'q'      => $keywords,
			'count'  => 100,
			'loc'    => 'en',
			'format' => 'rss',
			'first'  => ( $page * 10 ),
		) );

		$response = wpcp_is_valid_response( $request );
		$request->close();

		if ( ! $response ) {
			return [];

		}

		if ( ! $response instanceof \SimpleXMLElement ) {
			wpcp_log( 'log', $response );
			$response = simplexml_load_string( $response );
		}

		$deJson    = json_encode( $response );
		$xml_array = json_decode( $deJson, true );
		if ( ! $xml_array ) {
			return [];
		}

		$response_array = $xml_array;

		$result_group_arr = explode( '.', $result_group );
		foreach ( $result_group_arr as $key ) {
			if ( empty( $response_array[ $key ] ) ) {
				return [];
				break;
			}
			$response_array = $response_array[ $key ];

		}

		return $response_array;

	}

}
