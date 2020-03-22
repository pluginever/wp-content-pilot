<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Feed extends WPCP_Module {

	/**
	 * The single instance of the class
	 *
	 * @var $this ;
	 */
	protected static $_instance = null;

	/**
	 * WPCP_Module constructor.
	 */
	public function __construct() {
		parent::__construct( 'feed' );
		add_action( 'wp_feed_options', array( $this, 'set_feed_options' ) );
		add_action( 'http_response', array( $this, 'trim_feed_content' ) );
	}

	/**
	 * @return string
	 */
	public function get_module_icon() {
		return '';
	}

	/**
	 * @return array
	 * @since 1.2.0
	 */
	public function get_template_tags() {
		return array(
			'title'      => __( 'Title', 'wp-content-pilot' ),
			'excerpt'    => __( 'Summary', 'wp-content-pilot' ),
			'content'    => __( 'Content', 'wp-content-pilot' ),
			'image_url'  => __( 'Main image url', 'wp-content-pilot' ),
			'source_url' => __( 'Source link', 'wp-content-pilot' ),
		);
	}

	/**
	 * @return string
	 */
	public function get_default_template() {
		$template =
			<<<EOT
<img src="{image_url}" alt="">
{content}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {

		echo WPCP_HTML::textarea_input( array(
			'name'        => '_feed_links',
			'label'       => __( 'Feed Links', 'wp-content-pilot' ),
			'placeholder' => __( 'Example: http://example.com/feed', 'wp-content-pilot' ),
			'desc'        => __( 'Input feed links, Separate links with a comma (,)', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::checkbox_input( array(
			'name'          => '_fetch_full_content',
			'label'         => __( 'Feed fetch full content', 'wp-content-pilot' ),
			'wrapper_class' => 'pro',
			'attrs'         => array(
				'disabled' => 'disabled'
			)
		) );
	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		$raw_links = empty( $posted['_feed_links'] ) ? '' : esc_html( $posted['_feed_links'] );
		$links     = wpcp_string_to_array( $raw_links, ',', array( 'trim', 'esc_url' ) );
		$str_links = implode( ',', $links );

		$force_feed         = empty( $posted['_force_feed'] ) ? '' : sanitize_key( $posted['_force_feed'] );
		$fetch_full_content = empty( $posted['_fetch_full_content'] ) ? '' : sanitize_key( $posted['_fetch_full_content'] );

		update_post_meta( $campaign_id, '_feed_links', $str_links );
		update_post_meta( $campaign_id, '_force_feed', $force_feed );
		update_post_meta( $campaign_id, '_fetch_full_content', $fetch_full_content );
	}

	/**
	 * @param $section
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		return $sections;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_fields( $fields ) {
		return $fields;
	}

	/**
	 * @return array|WP_Error
	 * @since 1.2.0
	 */
	public function get_post( $campaign_id, $feed_urls ) {

		wpcp_logger()->info( 'Feed Campaign Started', $campaign_id );

		foreach ( $feed_urls as $feed_url ) {
			wpcp_logger()->info( sprintf( 'Looping through feed link now trying with [ %s ]', $feed_url ), $campaign_id );

			if ( $this->is_deactivated_key( $campaign_id, $feed_url ) ) {
				$message = sprintf( 'The feed url is deactivated for 1 hr because last time could not find any article with url [%s]', $feed_url );
				wpcp_logger()->info( $message, $campaign_id );
				continue;
			}

			//get links from database
			$links = $this->get_links( $feed_url, $campaign_id );
			if ( empty( $links ) ) {
				wpcp_logger()->info( 'No generated links now need to generate new links', $campaign_id );
				$this->discover_links( $feed_url, $campaign_id );
				$links = $this->get_links( $feed_url, $campaign_id );
			}


			foreach ( $links as $link ) {
				wpcp_logger()->info( sprintf( 'Grabbing feed from [%s]', $link->url ), $campaign_id );

				$this->update_link( $link->id, [ 'status' => 'failed' ] );

				$curl = $this->setup_curl();
				$curl->setHeader( 'accept-encoding', 'utf-8' );
				$curl->setOpt( CURLOPT_ENCODING, '' );
				$curl->get( $link->url );


				if ( $curl->isError() && $this->initiator != 'cron' ) {
					wpcp_logger()->info( sprintf( "Failed processing link reason [%s]", $curl->getErrorMessage() ), $campaign_id );
					continue;
				}

				$html        = $curl->response;
				$readability = new WPCP_Readability();
				$readable    = $readability->parse( $html, $link->url );
				if ( is_wp_error( $readable ) ) {
					wpcp_logger()->info( sprintf( "Failed readability reason [%s]", $readable->get_error_message() ), $campaign_id );
					continue;
				}

				//check if the clean title metabox is checked and perform title cleaning
				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' == $check_clean_title ) {
					$title = wpcp_clean_title( $readability->get_title() );
				} else {
					$title = html_entity_decode( $readability->get_title(), ENT_QUOTES );
				}

				$article = apply_filters( 'wpcp_feed_article', array(
					'title'      => $title,
					'author'     => $readability->get_author(),
					'image_url'  => $readability->get_image(),
					'excerpt'    => $readability->get_excerpt(),
					'language'   => $readability->get_language(),
					'content'    => $readability->get_excerpt(),
					'source_url' => $link->url,
				), $readability, $campaign_id );

				wpcp_logger()->info( 'Article processed from campaign', $campaign_id );
				$this->update_link( $link->id, [ 'status' => 'success' ] );

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No feed article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );

	}


	public function discover_links( $feed_link, $campaign_id ) {
		include_once( ABSPATH . WPINC . '/feed.php' );
		$rss = fetch_feed( $feed_link );

		if ( is_wp_error( $rss ) ) {
			wpcp_logger()->warning( sprintf( 'Failed fetching feeds [%s]', $rss->get_error_message() ), $campaign_id );

			return $rss;
		}

		$max_items = $rss->get_item_quantity();
		$rss_items = $rss->get_items( 0, $max_items );
		if ( ! isset( $max_items ) || $max_items == 0 ) {
			wpcp_logger()->info( 'Could not find any article, waiting...', $campaign_id );

			return new WP_Error( 'feed-error', __( 'Could not find any article, waiting...', 'wp-content-pilot' ) );
		}

		$links = [];
		foreach ( $rss_items as $rss_item ) {
			$url = esc_url( $rss_item->get_permalink() );
			if ( stristr( $feed_link, 'news.google' ) ) {
				$urlParts   = explode( 'url=', $url );
				$correctUrl = $urlParts[1];
				$url        = $correctUrl;
			}

			//Google alerts links correction
			if ( stristr( $feed_link, 'alerts/feeds' ) && stristr( $feed_link, 'google' ) ) {
				preg_match( '{url\=(.*?)[&]}', $url, $urlMatches );
				$correctUrl = $urlMatches[1];

				if ( trim( $correctUrl ) != '' ) {
					$url = $correctUrl;
				}
			}

			$title = $rss_item->get_title();

			//check global settings for skip url with duplicate title or url
			$skip_global = wpcp_get_settings( 'skip_duplicate_url', 'wpcp_settings_misc', '' );

			if ( 'on' == $skip_global ) {
				if ( wpcp_is_duplicate_title( $title ) ) {
					continue;
				}

				if ( wpcp_is_duplicate_url( $url ) ) {
					continue;
				}
			}

			//check duplicate title and don't publish the post with duplicate title
			$skip_duplicate_title = wpcp_get_post_meta( $campaign_id, '_skip_duplicate_title', 'off' );

			if ( 'on' == $skip_duplicate_title ) {
				if ( wpcp_is_duplicate_title( $title ) ) {
					continue;
				}

				if ( wpcp_is_duplicate_url( $url ) ) {
					continue;
				}
			}


			$links[] = [
				'url'     => esc_url( $url ),
				'title'   => $title,
				'keyword' => $feed_link,
				'camp_id' => $campaign_id,
			];
		}

		$total_inserted = $this->inset_links( $links );
		wpcp_logger()->info( sprintf( 'Total found links [%d] and accepted [%d]', count( $links ), $total_inserted ), $campaign_id );

		return true;
	}

	/**
	 * Set user agent to fix curl transfer
	 * closed without complete data
	 *
	 * @param $args
	 *
	 * @since 1.0.0
	 *
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
	 * @param $campaign_id
	 *
	 * @return array|string|null
	 * @since 1.2.0
	 */
	public function get_keywords( $campaign_id ) {
		return wpcp_get_post_meta( $campaign_id, '_feed_links', '' );
	}

	/**
	 * Main WPCP_Feed Instance.
	 *
	 * Ensures only one instance of WPCP_Feed is loaded or can be loaded.
	 *
	 * @return WPCP_Feed Main instance
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

WPCP_Feed::instance();
