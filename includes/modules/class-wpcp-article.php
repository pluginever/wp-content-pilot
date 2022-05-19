<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Article extends WPCP_Module {

	/**
	 * @var string
	 */
	protected $module = 'article';

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
		//option fields
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_field' );
		parent::__construct( $this->module );
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
		$template
			= <<<EOT
<img src="{image_url}">
{content}
<br> <a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {

		echo WPCP_HTML::start_double_columns();
		echo WPCP_HTML::select_input( array(
			'name'          => '_article_region',
			'label'         => __( 'Select region to search article', 'wp-content-pilot' ),
			'options'       => $this->get_article_region(),
			'default'       => 'global',
			'class'         => 'wpcp-select2',
			'wrapper_class' => 'pro',
			'attrs'         => array(
				'disabled' => 'disabled',
			)
		) );
//		echo WPCP_HTML::select_input( array(
//			'name'          => '_article_language',
//			'label'         => __( 'Select language to search article', 'wp-content-pilot' ),
//			'options'       => $this->get_article_language(),
//			'default'       => 'en',
//			'wrapper_class' => 'pro',
//			'attrs'         => array(
//				'disabled' => 'disabled',
//			)
//		) );
		echo WPCP_HTML::end_double_columns();

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
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_article',
			'title' => __( 'Article Settings', 'wp-content-pilot' )
		];

		return $sections;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_fields( $fields ) {
		$fields['wpcp_settings_article'] = [
			array(
				'name'        => 'banned_hosts',
				'label'       => __( 'Banned hosts', 'wp-content-pilot' ),
				'desc'        => __( 'Articles from the above hosts will be rejected. Put single url/host per line.', 'wp-content-pilot' ),
				'placeholder' => __( "example.com \n example1.com", 'wp-content-pilot' ),
				'type'        => 'textarea',
			),
		];

		return $fields;
	}


	/**
	 * @param int $campaign_id
	 *
	 * @return array|mixed|WP_Error
	 * @throws ErrorException
	 * @since 1.2.0
	 */
	public function get_post( $campaign_id ) {
		//before it was getting keywords but now we are changing to source instead of keywords
		//it can be anything
		$keywords = $this->get_campaign_meta( $campaign_id );
		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		wpcp_logger()->info( __( 'Loaded Article campaign', 'wp-content-pilot' ), $campaign_id );

		//loop through keywords
		foreach ( $keywords as $keyword ) {
			wpcp_logger()->info( sprintf( __( 'Looking for article for the keyword [ %s ]', 'wp-content-pilot' ), $keyword ), $campaign_id );

			if ( $this->is_deactivated_key( $campaign_id, $keyword ) ) {
//				$reactivate_keyword_action = add_query_arg( [
//					'campaign_id' => $campaign_id,
//					'keyword'     => $keyword,
//					'action'      => 'wpcp_reactivate_keyword'
//				], admin_url( 'admin-post.php' ) );
//				wpcp_logger()->info( sprintf( __( 'The keyword is deactivated for 1 hr because last time could not find any article with keyword [%s] %s reactivate keyword %s', 'wp-content-pilot' ), $keyword, '<a href="' . $reactivate_keyword_action . '">', '</a>' ), $campaign_id );
				wpcp_logger()->info( __( 'The keyword is deactivated for 1 hr because last time could not find any article with keyword [%s]', 'wp-content-pilot' ), $campaign_id );
				continue;
			}

			//get links from database
			wpcp_logger()->info( __( 'Checking for cached links in store', 'wp-content-pilot' ), $campaign_id );
			$links = $this->get_links( $keyword, $campaign_id );
			if ( empty( $links ) ) {
				wpcp_logger()->info( __( 'No cached links in store. Generating new links...', 'wp-content-pilot' ), $campaign_id );
				$this->discover_links( $campaign_id, $keyword );
				$links = $this->get_links( $keyword, $campaign_id );
			}

			wpcp_logger()->info( __( 'Looping through cached links for publishing article', 'wp-content-pilot' ), $campaign_id );
			foreach ( $links as $link ) {
				wpcp_logger()->info( sprintf( __( 'Generating article from [%s]', 'wp-content-pilot' ), $link->url ), $campaign_id );

				$this->update_link( $link->id, [ 'status' => 'failed' ] );

				$curl = $this->setup_curl();
				$curl->get( $link->url );

				if ( $curl->isError() && $this->initiator != 'cron' ) {
					wpcp_logger()->error( sprintf( __( "Failed processing link reason [%s]", 'wp-content-pilot' ), $curl->getErrorMessage() ), $campaign_id );
					continue;
				}

				wpcp_logger()->info( __( "Extracting post content from request", 'wp-content-pilot' ), $campaign_id );

				$html        = $curl->response;
				$readability = new WPCP_Readability();
				$readable    = $readability->parse( $html, $link->url );
				if ( is_wp_error( $readable ) ) {
					wpcp_logger()->error( sprintf( __( "Failed readability reason [%s] changing to different link", 'wp-content-pilot' ), $readable->get_error_message() ), $campaign_id );
					continue;
				}

				//check if the clean title metabox is checked and perform title cleaning
				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' == $check_clean_title ) {
					wpcp_logger()->info( __( 'Cleaning title', 'wp-content-pilot' ), $campaign_id );
					$title = wpcp_clean_title( $readability->get_title() );
				} else {
					$title = html_entity_decode( $readability->get_title(), ENT_QUOTES );
				}

				wpcp_logger()->info( __( 'Making article content from response', 'wp-content-pilot' ), $campaign_id );
				$article = array(
					'title'      => $title,
					'author'     => $readability->get_author(),
					'image_url'  => $readability->get_image(),
					'excerpt'    => $readability->get_excerpt(),
					'language'   => $readability->get_language(),
					'content'    => $readability->get_content(),
					'source_url' => $link->url,
				);

				wpcp_logger()->info( __( 'Article processed from campaign', 'wp-content-pilot' ), $campaign_id );
				$this->update_link( $link->id, [ 'status' => 'success', 'meta' => '' ] );

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
	}


	/**
	 * @param $campaign_id
	 * @param $keyword
	 *
	 * @return bool|mixed|WP_Error
	 * @throws ErrorException
	 * @since 1.2.0
	 */
	protected function discover_links( $campaign_id, $keyword ) {
		$page_key    = $this->get_unique_key( $keyword );
		$page_number = wpcp_get_post_meta( $campaign_id, $page_key, 0 );

		$args = apply_filters( 'wpcp_article_search_args', array(
			'q'     => urlencode( $keyword ),
			'count' => 10,
			'loc'   => 'en',
			//'format' => 'rss',
			'first' => ( $page_number * 10 ),
		), $campaign_id );


		$endpoint = add_query_arg( array(
			$args,
		), 'https://www.bing.com/search' );

		//wpcp_logger()->debug( sprintf( 'Searching page url [%s]', $endpoint ), $campaign_id );
		wpcp_logger()->info( sprintf( __( 'Searching page url [%s]', 'wp-content-pilot' ), $endpoint ), $campaign_id );

		$curl     = $this->setup_curl();
		$response = $curl->get( $endpoint );
		if ( $curl->isError() ) {
			wpcp_logger()->error( $curl->errorMessage, $campaign_id );
			$this->deactivate_key( $campaign_id, $keyword );

			return $response;
		}

//		if ( ! $response instanceof \SimpleXMLElement ) {
//			$response = simplexml_load_string( $response );
//		}

		wpcp_logger()->info( __( 'Extracting response from request', 'wp-content-pilot' ), $campaign_id );
		$dom = wpcp_str_get_html( $response );
		$matches = array();
		for ( $i = 0; $i < 10; $i ++ ) {
			if ( $dom->getElementsByTagName( '<h2>', $i ) ) {
				$matches[] = $dom->getElementsByTagName( '<h2>', $i )->innerText();
			}
		}

		// preg_match_all( '/<h2><a href="([^"]+)"\s*h="ID=SERP,[0-9]{4}\.1"/', $response, $matches );
		// preg_match_all( '/<h2><a href="([^"]+)"\s*h="ID=SERP,[0-9]{4}\.1">([^"]+)</', $dom, $matches );

		// $response = json_encode( $response );
		// $response = json_decode( $response, true );

		//check if links exist
		if ( empty( $response ) || ! isset( $matches ) || ! isset( $matches ) || empty( $matches ) ) {
			$message = __( 'Could not find any links from search engine, deactivating keyword for an hour.', 'wp-content-pilot' );
			wpcp_logger()->error( $message, $campaign_id );
			$this->deactivate_key( $campaign_id, $keyword );

			return new WP_Error( 'no-links-found', $message );
		}

		$items = $matches;

		wpcp_logger()->info( __( 'Getting banned hosts for skipping links', 'wp-content-pilot' ), $campaign_id );
		$banned_hosts = wpcp_get_settings( 'banned_hosts', 'wpcp_settings_article' );
		$banned_hosts = preg_split( '/\n/', $banned_hosts );
		$banned_hosts = array_merge( $banned_hosts, array(
			'youtube.com',
			'wikipedia',
			'dictionary',
			'youtube',
			'wikihow'
		) );

		$links = [];

		wpcp_logger()->info( __( 'Finding links from response and inserting into database', 'wp-content-pilot' ), $campaign_id );
		foreach ( $items as $item ) {
			preg_match( '/href="([^"]*)"/i', $item, $link );
			preg_match( '#<a[^>]*>([^<]*)<\/a>#i', $item, $title );
			$link  = $link[1];
			$title = ( isset( $title[1] ) && ! empty( $title[1] ) ) ? $title[1] : '';
			foreach ( $banned_hosts as $banned_host ) {
				if ( stristr( $link, $banned_host ) ) {
					continue;
				}
			}

			if ( stristr( $link, 'wikipedia' ) ) {
				continue;
			}

			if ( wpcp_is_duplicate_url( $link ) ) {
				continue;
			}

			$skip = apply_filters( 'wpcp_skip_duplicate_title', false, $title, $campaign_id );
			if ( $skip ) {
				continue;
			}

			$links[] = [
				'url'     => $link,
				'title'   => $title,
				'for'     => $keyword,
				'camp_id' => $campaign_id
			];
		}

		$total_inserted = $this->inset_links( $links );
		wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
		wpcp_logger()->info( sprintf( 'Total found links [%d] and accepted [%d] and rejected [%d]', count( $links ), $total_inserted, ( count( $links ) - $total_inserted ) ), $campaign_id );

		return true;
	}

	/**
	 * Get all supported regions for searching article
	 *
	 * @return array
	 * @since 1.1.1
	 *
	 */

	public function get_article_region() {
		$regions = array(
			'global' => 'Global Search',
			'es-AR'  => 'Spanish Argentina',
			'en-AU'  => 'English Australia',
			'de-AT'  => 'German Austria',
			'nl-BE'  => 'Dutch Belgium',
			'fr-BE'  => 'French Belgium',
			'pt-BR'  => 'Portuguese Brazil',
			'en-CA'  => 'English Canada',
			'fr-CA'  => 'French Canada',
			'es-CL'  => 'Spanish Chile',
			'da-DK'  => 'Danish Denmark',
			'fi-FI'  => 'Finnish Finland',
			'fr-FR'  => 'French France',
			'de-DE'  => 'German Germany',
			'zh-HK'  => 'Chinese Hong Kong',
			'en-IN'  => 'English India',
			'en-ID'  => 'English Indonesia',
			'it-IT'  => 'Italian Italy',
			'ja-JP'  => 'Japanese Japan',
			'ko-KR'  => 'Korean Korea',
			'en-MY'  => 'English Malaysia',
			'es-MX'  => 'Spanish Mexico',
			'nl-NL'  => 'Dutch Netherlands',
			'en-NZ'  => 'English New Zealand',
			'no-NO'  => 'Norwegian Norway',
			'zh-CN'  => 'Chinese China',
			'pl-PL'  => 'Polish Poland',
			'en-PH'  => 'English Philippines',
			'ru-RU'  => 'Russian Russia',
			'en-ZA'  => 'English South Africa',
			'es-ES'  => 'Spanish Spain',
			'sv-SE'  => 'Swedish Sweden',
			'fr-CH'  => 'French Switzerland',
			'de-CH'  => 'German Switzerland',
			'zh-TW'  => 'Chinese Taiwan',
			'tr-TR'  => 'Turkish Turkey',
			'en-GB'  => 'English United Kingdom',
			'en-US'  => 'English United States',
			'es-US'  => 'Spanish United States',


		);

		return $regions;
	}

	/**
	 * Get all supported languages for searching article
	 *
	 * @return array
	 * @since 1.1.1
	 *
	 */
	public function get_article_language() {

		$languages = array(
			'ar'      => 'Arabic',
			'eu'      => 'Basque',
			'bn'      => "Bengali",
			'bg'      => 'Bulgarian',
			'ca'      => 'Catalan',
			'zh-hans' => 'Simplified Chinese',
			'zh-hant' => 'Traditional Chinese',
			'hr'      => 'Croatian',
			'cs'      => 'Czech',
			'da'      => 'Danish',
			'nl'      => 'Dutch',
			'en'      => 'English',
			'en-gb'   => 'English - United Kingdom',
			'et'      => 'Estonian',
			'fi'      => 'Finish',
			'fr'      => 'French',
			'gl'      => 'Galician',
			'de'      => 'German',
			'gu'      => 'Gujrati',
			'he'      => 'Hebrew',
			'hi'      => 'Hindi',
			'hu'      => 'Hungarian',
			'is'      => 'Icelandic',
			'it'      => 'Italian',
			'jp'      => 'Japanese',
			'kn'      => 'Kannada',
			'ko'      => 'Korean',
			'lv'      => 'Latvian',
			'lt'      => 'Lithunian',
			'ms'      => 'Malay',
			'ml'      => 'Malayalam',
			'mr'      => 'Marathi',
			'nb'      => 'Norwegian',
			'pl'      => 'Polish',
			'pt-br'   => 'Portugese Brazil',
			'pt-pt'   => 'Portugese Portugal',
			'pa'      => 'Punjabi',
			'ro'      => 'Romanian',
			'ru'      => 'Russian',
			'sr'      => 'Serbian',
			'sk'      => 'Slovak',
			'sl'      => 'Slovenian',
			'es'      => 'Spanish',
			'sv'      => 'Swedish',
			'ta'      => 'Tamil',
			'te'      => 'Telegu',
			'th'      => 'Thai',
			'tr'      => 'Turkish',
			'uk'      => 'Ukrainian',
			'vi'      => 'Vietnamese',
		);

		return $languages;
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
