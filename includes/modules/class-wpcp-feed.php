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
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );

		add_action( 'wpcp_feed_campaign_options_meta_fields', array( $this, 'add_campaign_fields' ) );

		add_action( 'wpcp_feed_update_campaign_settings', array( $this, 'save_campaign_meta' ), 10, 2 );

		add_action( 'wp_feed_options', array( $this, 'set_feed_options' ) );
		add_action( 'http_response', array( $this, 'trim_feed_content' ) );
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_campaign_type() {
		return 'feed';
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules['feed'] = __CLASS__;

		return $modules;
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
	public function add_campaign_fields( $post ) {

		echo WPCP_HTML::textarea_input( array(
			'name'        => '_feed_links',
			'label'       => __( 'Feed Links', 'wp-content-pilot' ),
			'placeholder' => __( 'Example: http://example.com/feed', 'wp-content-pilot' ),
			'desc'        => __( 'Input feed links, Separate links with a comma (,)', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::checkbox_input( array(
			'name'        => '_fetch_full_content',
			'label'       => __( 'Feed fetch full content', 'wp-content-pilot' ),
			'wrapper_class' => 'pro',
			'attrs'=> array(
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
	public function get_setting_section( $section ) {
		return $section;
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
	 * @since 1.2.0
	 */
	public function save_settings() {

	}

	/**
	 * @return array|WP_Error
	 * @since 1.2.0
	 */
	public function get_post( $feed_urls = null ) {
		//if empty get from meta
		if ( empty( $feed_urls ) ) {
			$feed_urls = get_post_meta( $this->campaign_id, '_keywords', true );
			if ( empty( $feed_urls ) ) {
				$message = __( 'Campaign do not have feel link to proceed, please set feed link', 'wp-content-pilot' );
				wpcp_logger()->error( $message );

				return new WP_Error( 'missing-data', $message );
			}
		}

		$feed_urls = wpcp_string_to_array( $feed_urls );
		if ( empty( $feed_urls ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have feel link to proceed, please set feed link', 'wp-content-pilot' ) );
		}

		$last_keyword = wpcp_get_post_meta( $this->campaign_id, '_last_keyword', '' );


		foreach ( $feed_urls as $feed_url ) {
			wpcp_logger()->debug( sprintf( 'Looping through feed link [ %s ]', $feed_url ) );
			//if more than 1 then unset last one
			if ( count( $feed_urls ) > 1 && $last_keyword == $feed_url ) {
				wpcp_logger()->debug( sprintf( 'feed links more than 1 and [ %s ] this link used last time so skipping it ', $feed_url ) );
				continue;
			}

			if ( $this->is_deactivated_key( $this->campaign_id, $feed_url ) ) {
				wpcp_logger()->debug( sprintf( 'The feed url is deactivated for 1 hr because last time could not find any article with url [%s]', $feed_url ) );
			}

			//get links from database
			$links = $this->get_links( $feed_url );
			if ( empty( $links ) ) {
				wpcp_logger()->debug( 'No generated links now need to generate new links' );
				$discovered_link = $this->discover_links( $feed_url );
				$links           = $this->get_links( $feed_url );
			}

			if ( empty( $links ) ) {
				$message = __( 'No links to process the campaign, will run again after 1 hour' );
				wpcp_logger()->error( $message );
				$this->deactivate_key( $this->campaign_id, $feed_url );
				return new WP_Error( 'no-links', $message );
			}

			wpcp_logger()->debug( 'Starting to process youtube article' );

			foreach ( $links as $key => $link ) {
				wpcp_logger()->info( sprintf( 'Running campaign from generated %d time link [%s]', $key + 1, $link->url ) );
				$this->update_link( $link->id, [ 'status' => 'failed' ] );
				$article = [];
				$curl = $this->setup_curl();
				$curl->get( $link->url );

				if ( $curl->isError() && $this->initiator != 'cron') {
					wpcp_logger()->info( sprintf( "Failed processing link reason [%s]", $curl->getErrorMessage() ) );
					continue;
				}

				$html        = $curl->response;
				$readability = new WPCP_Readability();
				$readable    = $readability->parse( $html, $link->url );
				if ( is_wp_error( $readable ) ) {
					wpcp_logger()->info( sprintf( "Failed readability reason [%s]", $readable->get_error_message() ) );
					continue;
				}

				$article = apply_filters('wpcp_feed_article', array(
					'title'      => $readability->get_title(),
					'author'     => $readability->get_author(),
					'image_url'  => $readability->get_image(),
					'excerpt'    => $readability->get_excerpt(),
					'language'   => $readability->get_language(),
					'content'    => $readability->get_excerpt(),
					'source_url' => $link->url,
				), $readability, $this->campaign_id );

				wpcp_logger()->debug( 'successfully generated article' );
				wpcp_update_post_meta( $this->campaign_id, '_last_keyword', $feed_url );
				$this->update_link( $link->id, [ 'status' => 'success' ] );
				return $article;

			}
		}

		return new WP_Error( 'campaign-error', __( 'Could not generate any article, try later', 'wp-content-pilot' ) );
	}


	public function discover_links( $feed_link ) {
		include_once( ABSPATH . WPINC . '/feed.php' );
		$rss = fetch_feed( $feed_link );

		if ( is_wp_error( $rss ) ) {
			wpcp_logger()->info( sprintf( 'Failed fetching feeds [%s]', $rss->get_error_message() ) );

			return $rss;
		}

		$max_items = $rss->get_item_quantity();
		$rss_items = $rss->get_items( 0, $max_items );
		if ( ! isset( $max_items ) || $max_items == 0 ) {
			wpcp_logger()->info( 'Could not find any article, waiting...' );

			return new WP_Error( 'feed-error', __( 'Could not find any article, waiting...', 'wp-content-pilot' ) );
		}

		$inserted = 0;
		foreach ( $rss_items as $rss_item ) {
			$url = esc_url( $rss_item->get_permalink() );
			if ( stristr( $url, 'news.google' ) ) {
				$urlParts   = explode( 'url=', $url );
				$correctUrl = $urlParts[1];
				$url        = $correctUrl;
			}

			//Google alerts links correction
			if ( stristr( $url, 'alerts/feeds' ) && stristr( $url, 'google' ) ) {
				preg_match( '{url\=(.*?)[&]}', $url, $urlMatches );
				$correctUrl = $urlMatches[1];

				if ( trim( $correctUrl ) != '' ) {
					$url = $correctUrl;
				}
			}

			$title = $rss_item->get_title();


			if ( wpcp_is_duplicate_url( $url ) ) {
				continue;
			}

			$this->insert_link( array(
				'url'          => esc_url( $url ),
				'title'        => $title,
				'keyword'      => $feed_link,
				'pub_date_gmt' => '',
			) );

			$inserted ++;
		}

		if ( $inserted < 1 ) {
			wpcp_logger()->info( 'Could not find any links' );

			return false;
		}

		wpcp_logger()->debug( sprintf( 'Total found links [%d] and accepted [%d]', count( $rss_items ), $inserted ) );

		return $inserted;
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
