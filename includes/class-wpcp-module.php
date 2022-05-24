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
	 * @var int
	 */
	protected $max_try = 3;

	/**
	 * @var int
	 */
	protected $try_count = 0;

	/**
	 * @param $campaign_type
	 *
	 * @since 1.2.0
	 * WPCP_Module constructor.
	 */
	public function __construct( $campaign_type ) {

		$this->campaign_type = $campaign_type;

		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );

		$hook_name = 'wpcp_' . $this->campaign_type;

		add_action( $hook_name . '_campaign_options_meta_fields', array( $this, 'add_campaign_option_fields' ) );
		add_action( $hook_name . '_update_campaign_settings', array( $this, 'save_campaign_meta' ), 10, 2 );

		//setting
		add_filter( 'wpcp_settings_sections', array( $this, 'get_setting_section' ), 10 );
		add_filter( 'wpcp_settings_fields', array( $this, 'get_setting_fields' ), 10 );
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules[ $this->campaign_type ] = get_called_class();

		return $modules;
	}

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
	 * @param int $campaign_id
	 *
	 * @return mixed
	 * @since 1.2.0
	 */
	abstract public function get_post( $campaign_id );

	/**
	 * @param        $campaign_id
	 * @param string $user
	 *
	 * @return int|WP_Error
	 * @since 1.2.0
	 */
	public function process_campaign( $campaign_id, $source = null, $user = 'cron' ) {
		$wp_post = get_post( $campaign_id );
		if ( ! $wp_post || 'wp_content_pilot' !== $wp_post->post_type ) {
			wpcp_logger()->error( 'Could not find any campaign with the provided id', $campaign_id );

			return new WP_Error( 'invalid-campaign-id', __( 'Could not find the campaign', 'wp-content-pilot' ) );
		}

		$campaign_type = get_post_meta( $campaign_id, '_campaign_type', true );
		if ( $campaign_type !== $this->campaign_type ) {
			wpcp_logger()->error( 'Campaign type mismatch', $campaign_id );

			return new WP_Error( 'invalid-campaign-id', __( 'Campaign type mismatch', 'wp-content-pilot' ) );
		}

		if ( $this->try_count > $this->max_try ) {
			$message = __( 'Tried maximum time but could not generate article check log', 'wp-content-pilot' );
			wpcp_logger()->error( $message, $campaign_id );

			return new WP_Error( 'time-out', __( 'Tried maximum time but could not generate article check log', 'wp-content-pilot' ) );
		}

		$this->campaign_id = absint( $campaign_id );
		$this->initiator   = sanitize_text_field( $user );

//		if ( empty( $keywords ) ) {
//			$keywords = $this->get_keywords( $this->campaign_id );
//			if ( empty( $keywords ) ) {
//				$message = __( 'Campaign do not have keywords to proceed, please set keyword', 'wp-content-pilot' );
//				wpcp_logger()->error( $message, $campaign_id );
//
//				return new WP_Error( 'missing-data', $message );
//			}
//		}
//
//		$keywords = wpcp_string_to_array( $keywords );
//		shuffle( $keywords );
//		if ( empty( $keywords ) ) {
//			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
//		}

		$article = $this->get_post( $campaign_id );
		if ( is_wp_error( $article ) ) {
			//wpcp_logger()->error( $article->get_error_message(), $campaign_id);
			return $article;
		}

		if ( ! $article ) {
			return new WP_Error( 'no-response', __( 'Content Pilot did not responded for the action', 'wp-content-pilot' ) );
		}

		$article = apply_filters( 'wpcp_article', wp_parse_args( $article, array(
			'title'      => '',
			'author'     => '',
			'image_url'  => '',
			'excerpt'    => '',
			'content'    => '',
			'source_url' => '',
		) ), $campaign_id, $campaign_type );

//		fix utf chars & emoji
		foreach ( $article as $tag => $tag_content ) {
			$article[ $tag ] = wpcp_fix_utf8( $tag_content );
			$article[ $tag ] = wpcp_remove_emoji( $tag_content );
		}

		$post_type     = wpcp_get_post_meta( $this->campaign_id, '_post_type', '' );
		$post_status   = wpcp_get_post_meta( $this->campaign_id, '_post_status', '' );
		$post_excerpt  = '';
		$post_author   = get_post_field( 'post_author', $campaign_id, 'edit' );
		$post_meta     = [];
		$post_taxonomy = [];
		$post_time     = current_time( 'mysql' );


		//check if acceptance passed if not then return this method again
		$accepted = apply_filters( 'wpcp_acceptance_check', true, $article, $campaign_id, $this );
		if ( ! $accepted ) {
			wpcp_logger()->error( __( 'Article failed in acceptance test', 'wp-content-pilot' ), $campaign_id );
			$this->try_count ++;

			return $this->process_campaign( $campaign_id, '', $user );
		}


		//truncate content
		$limit_title = wpcp_get_post_meta( $this->campaign_id, '_title_limit', 0 );
		if ( ! empty( $limit_title ) && $limit_title > 0 ) {
			wpcp_logger()->info( __( 'Limiting title', 'wp-content-pilot' ), $campaign_id );
			$article['title'] = wp_trim_words( $article['title'], $limit_title );
		}

		$limit_content = wpcp_get_post_meta( $this->campaign_id, '_content_limit', 0 );
		if ( ! empty( $limit_content ) && $limit_content > 0 ) {
			//previously use wp_trim_words but it remove all html tag from content
			//that's why use custom function wpcp_truncate_content from allow html in content
			wpcp_logger()->info( __( 'Limiting content', 'wp-content-pilot' ), $campaign_id );
			$article['content'] = wpcp_truncate_content( $article['content'], $limit_content );
		}

		//strip links
		$remove_hyper_links = wpcp_get_post_meta( $this->campaign_id, '_strip_links', 0 );
		if ( 'on' === $remove_hyper_links ) {
			wpcp_logger()->info( __( 'Stripping links', 'wp-content-pilot' ), $campaign_id );
			//keep text
			$article['content'] = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', html_entity_decode( $article['content'] ) );
			//remove text
			/*$content =  preg_replace( '#<a.*?>(.*?)</a>#i', '', $content );*/
		}


		//translate

		//make template of title,content,meta

		//translate template
		$post_content = wpcp_get_post_meta( $this->campaign_id, '_post_template', '' );
		$post_title   = wpcp_get_post_meta( $this->campaign_id, '_post_title', '' );
		$tags         = array_keys( $this->get_template_tags() );
		foreach ( $tags as $tag ) {
			if ( array_key_exists( $tag, $article ) ) {
				$post_content = str_replace( '{' . $tag . '}', $article[ $tag ], $post_content );
				$post_title   = str_replace( '{' . $tag . '}', $article[ $tag ], $post_title );
			}

			$post_content = html_entity_decode( $post_content );
			$post_title   = html_entity_decode( $post_title );
		}

		// replacing the keywords with affiliate links

		//replacing patterns

		//spin
		$spin_article = wpcp_get_post_meta( $campaign_id, '_spin_article', '' );
		if ( 'on' == $spin_article ) {
			wpcp_logger()->debug( 'Spinning article ...', $campaign_id );
			$separator    = str_repeat( '#', ceil( rand( 10, 20 ) ) );
			$spinable     = $post_title . $separator . $post_content;
			$spinned      = explode( $separator, wpcp_spin_article( $campaign_id, $spinable ), 2 );
			$post_title   = $spinned[0];
			$post_content = $spinned[1];
		}

		//category handles
		$categories = wpcp_get_post_meta( $this->campaign_id, '_categories', [] );
		if ( ! empty( $categories ) ) {
			$post_taxonomy['category'] = array_map( 'intval', $categories );
		}

		//tags handles
		$tags = wpcp_get_post_meta( $this->campaign_id, '_tags', [] );
		if ( ! empty( $tags ) ) {
			$post_taxonomy['post_tag'] = array_map( 'intval', $tags );
		}

		//Pending for transation fail

		//prepare author
		$custom_author = wpcp_get_post_meta( $campaign_id, '_author', 1 );
		if ( ! empty( $custom_author ) ) {
			$post_author = intval( $custom_author );
		}

		//delete first image from content

		//if image not found set pending

		//amazon woocommerce integration


		//remove images  from content
		$remove_images = wpcp_get_post_meta( $this->campaign_id, '_remove_images', 'off' );

		if ( 'on' == $remove_images ) {
			wpcp_logger()->info( __( 'Removing images from content', 'wp-content-pilot' ), $campaign_id );
			$post_content = preg_replace( '/<img[^>]+\>/mi', '', $post_content );
		}

		//summery
		$insert_excerpt = wpcp_get_post_meta( $campaign_id, '_excerpt', '' );
		if ( 'on' == $insert_excerpt ) {
			$excerpt_length = wpcp_get_post_meta( $campaign_id, '_excerpt_length', 55 );
			$post_excerpt   = empty( $post_excerpt ) ? $post_content : $post_excerpt;
			$post_excerpt   = strip_tags( $post_excerpt );
			$post_excerpt   = strip_shortcodes( $post_excerpt );
			$post_excerpt   = wp_trim_words( $post_excerpt, $excerpt_length );
		}

		//spin
//		$spin_article = wpcp_get_post_meta( $campaign_id, '_spin_article', '' );
//		if ( 'on' == $spin_article ) {
//			wpcp_logger()->info( __( 'Spinning article content...', 'wp-content-pilot' ), $campaign_id );
//			$post_content = wpcp_spin_article( $post_content );
//		}

		//post
		do_action( 'wpcp_before_post_insert', $campaign_id, $article );

		$title          = apply_filters( 'wpcp_post_title', $post_title, $campaign_id, $article );
		$post_content   = apply_filters( 'wpcp_post_content', $post_content, $campaign_id, $article );
		$post_excerpt   = apply_filters( 'wpcp_post_excerpt', $post_excerpt, $campaign_id, $article );
		$post_author    = apply_filters( 'wpcp_post_author', $post_author, $campaign_id, $article );
		$post_type      = apply_filters( 'wpcp_post_type', $post_type, $campaign_id, $article );
		$post_status    = apply_filters( 'wpcp_post_status', $post_status, $campaign_id, $article );
		$post_meta      = apply_filters( 'wpcp_post_meta', $post_meta, $campaign_id, $article );
		$post_tax       = apply_filters( 'wpcp_post_taxonomy', $post_taxonomy, $campaign_id, $article );
		$post_time      = apply_filters( 'wpcp_post_time', $post_time, $campaign_id, $article );
		$comment_status = apply_filters( 'wpcp_post_comment_status', get_default_comment_status( $post_type ), $campaign_id, $article );
		$ping_status    = apply_filters( 'wpcp_post_ping_status', get_default_comment_status( $post_type, 'pingback' ), $campaign_id, $article );

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
		], $campaign_id, $article );

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
			do_action( 'wpcp_post_insertion_failed', $campaign_id );

			return $post_id;
		}

		//set featured image
		$is_set_featured_image = wpcp_get_post_meta( $campaign_id, '_set_featured_image', 0 );
		if ( 'on' === $is_set_featured_image && ! empty( $article['image_url'] ) ) {
			wpcp_logger()->info( __( 'Setting featured image', 'wp-content-pilot' ), $campaign_id );
			if ( 'clickbank' == $campaign_type || 'instagram' == $campaign_type || 'soundcloud' == $campaign_type ) {
				$attachment_id = $article['attachment_id'];
			} else {
				$attachment_id = wpcp_download_image( html_entity_decode( $article['image_url'] ) );
			}
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}
		}
		$set_woocommerce_gallery = wpcp_get_post_meta( $campaign_id, '_add_images_gallery', '' );
		if ( 'on' === $set_woocommerce_gallery && ! empty( $article['gallery_images'] ) ) {
			do_action( 'wpcp_woo_product_gallery', $post_id, $article['gallery_images'] );
		}

		// set polylang plugin

		$enable_polylang        = !empty( wpcp_get_post_meta( $campaign_id, '_enable_polylang', 0 ) ) ? wpcp_get_post_meta( $campaign_id, '_enable_polylang', 0 ) : 0;
		$polylang_language_code = wpcp_get_post_meta( $campaign_id, '_polylang_language_code', '' );
		if ( 'on' === $enable_polylang && ! empty( $polylang_language_code ) ) {
			wpcp_logger()->info( __( "Settings up polylang language", 'wp-content-pilot' ), $campaign_id );
			pll_set_post_language( $post_id, $polylang_language_code );
		}

		// set fifu plugin
		if ( function_exists('fifu_update_fake_attach_id' ) ) {
			$enable_not_save_featured_image = ! empty( wpcp_get_post_meta( $campaign_id, '_not_save_featured_image', 0 ) ) ? wpcp_get_post_meta( $campaign_id, '_not_save_featured_image', 0 ) : 0;
			if ( ! empty( $enable_not_save_featured_image ) && 'on' === $enable_not_save_featured_image ) {
				wpcp_logger()->info( __( 'Setting featured image using external links', 'wp-content-pilot' ), $campaign_id );
				update_post_meta ( $post_id, 'fifu_image_url', $article['image_url'] );
				fifu_update_fake_attach_id ( $post_id );
			}
		}
		//wpml internal cron patch

		//wpml integration

		//setting categories for custom post types

		//save campaign data
		update_post_meta( $post_id, '_campaign_id', $campaign_id );
		update_post_meta( $campaign_id, '_last_post', $post_id );
		update_post_meta( $campaign_id, '_last_run', current_time( 'mysql' ) );
		update_post_meta( $campaign_id, 'wpcp_last_ran_campaign', current_time( 'mysql' ) );
		$posted = wpcp_get_post_meta( $campaign_id, '_post_count', 0 );
		update_post_meta( $campaign_id, '_post_count', ( $posted + 1 ) );
		do_action( 'wpcp_after_post_publish', $post_id, $campaign_id, $article );
		do_action( 'wpcp_' . $campaign_type . '_after_post_publish', $post_id, $campaign_id, $article );
		wpcp_logger()->info( 'hurray! successfully generated article', $campaign_id );

		return $post_id;
	}

	/**
	 * @return \Curl\Curl
	 * @since 1.2.0
	 */
	protected function setup_curl() {
		return wpcp_setup_request();
	}


	/**
	 * Deactivate key for hours
	 *
	 * @param     $campaign_id
	 * @param     $key
	 * @param int $hours
	 *
	 * @since 1.2.0
	 */
	protected function deactivate_key( $campaign_id, $key, $hours = 1 ) {
		wpcp_logger()->warning( sprintf( 'Deactivating key [%s] for [%d] hour', $key, $hours ), $campaign_id );
		$deactivated_until = current_time( 'timestamp' ) + ( $hours * HOUR_IN_SECONDS );
		update_post_meta( $campaign_id, '_' . md5( $key ), $deactivated_until );
	}

	/**
	 * Check if the source is deactivated
	 *
	 * @param $campaign_id
	 * @param $source
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	protected function is_deactivated_key( $campaign_id, $key ) {
		$deactivated_until = wpcp_get_post_meta( $campaign_id, '_' . md5( $key ), '' );
		if ( empty( $deactivated_until ) || $deactivated_until < current_time( 'timestamp' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get unique string for the campaign
	 *
	 * @param string $key
	 *
	 * @return string
	 * @since 1.2.0
	 */
	protected function get_unique_key( $key = 'page' ) {
		$key = '_wpcp_' . $key . '_' . md5( $key );

		return sanitize_title( $key );
	}

	/**
	 * @param $campaign_id
	 *
	 * @return array|string|null
	 * @since 1.2.0
	 */
	protected function get_last_keyword( $campaign_id ) {
		return wpcp_get_post_meta( $this->campaign_id, '_last_keyword', '' );
	}

	/**
	 * @param $campaign_id
	 * @param $keyword
	 *
	 * @since 1.2.0
	 */
	protected function set_last_keyword( $campaign_id, $keyword ) {
		wpcp_update_post_meta( $campaign_id, '_last_keyword', $keyword );
	}

	/**
	 * @param $campaign_id
	 * @param $keyword
	 * @param $default
	 *
	 * @since 1.2.0
	 */
	protected function get_page_number( $campaign_id, $keyword, $default ) {
		$key = '_wpcp_' . sanitize_key( $keyword ) . '_' . md5( $keyword );
		wpcp_get_post_meta( $campaign_id, $key, $default );
	}

	/**
	 * @param        $source
	 * @param        $campaign_id
	 * @param string $status
	 * @param int $count
	 *
	 * @return array|object|null
	 * @since 1.2.0
	 */
	protected function get_links( $for, $campaign_id = null, $status = 'new', $count = 5 ) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->wpcp_links WHERE `for`=%s AND camp_id=%d AND status=%s LIMIT %d", $for, $campaign_id, $status, $count ) );
		foreach ( $results as $result ) {
			$result->meta = maybe_unserialize( base64_decode( $result->meta ) );
		}

		return $results;
	}

	/**
	 * @param $data
	 *
	 * @return false|int
	 * @since 1.2.0
	 */
	protected function insert_link( $data ) {
		$data = wp_parse_args( $data, array(
			'camp_id' => '',
			'url'     => '',
			'title'   => '',
			'for'     => '',
			'meta'    => '',
			'status'  => 'new',
		) );
		global $wpdb;

		$data['meta'] = ! is_serialized( $data['meta'] ) && ! empty( $data['meta'] ) ? base64_encode( serialize( $data['meta'] ) ) : base64_encode( $data['meta'] );

		if ( false !== $wpdb->insert( $wpdb->wpcp_links, $data ) ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Insert links
	 *
	 * @param array $links
	 *
	 * @return int
	 *
	 * @since 1.0.0
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
	 * @param int $id
	 * @param array $data
	 *
	 * @return false|int
	 * @since 1.2.0
	 */
	protected function update_link( $id, $data = array() ) {
		global $wpdb;

		return $wpdb->update( $wpdb->wpcp_links, $data, [ 'id' => absint( $id ) ] );
	}

	/**
	 * Returns campaign meta mainly built for getting campaign keywords/links
	 *
	 * @param int $campaign_id
	 * @param string $key
	 * @param bool $shuffle
	 *
	 * @return string|array
	 * @since 1.2.0
	 * @since 1.2.4 $key added
	 * @since 1.2.4 $default added
	 * @since 1.2.4 $shuffle added
	 */
	protected function get_campaign_meta( $campaign_id, $key = '_keywords', $default = '', $shuffle = true ) {
		$meta = wpcp_get_post_meta( $campaign_id, $key, $default );
		if ( empty( $meta ) ) {
			return $meta;
		}
		if ( $shuffle ) {
			$metas = wpcp_string_to_array( $meta );
			shuffle( $metas );
		}

		return $metas;
	}
}
