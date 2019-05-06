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

	/**
	 * Discover links
	 *
	 * @since 1.0.0
	 * @return array|\WP_Error
	 */
	abstract function discover_links();

	abstract function get_post( $link );

	/**
	 * setup campaign id
	 *
	 * @since 1.0.0
	 *
	 * @param $campaign_id
	 */
	public function set_campaign_id( $campaign_id ) {
		$this->campaign_id   = intval( $campaign_id );
		$this->campaign_type = wpcp_get_post_meta( $campaign_id, '_campaign_type', 'feed' );
	}

	/**
	 * keyword for the campaign
	 *
	 * @since 1.0.0
	 *
	 * @param $keyword
	 */
	public function set_keyword( $keywords ) {
		$keywords      = strip_tags( $keywords );
		$keywords      = wpcp_string_to_array( $keywords );
		$keyword       = $keywords[ array_rand( $keywords ) ];
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
		wpcp_log( "Running campaign #ID{$this->campaign_id} Type {$this->campaign_type} Keyword {$this->keyword}" );
		if ( empty( $this->campaign_id ) || empty( $this->keyword ) || empty( $this->campaign_type ) ) {
			wpcp_log( 'Campaign is not initiated correctly, missing ID||keyword' );

			return new WP_Error( 'doing-wrong', __( 'Campaign is not initiated correctly, missing ID||keyword', 'wp-content-pilot' ) );
		}

		wpcp_log( 'Looking for link to run campaign' );
		$link = $this->get_link();
		if ( ! $link ) {
			//check if there is already links but not ready yet
			$total_fetched_links = $this->count_links( 'fetched' );

			wpcp_log( 'Could not find available links Total Fetched Link ' . $total_fetched_links );

			if ( $total_fetched_links > 1 ) {
				wpcp_log( 'Link discovery skipped because there already links waiting for getting ready ' . $total_fetched_links );

				return new \WP_Error( 'no-ready-links', __( 'Please wait links generated but not ready to run campaign yet.', 'content-pilot' ) );
			}

			//otherwise discover few new links
			wpcp_log( 'Discovering links' );
			$links = $this->discover_links();

			if ( is_wp_error( $links ) ) {
				wpcp_log( 'Error in discovering links Message' . $links->get_error_message() );

				return $links;
			}

			//hook here for any link to subtract
			wpcp_log( 'Generated total links ' . count( $links ) );
			$links = apply_filters( 'wpcp_fetched_links', $links, $this->campaign_id, $this->campaign_type );

			if ( empty( $links ) ) {
				return new \WP_Error( 'no-links-found', __( 'Could not retrieve any valid links', 'wp-content-pilot' ) );
			}

			//check the result
			$urls        = wp_list_pluck( $links, 'url' );
			$string_urls = implode( ',', $urls );

			if ( $this->is_result_like_last_time( $string_urls ) ) {
				$msg = __( sprintf( 'Could not discover any new links to grab contents for the keyword "%s". Please try letter.', $this->keyword ), 'wp-content-pilot' );
				wpcp_log( $msg, 'log' );

				return new \WP_Error( 'no-new-result', $msg );
			}


			$inserted = $this->inset_links( $links );

			wpcp_log( __( sprintf( 'Total %d links inserted', $inserted ), 'wp-content-pilot' ), 'log' );

			$link                = $this->get_link();
			$total_fetched_links = $this->count_links( 'fetched' );
			if ( $total_fetched_links > 1 ) {
				return new \WP_Error( 'no-ready-links', __( 'Please wait links generated but not ready to run campaign yet.', 'content-pilot' ) );
			}

			if ( empty( $total_fetched_links ) && empty( $link ) ) {
				return new \WP_Error( 'no-valid-links-found', __( 'Could not retrieve any valid links. Please wait to generate new links.', 'content-pilot' ) );
			}
		}

		//set link as failed if run till end then mark as success
		wpcp_update_link( $link->id, [ 'status' => 'failed' ] );

		$article = $this->get_post( $link );

		if ( is_wp_error( $article ) ) {
			return $article;
		}

		/*=========================CHECK FOR ACCEPTANCE=========================*/

		//minimum content check
		$is_required_length = wpcp_get_post_meta( $this->campaign_id, '_min_words', 0 );
		if ( ! empty( $is_required_length ) ) {
			$words_count = str_word_count( $article['content'] );
			if ( $words_count < $is_required_length ) {
				return new WP_Error( 'lack-of-content', sprintf( __( "Post is rejected due to less content. Required %d Found %d", 'wp-content-pilot' ), $is_required_length, $words_count ) );
			}
		}

		//duplicate check
		$is_duplicate_title = wpcp_get_post_meta( $this->campaign_id, '_skip_duplicate_title', 0 );
		if ( 'on' === $is_duplicate_title ) {
			$post_type    = wpcp_get_post_meta( $this->campaign_id, '_post_type', 'post' );
			$is_duplicate = get_page_by_title( $article['title'], OBJECT, $post_type );
			if ( $is_duplicate ) {
				return new WP_Error( 'duplicate-post', __( 'Post is rejected because post with same title exit.', 'wp-content-pilot' ) );
			}
		}

		//skip post wihtout images
		$is_required_img = wpcp_get_post_meta( $this->campaign_id, '_skip_no_image', 0 );
		if ( 'on' === $is_required_img && empty( $article['image_url'] ) ) {
			return new WP_Error( 'no-image-found', __( 'Post is rejected because could not find any image in the post.', 'wp-content-pilot' ) );
		}

		//allow 3rd party to hook
		$passed_acceptance_test = apply_filters( 'wpcp_acceptance_check', true, $this->campaign_id, $article );
		if ( true !== $passed_acceptance_test ) {
			return $passed_acceptance_test;
		}


		$post_time = current_time( 'mysql' );
		$summary   = '';
		$author_id = get_post_field( 'post_author', $this->campaign_id, 'edit' );

		/*=========================BEFORE INSERTING POST =========================*/
		//use original post date
		$use_original_date = wpcp_get_post_meta( $this->campaign_id, '_use_original_date', 0 );
		if ( 'on' === $use_original_date && !empty( $article['date'] ) ) {
			$post_time = $article['date'];
		}

		//insert post summary
		$use_post_summary = wpcp_get_post_meta( $this->campaign_id, '_excerpt', 0 );
		if ( 'on' === $use_post_summary && ! empty( $article['content'] ) ) {
			$summary = wp_trim_words( $article['content'], 55 );
			$summary = strip_tags( $summary );
			$summary = strip_shortcodes( $summary );
		}
		//is custom author


		//remove images links
		$remove_image_links = wpcp_get_post_meta( $this->campaign_id, '_remove_images', 0 );
		if ( 'on' === $remove_image_links ) {
			$article['content'] = preg_replace( '#<img.*?>.*?>#i', '', html_entity_decode( $article['content'] ) );
		}

		//remove hyper links
		$remove_hyper_links = wpcp_get_post_meta( $this->campaign_id, '_strip_links', 0 );
		if ( 'on' === $remove_hyper_links ) {
			//keep text
			$article['content'] = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', html_entity_decode( $article['content'] ) );
			//remove text
			/*$content =  preg_replace( '#<a.*?>(.*?)</a>#i', '', $content );*/

		}

		$limit_title = wpcp_get_post_meta( $this->campaign_id, '_title_limit', 0 );
		if ( ! empty( $limit_title ) && $limit_title > 0 ) {
			$article['title'] = wp_trim_words( $article['title'], $limit_title );
		}

		$limit_content = wpcp_get_post_meta( $this->campaign_id, '_content_limit', 0 );
		if ( ! empty( $limit_content ) && $limit_content > 0 ) {
			$article['content'] = wp_trim_words( $article['content'], $limit_content );
		}


		//apply limit

		//translate template
		$content_template = wpcp_get_post_meta( $this->campaign_id, '_post_template', '' );
		if ( ! empty( $content_template ) ) {
			$content = wpcp_replace_template_tags( $content_template, $article );
		}

		$title_template = wpcp_get_post_meta( $this->campaign_id, '_post_title', '' );
		if ( ! empty( $title_template ) ) {
			$title = wpcp_replace_template_tags( $title_template, $article );
		}


		//post
		do_action( 'wpcp_before_post_insert', $this->campaign_id, $article, $this->keyword );

		$readability_score_passed = apply_filters( 'wpcp_check_readability_score', true, $this->campaign_id, $article );

		if ( ! $readability_score_passed ) {
			return new WP_Error( 'score', __( 'Readability score faild!', 'wp-content-pilot' ) );
		}

		$title          = apply_filters( 'wpcp_post_title', $title, $this->campaign_id, $article, $this->keyword );
		$post_content   = apply_filters( 'wpcp_post_content', $content, $this->campaign_id, $article, $this->keyword );
		$post_excerpt   = apply_filters( 'wpcp_post_excerpt', $summary, $this->campaign_id, $article, $this->keyword );
		$post_author    = apply_filters( 'wpcp_post_author', $author_id, $this->campaign_id, $article, $this->keyword );
		$post_type      = apply_filters( 'wpcp_post_type', 'post', $this->campaign_id, $article, $this->keyword );
		$post_status    = apply_filters( 'wpcp_post_status', 'publish', $this->campaign_id, $article, $this->keyword );
		$post_meta      = apply_filters( 'wpcp_post_meta', [], $this->campaign_id, $article, $this->keyword );
		$post_tax       = apply_filters( 'wpcp_post_taxonomy', [], $this->campaign_id, $article, $this->keyword );
		$post_time      = apply_filters( 'wpcp_post_time', $post_time, $this->campaign_id, $article, $this->keyword );
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
			do_action( 'wpcp_post_insertion_failed', $this->campaign_id, $this->keyword );

			return $post_id;
		}

		/*=========================AFTER POST STUFF=========================*/
		//set featured image
		$is_set_featured_image = wpcp_get_post_meta( $this->campaign_id, '_set_featured_image', 0 );
		if ( 'on' === $is_set_featured_image && ! empty( $article['image_url'] ) ) {
			$attachment_id = wpcp_download_image( $article['image_url'] );
			if ( $attachment_id ) {
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}
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
	 * @param string $type
	 *
	 * @return object|boolean
	 */
	protected function get_link( $type = 'ready' ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'wpcp_links';
		$sql    = $wpdb->prepare( "select * from {$table} where keyword = %s and camp_id  = %s and camp_type= %s and status = %s limit 1",
			$this->keyword,
			$this->campaign_id,
			$this->campaign_type,
			$type
		);
		$result = $wpdb->get_row( $sql );
		//$result = $wpdb->get_row( "select * from {$table} where id='104'" );
		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * get total links available
	 *
	 * @since 1.0.0
	 *
	 * @param string $status
	 *
	 * @return null|string
	 */
	protected function count_links( $status = 'ready' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wpcp_links';
		$sql   = $wpdb->prepare( "select count(id) from {$table} where keyword = %s and camp_id  = %s and camp_type= %s and status = %s limit 1",
			$this->keyword,
			$this->campaign_id,
			$this->campaign_type,
			$status
		);

		return $wpdb->get_var( $sql );
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
			'gmt_date'    => empty( $args['gmt_date'] ) ? null : esc_attr( $args['gmt_date'] ),
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
	public function get_uid( $string = '' ) {
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
	 * Search in bing
	 *
	 * since 1.0.0
	 *
	 * @param        $keywords
	 * @param int    $page
	 * @param string $result_group
	 *
	 * @return array|mixed|object
	 */
	public function bing_search( $keywords, $page = 0, $result_group = 'channel.item' ) {
		$request  = wpcp_remote_get( 'https://www.bing.com/search', array(
			'q'      => $keywords,
			'count'  => 100,
			'loc'    => 'en',
			'format' => 'rss',
			'first'  => ( $page * 10 ),
		) );
		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			return [];
		}
		if ( ! $response instanceof \SimpleXMLElement ) {
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
