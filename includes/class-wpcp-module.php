<?php
/**
 * WPCP_Module Class
 *
 * @package     WP Content Pilot
 * @subpackage  Module
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit();

abstract class WPCP_Module {
	/**
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
	protected $initiator;

	/**
	 * @var \Curl\Curl
	 */
	protected $curl;

	/**
	 * @var array
	 */
	protected $errors;

	/**
	 * @return string
	 * @since 1.2.0
	 */
	abstract public function get_campaign_type();

	/**
	 * @return array
	 */
	abstract public function register_module( $modules );

	/**
	 * @return string
	 */
	abstract public function get_module_icon();

	/**
	 * @return array
	 */
	abstract public function get_template_tags();

	/**
	 * @return string
	 */
	abstract public function get_default_template();

	/**
	 * @param $post
	 *
	 * @return void
	 */
	abstract public function add_campaign_option_fields( $post );

	/**
	 * Save campaign meta
	 *
	 * @param $campaign_id
	 * @param $posted
	 *
	 * @return void
	 */
	abstract public function save_campaign_meta( $campaign_id, $posted );

	/**
	 * get setting section
	 *
	 * @param $section
	 *
	 * @return array
	 */
	abstract public function get_setting_section( $section );

	/**
	 * Get setting fields
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	abstract public function get_setting_fields( $fields );


	/**
	 * @param $keyword
	 *
	 * @return array|WP_Error
	 * @since 1.2.0
	 */
	abstract public function get_post( $keyword );


	/**
	 * @param $campaign_id
	 * @param string $user
	 *
	 * @return int|WP_Error
	 * @since 1.2.0
	 */
	public function process_campaign( $campaign_id, $keywords = null, $user = 'cron' ) {
		wpcp_logger()->debug( sprintf( 'Processing campaign id# [%d] Keyword#[%s] and user#[%s]', $campaign_id, $keywords, $user ) );

		//todo uncomment this
//		$wp_post = get_post( $campaign_id );
//		if ( ! $wp_post || 'wp-content-pilot' !== $wp_post->post_type || 'publish' !== $wp_post->post_status ) {
//			wpcp_logger()->error( 'Could not find any campaign with the provided id');
//			return new WP_Error( 'invalid-campaign-id', __( 'Could not find any campaign with the provided id', 'wp-content-pilot' ) );
//		}
//
//		$campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );
//		if ( $campaign_type !== $this->get_campaign_type() ) {
//			wpcp_logger()->error( 'Campaign type mismatch');
//			return new WP_Error( 'invalid-campaign-id', __( 'Campaign type mismatch', 'wp-content-pilot' ) );
//		}

		$this->campaign_id   = absint( $campaign_id );
		$this->campaign_type = $this->get_campaign_type();
		$this->initiator     = sanitize_text_field( $user );

		if ( empty( $keywords ) ) {
			$keywords = $this->get_keywords( $this->campaign_id );
			if ( empty( $keywords ) ) {
				$message = __( 'Campaign do not have keywords to proceed, please set keyword', 'wp-content-pilot' );
				wpcp_logger()->error( $message, $this->campaign_id );

				return new WP_Error( 'missing-data', $message );
			}
		}

		$keywords = wpcp_string_to_array( $keywords );

		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		$article = $this->get_post( $keywords );
		if ( is_wp_error( $article ) ) {
			wpcp_logger()->error( $article->get_error_message() );

			return $article;
		}

		if ( ! $article ) {
			return new WP_Error( 'no-response', __( 'Content Pilot did not responded for the action', 'wp-content-pilot' ) );
		}

		$article = wp_parse_args( $article, array(
			'title'      => '',
			'author'     => '',
			'image_url'  => '',
			'excerpt'    => '',
			'language'   => '',
			'content'    => '',
			'source_url' => '',
		) );

		//check if acceptance passed if not then return this method again
		$accepted = apply_filters( 'wpcp_acceptance_check', true, $article, $this->campaign_id, $this );
		if ( ! $accepted ) {
			wpcp_logger()->debug( 'Article failed in acceptance test' );

			return $this->process_campaign( $campaign_id, $keyword, $user );
		}


		//truncate content
		$limit_title = wpcp_get_post_meta( $this->campaign_id, '_title_limit', 0 );
		if ( ! empty( $limit_title ) && $limit_title > 0 ) {
			$article['title'] = wp_trim_words( $article['title'], $limit_title );
		}

		$limit_content = wpcp_get_post_meta( $this->campaign_id, '_content_limit', 0 );
		if ( ! empty( $limit_content ) && $limit_content > 0 ) {
			//previously use wp_trim_words but it remove all html tag from content
			//that's why use custom function wpcp_truncate_content from allow html in content
			$article['content'] = wpcp_truncate_content( $article['content'], $limit_content );
		}

		//strip links
		$remove_hyper_links = wpcp_get_post_meta( $this->campaign_id, '_strip_links', 0 );
		if ( 'on' === $remove_hyper_links ) {
			//keep text
			$article['content'] = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', html_entity_decode( $article['content'] ) );
			//remove text
			/*$content =  preg_replace( '#<a.*?>(.*?)</a>#i', '', $content );*/
		}

		//remove images links
		$remove_image_links = wpcp_get_post_meta( $this->campaign_id, '_remove_images', 0 );
		if ( 'on' === $remove_image_links ) {
			$article['content'] = preg_replace( '/<img[^>]+\>/mi', '', html_entity_decode( $article['content'] ) );
		}

		//open links in new tab & add rel nofollow
		if ( 'on' == wpcp_get_post_meta( $this->campaign_id, '_add_rel_no_follow_target', '' )
		     && function_exists( 'wpcp_pro_add_no_follow_blank_target' ) ) {
			$article['content'] = wpcp_pro_add_no_follow_blank_target( $article['content'] );
		}


		//translate

		//make template of title,content,meta

		//translate template
		$content = wpcp_get_post_meta( $this->campaign_id, '_post_template', '' );
		$title   = wpcp_get_post_meta( $this->campaign_id, '_post_title', '' );
		$tags    = array_keys( $this->get_template_tags() );
		foreach ( $tags as $tag ) {
			if ( array_key_exists( $tag, $article ) ) {
				$content = str_replace( '{' . $tag . '}', $article[ $tag ], $content );
				$title   = str_replace( '{' . $tag . '}', $article[ $tag ], $title );
			}

			$content = html_entity_decode( $content );
			$title   = html_entity_decode( $title );
		}

		// replacing the keywords with affiliate links

		//replacing patterns

		//spin

		//taxonomies
		$post_tax = [];
		//category handles
		$categories = wpcp_get_post_meta( $this->campaign_id, '_categories', [] );
		if ( ! empty( $categories ) ) {
			$post_tax['category'] = array_map( 'intval', $categories );
		}
		//tags handles
		$tags = wpcp_get_post_meta( $this->campaign_id, '_tags', [] );
		if ( ! empty( $tags ) ) {
			$post_tax['post_tag'] = array_map( 'intval', $tags );
		}

		//Pending for transation fail

		//prepare author

		//fix invalid utf chars
		$title   = wpcp_fix_utf8( $title );
		$content = wpcp_fix_utf8( $content );


		//fix emoji
		$title   = wpcp_remove_emoji( $title );
		$content = wpcp_remove_emoji( $content );


		// add featured image

		//delete first image from content

		//if image not found set pending

		//amazon woocommerce integration

		//populate custom fields


		//summery
		$summary        = '';
		$insert_excerpt = wpcp_get_post_meta( $this->campaign_id, '_excerpt', '' );
		if ( 'on' == $insert_excerpt ) {
			$excerpt_length = wpcp_get_post_meta( $this->campaign_id, '_excerpt_length', 55 );
			$summary        = empty( $article['excerpt'] ) ? $article['content'] : $article['excerpt'];
			$summary        = strip_tags( $summary );
			$summary        = strip_shortcodes( $summary );
			$summary        = wp_trim_words( $summary, $excerpt_length );
		}

		//author id
		$author_id = get_post_field( 'post_author', $this->campaign_id, 'edit' );
		$author_id = wpcp_get_post_meta( $this->campaign_id, '_author', $author_id );

		//post time
		$post_time = current_time( 'mysql' );

		//post
		do_action( 'wpcp_before_post_insert', $this->campaign_id, $article );
		$post_type      = wpcp_get_post_meta( $this->campaign_id, '_post_type', 'post' );
		$post_status    = wpcp_get_post_meta( $this->campaign_id, '_post_status', 'post' );
		$title          = apply_filters( 'wpcp_post_title', $title, $this->campaign_id, $article );
		$post_content   = apply_filters( 'wpcp_post_content', $content, $this->campaign_id, $article );
		$post_excerpt   = apply_filters( 'wpcp_post_excerpt', $summary, $this->campaign_id, $article );
		$post_author    = apply_filters( 'wpcp_post_author', $author_id, $this->campaign_id, $article );
		$post_type      = apply_filters( 'wpcp_post_type', $post_type, $this->campaign_id, $article );
		$post_status    = apply_filters( 'wpcp_post_status', $post_status, $this->campaign_id, $article );
		$post_meta      = apply_filters( 'wpcp_post_meta', [], $this->campaign_id, $article );
		$post_tax       = apply_filters( 'wpcp_post_taxonomy', $post_tax, $this->campaign_id, $article );
		$post_time      = apply_filters( 'wpcp_post_time', $post_time, $this->campaign_id, $article );
		$comment_status = apply_filters( 'wpcp_post_comment_status', get_default_comment_status( $post_type ), $this->campaign_id, $article );
		$ping_status    = apply_filters( 'wpcp_post_ping_status', get_default_comment_status( $post_type, 'pingback' ), $this->campaign_id, $article );


		/**
		 * Filter to manipulate postarr param before insert a post
		 *
		 * @param array
		 *
		 * @since 1.0.3
		 *
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

		/**
		 * @since 1.0.8
		 * set user when insert post using background process
		 */
		$user = get_user_by( 'ID', $post_author );
		if ( $user ) {
			wp_set_current_user( $post_author, $user->user_login );
		}

		$post_id = wp_insert_post( $postarr, true );

		if ( is_wp_error( $post_id ) ) {
			do_action( 'wpcp_post_insertion_failed', $this->campaign_id );

			return $post_id;
		}

		//set featured image
		$is_set_featured_image = wpcp_get_post_meta( $this->campaign_id, '_set_featured_image', 0 );
		if ( 'on' === $is_set_featured_image ) {
			if ( ! empty( $article['image_url'] ) ) {
				wpcp_logger()->debug( 'Setting featured image' );
				$attachment_id = wpcp_download_image( html_entity_decode( $article['image_url'] ) );
				if ( $attachment_id ) {
					set_post_thumbnail( $post_id, $attachment_id );
					update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
				}
			} else {
				if ( 'on' == wpcp_get_post_meta( $this->campaign_id, '_random_featured_image', '' ) && function_exists( 'wpcp_pro_set_random_featured_image' ) ) {
					wpcp_pro_set_random_featured_image( $post_id );
				}
			}
		}


		//wpml internal cron patch

		//wpml integration

		//setting categories for custom post types

		//if link canonical _yoast_wpseo_canonical
		if ( 'on' == wpcp_get_post_meta( $this->campaign_id, '_canonical_tag', '' ) && function_exists( 'wpcp_pro_add_canonical_tag' ) ) {
			wpcp_pro_add_canonical_tag( $post_id, $article['source_url'] );
		}


		update_post_meta( $this->campaign_id, '_last_post', $post_id );
		update_post_meta( $this->campaign_id, '_last_run', current_time( 'mysql' ) );
		update_post_meta( $this->campaign_id, 'wpcp_last_ran_campaign', current_time( 'mysql' ) );
		$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
		update_post_meta( $campaign_id, '_post_count', ( $posted + 1 ) );
		do_action( 'wpcp_after_post_publish', $post_id, $this->campaign_id, $article );

		return $post_id;
	}

	/**
	 * @return \Curl\Curl
	 * @since 1.2.0
	 */
	public function setup_curl() {
		$curl = new Curl\Curl();
		$curl->setOpt( CURLOPT_FOLLOWLOCATION, true );
		$curl->setOpt( CURLOPT_TIMEOUT, 30 );
		$curl->setOpt( CURLOPT_RETURNTRANSFER, true );
		$curl->setOpt( CURLOPT_REFERER, 'http://www.bing.com/' );
		$curl->setOpt( CURLOPT_USERAGENT, wpcp_get_random_user_agent() );

		return $curl;
	}


	/**
	 * Deactivate key for hours
	 *
	 * @param $campaign_id
	 * @param $keyword
	 * @param int $hours
	 *
	 * @since 1.2.0
	 */
	public function deactivate_key( $campaign_id, $keyword, $hours = 1 ) {
		$deactivated_until = current_time( 'timestamp' ) + ( $hours * HOUR_IN_SECONDS );
		update_post_meta( $campaign_id, '_' . md5( $keyword ), $deactivated_until );
	}

	/**
	 * Check if the keyword is deactivated
	 *
	 * @param $campaign_id
	 * @param $keyword
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public function is_deactivated_key( $campaign_id, $keyword ) {
		$deactivated_until = wpcp_get_post_meta( $campaign_id, '_' . md5( $keyword ), '' );
		if ( empty( $deactivated_until ) || $deactivated_until < current_time( 'timestamp' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get unique string for the campaign
	 *
	 * @param string $keyword
	 * @param string $extra
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_unique_key( $keyword = '', $extra = '' ) {
		$key = '_wpcp_' . $this->campaign_id . '-' . $this->campaign_type . '-' . $keyword . '-' . $extra;

		return sanitize_title( $key );
	}

	/**
	 * @param $keyword
	 * @param string $status
	 *
	 * @return array|object|void|null
	 * @since 1.2.0
	 */
	public function get_links( $keyword, $status = 'new', $count = 5 ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->wpcp_links WHERE keyword=%s AND camp_id=%d AND status=%s LIMIT %d", $keyword, $this->campaign_id, $status, $count ) );
	}

	/**
	 * @param $data
	 *
	 * @return false|int
	 * @since 1.2.0
	 */
	public function insert_link( $data ) {
		$data = wp_parse_args( $data, array(
			'camp_id'      => $this->campaign_id,
			'camp_type'    => $this->campaign_type,
			'url'          => '',
			'title'        => '',
			'keyword'      => '',
			'pub_date_gmt' => '',
			'status'       => 'new',
			'date_created' => current_time( 'mysql' ),
		) );
		global $wpdb;

		return $wpdb->insert( $wpdb->wpcp_links, $data );
	}

	/**
	 * @param $id
	 * @param array $data
	 *
	 * @return false|int
	 * @since 1.2.0
	 */
	public function update_link( $id, $data = array() ) {
		global $wpdb;

		return $wpdb->update( $wpdb->wpcp_links, $data, [ 'id' => absint( $id ) ] );
	}


	/**
	 * @since 1.2.0
	 * @param $campaign_id
	 *
	 * @return array|string|null
	 */
	public function get_keywords( $campaign_id ) {
		return wpcp_get_post_meta( $this->campaign_id, '_keywords', '' );
	}
}
