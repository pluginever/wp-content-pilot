<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Article extends WPCP_Module {

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

		//option fields
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_field' );
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_campaign_type() {
		return 'article';
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules['article'] = __CLASS__;

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
	 * @since 1.2.0
	 */
	public function get_default_template() {
		$template =
			<<<EOT
<img src="{image_url}">
{content}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_fields( $post ) {

	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {

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
	 * @return mixed|void
	 */
	public function get_post( $keywords = null ) {
		//if empty get from meta
		if ( empty( $keywords ) ) {
			$keywords = get_post_meta( $this->campaign_id, '_keywords', true );
			if ( empty( $keywords ) ) {
				$message = __( 'Campaign do not have keywords to proceed, please set keyword', 'wp-content-pilot' );
				wpcp_logger()->error( $message );

				return new WP_Error( 'missing-data', $message );
			}
		}

		$keywords = wpcp_string_to_array( $keywords );
		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		$last_keyword = wpcp_get_post_meta( $this->campaign_id, '_last_keyword', '' );

		foreach ( $keywords as $keyword ) {
			wpcp_logger()->debug( sprintf( 'Looping through keywords [ %s ]', $keyword ) );

			if ( $this->is_deactivated_key( $this->campaign_id, $keyword ) ) {
				wpcp_logger()->debug( sprintf( 'The keyword is deactivated for 1 hr because last time could not find any article with keyword [%s]', $keyword ) );
			}

			//if more than 1 then unset last one
			if ( count( $keywords ) > 1 && $last_keyword == $keyword ) {
				wpcp_logger()->debug( sprintf( 'The keyword [%s] already used last time rotating it to different one', $keyword ) );
				continue;
			}

			//get links from database
			$links = $this->get_links( $keyword );
			if ( empty( $links ) ) {
				wpcp_logger()->debug( 'No generated links now need to generate new links' );
				$discovered_link = $this->discover_links( $this->campaign_id, $keyword );
				$links           = $this->get_links( $keyword );
			}

			if ( empty( $links ) ) {
				$message = __( 'No links to process the campaign, waiting to run later...' );
				wpcp_logger()->error( $message );
				$this->deactivate_key( $this->campaign_id, $keyword );

				return new WP_Error( 'no-links', $message );
			}

			wpcp_logger()->debug( 'Starting to process campaign article' );

			foreach ( $links as $link ) {
				wpcp_logger()->debug( sprintf( 'Grabbing article from #[%s]', $link->url ) );
				$this->update_link( $link->id, [ 'status' => 'failed' ] );
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

				$article = array(
					'title'      => $readability->get_title(),
					'author'     => $readability->get_author(),
					'image_url'  => $readability->get_image(),
					'excerpt'    => $readability->get_excerpt(),
					'language'   => $readability->get_language(),
					'content'    => $readability->get_content(),
					'source_url' => $link->url,
				);

				wpcp_logger()->debug( 'successfully generated article' );
				wpcp_update_post_meta( $this->campaign_id, '_last_keyword', $keyword );

				return $article;
			}

		}

		return new WP_Error( 'campaign-error', __( 'Could not generate any article', 'wp-content-pilot' ) );
	}


	/**
	 * @since 1.2.0
	 * @param $campaign_id
	 * @param $keyword
	 *
	 * @return bool|mixed|WP_Error
	 */
	protected function discover_links( $campaign_id, $keyword ) {
		$page_key    = sanitize_key( '_page_' . $campaign_id . '_' . md5( $keyword ) );
		$page_number = wpcp_get_post_meta( $campaign_id, $page_key, 0 );


		$endpoint = add_query_arg( array(
			'q'      => urlencode( $keyword ),
			'count'  => 10,
			'loc'    => 'en',
			'format' => 'rss',
			'first'  => ( $page_number * 10 ),
		), 'https://www.bing.com/search' );

		wpcp_logger()->info( sprintf( 'Searching page url [%s]', $endpoint ) );

		$curl = $this->setup_curl();

		$response = $curl->get( $endpoint );
		if ( $curl->isError() ) {
			wpcp_logger()->error( $curl->errorMessage );
			return $response;
		}

		if ( ! $response instanceof \SimpleXMLElement ) {
			$response = simplexml_load_string( $response );
		}

		$response = json_encode( $response );
		$response = json_decode( $response, true );

		//check if links exist
		if ( empty( $response ) || ! isset( $response['channel'] ) || ! isset( $response['channel']['item'] ) || empty( $response['channel']['item'] ) ) {
			$message = __( 'Could not find any links from search engine,waiting...', 'wp-content-pilot' );
			wpcp_logger()->info( $message );

			return new WP_Error( 'no-links-found', $message );
		}

		$inserted = 0;
		$items =  $response['channel']['item'];
		$banned_hosts = wpcp_get_settings( 'banned_hosts', 'wpcp_settings_article' );
		$banned_hosts = preg_split( '/\n/', $banned_hosts );
		$banned_hosts = array_merge( $banned_hosts, array('youtube.com', 'wikipedia', 'dictionary', 'youtube'));
		foreach ( $items as $item ) {

			foreach ( $banned_hosts as $banned_host ) {
				if ( stristr( $item['link'], $banned_host ) ) {
					continue;
				}
			}

			if ( stristr( $item['link'], 'wikipedia' ) ) {
				continue;
			}

			if ( wpcp_is_duplicate_title( $item['title'] ) ) {
				continue;
			}

			if ( wpcp_is_duplicate_url( $item['link'] ) ) {
				continue;
			}

			$this->insert_link( array(
				'url'          => esc_url( $item['link'] ),
				'title'        => $item['title'],
				'keyword'      => $keyword,
				'pub_date_gmt' => empty( $item['pubDate'] ) ? '' : date( 'Y-m-d H:i:s', strtotime( $item['pubDate'] ) ),
			) );

			$inserted ++;
		}

		if ( $inserted < 1 ) {
			wpcp_logger()->info( 'Could not find any links' );

			return false;
		}

		wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1);
		wpcp_logger()->debug( sprintf( 'Total found links [%d] and accepted [%d]', count( $items ), $inserted ) );

		return true;
	}


	/**
	 * Main WPCP_Article Instance.
	 *
	 * Ensures only one instance of WPCP_Article is loaded or can be loaded.
	 *
	 * @return WPCP_Article Main instance
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

WPCP_Article::instance();
