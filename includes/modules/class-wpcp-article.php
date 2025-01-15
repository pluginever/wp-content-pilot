<?php
/**
 * WPCP Article Class.
 *
 * @package     WP Content Pilot
 * @subpackage  Module
 *
 * @since       1.2.0
 */

// Exit if access directly.
defined( 'ABSPATH' ) || exit();

/**
 * WPCP_Article Class.
 *
 * @package     WP Content Pilot
 * @since       1.2.0
 */
class WPCP_Article extends WPCP_Module {

	/**
	 * Article.
	 *
	 * @var string $module Article.
	 *
	 * @since 1.0.0
	 */
	protected $module = 'article';

	/**
	 * The single instance of the class
	 *
	 * @var mixed $this Instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * WPCP_Article constructor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		// Option fields.
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_article_campaign_options_meta_fields', 'wpcp_keyword_field' );
		parent::__construct( $this->module );
	}

	/**
	 * Get module icon.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_module_icon() {
		return '';
	}

	/**
	 * Get template tags.
	 *
	 * @since 1.2.0
	 * @return array
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
	 * Get default template.
	 *
	 * @since 1.2.0
	 * @return string
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
	 * Add campaign option fields.
	 *
	 * @param object $post The post object.
	 *
	 * @since 1.2..0
	 * @return void
	 */
	public function add_campaign_option_fields( $post ) {

		echo WPCP_HTML::start_double_columns();
		echo WPCP_HTML::select_input(
			array(
				'name'          => '_article_region',
				'label'         => esc_html__( 'Select region to search article', 'wp-content-pilot' ),
				'options'       => $this->get_article_region(),
				'default'       => 'global',
				'class'         => 'wpcp-select2',
				'wrapper_class' => 'pro',
				'attrs'         => array(
					'disabled' => 'disabled',
				),
			)
		);

		//phpcs:disable
		//echo WPCP_HTML::select_input( array(
		//	'name'          => '_article_language',
		//	'label'         => __( 'Select language to search article', 'wp-content-pilot' ),
		//	'options'       => $this->get_article_language(),
		//	'default'       => 'en',
		//	'wrapper_class' => 'pro',
		//	'attrs'         => array(
		//		'disabled' => 'disabled',
		//	)
		//) );
		//phpcs:enable

		echo WPCP_HTML::end_double_columns();
	}

	/**
	 * Save campaign meta.
	 *
	 * @param int          $campaign_id The campaign ID.
	 * @param string|mixed $posted Post status.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {}

	/**
	 * Get setting section.
	 *
	 * @param array $sections Array of setting sections.
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public function get_setting_section( $sections ) {
		$sections[] = array(
			'id'    => 'wpcp_settings_article',
			'title' => __( 'Article Settings', 'wp-content-pilot' ),
		);

		return $sections;
	}

	/**
	 * Get setting fields.
	 *
	 * @param array $fields Setting fields.
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public function get_setting_fields( $fields ) {
		$fields['wpcp_settings_article'] = array(
			array(
				'name'        => 'banned_hosts',
				'label'       => __( 'Banned hosts', 'wp-content-pilot' ),
				'desc'        => __( 'Articles from the above hosts will be rejected. Put single url/host per line.', 'wp-content-pilot' ),
				'placeholder' => __( "example.com \n example1.com", 'wp-content-pilot' ),
				'type'        => 'textarea',
			),
		);

		return $fields;
	}

	/**
	 * Get the post.
	 *
	 * @param int $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return array|WP_Error
	 * @throws ErrorException Through exception.
	 */
	public function get_post( $campaign_id ) {
		// Before it was getting keywords but now we are changing to source instead of keywords it can be anything.
		$keywords = $this->get_campaign_meta( $campaign_id );
		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		wpcp_logger()->info( __( 'Loaded Article campaign', 'wp-content-pilot' ), $campaign_id );

		// Loop through keywords.
		foreach ( $keywords as $keyword ) {
			wpcp_logger()->info( sprintf( /* translators: The article search keywords. */ __( 'Looking for article for the keyword [ %s ]', 'wp-content-pilot' ), $keyword ), $campaign_id );

			if ( $this->is_deactivated_key( $campaign_id, $keyword ) ) {
// phpcs:disable
//				$reactivate_keyword_action = add_query_arg( [
//					'campaign_id' => $campaign_id,
//					'keyword'     => $keyword,
//					'action'      => 'wpcp_reactivate_keyword'
//				], admin_url( 'admin-post.php' ) );
//				wpcp_logger()->info( sprintf( __( 'The keyword is deactivated for 1 hr because last time could not find any article with keyword [%s] %s reactivate keyword %s', 'wp-content-pilot' ), $keyword, '<a href="' . $reactivate_keyword_action . '">', '</a>' ), $campaign_id );
// phpcs:enable
				wpcp_logger()->info( /* translators: The article search keywords. */ __( 'The keyword is deactivated for 1 hr because last time could not find any article with keyword [%s]', 'wp-content-pilot' ), $campaign_id );
				continue;
			}

			// Get links from database.
			wpcp_logger()->info( __( 'Checking for cached links in store', 'wp-content-pilot' ), $campaign_id );
			$links = $this->get_links( $keyword, $campaign_id );
			if ( empty( $links ) ) {
				wpcp_logger()->info( __( 'No cached links in store. Generating new links...', 'wp-content-pilot' ), $campaign_id );
				$this->discover_links( $campaign_id, $keyword );
				$links = $this->get_links( $keyword, $campaign_id );
			}

			wpcp_logger()->info( __( 'Looping through cached links for publishing article', 'wp-content-pilot' ), $campaign_id );
			foreach ( $links as $link ) {
				wpcp_logger()->info( sprintf( /* translators: Article source URL. */ __( 'Generating article from [%s]', 'wp-content-pilot' ), $link->url ), $campaign_id );

				$this->update_link( $link->id, array( 'status' => 'failed' ) );

				$curl = $this->setup_curl();
				$curl->get( $link->url );

				if ( $curl->isError() && 'cron' !== $this->initiator ) {
					wpcp_logger()->error( sprintf( /* translators: Error message. */ __( 'Failed processing link reason [%s]', 'wp-content-pilot' ), $curl->getErrorMessage() ), $campaign_id );
					continue;
				}

				wpcp_logger()->info( __( 'Extracting post content from request', 'wp-content-pilot' ), $campaign_id );

				$html        = $curl->response;
				$readability = new WPCP_Readability();
				$readable    = $readability->parse( $html, $link->url );
				if ( is_wp_error( $readable ) ) {
					wpcp_logger()->error( sprintf( /* translators: The error message. */ __( 'Failed readability reason [%s] changing to different link', 'wp-content-pilot' ), $readable->get_error_message() ), $campaign_id );
					continue;
				}

				// Check if the clean title metabox is checked and perform title cleaning.
				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' === $check_clean_title ) {
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
				$this->update_link(
					$link->id,
					array(
						'status' => 'success',
						'meta'   => '',
					)
				);

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', sprintf( /* translators: */ __( 'No article generated check %1$slog%2$s for details.', 'wp-content-pilot' ), '<a href="' . $log_url . '">', '</a>' ) );
	}

	/**
	 * Discover the links.
	 *
	 * @param int    $campaign_id The campaign ID.
	 * @param string $keyword The key.
	 *
	 * @since 1.2.0
	 * @return bool|mixed|WP_Error
	 * @throws ErrorException Throws error exception.
	 */
	protected function discover_links( $campaign_id, $keyword ) {
		$page_key    = $this->get_unique_key( $keyword );
		$page_number = wpcp_get_post_meta( $campaign_id, $page_key, 0 );

		$args = apply_filters(
			'wpcp_article_search_args',
			array(
				'q'     => rawurlencode( $keyword ),
				'count' => 10,
				'loc'   => 'en',
				// Remove or add this: 'format' => 'rss',.
				'first' => ( $page_number * 10 ),
			),
			$campaign_id
		);

		$endpoint = add_query_arg(
			array(
				$args,
			),
			'https://www.bing.com/search'
		);
		// phpcs:disable
		// wpcp_logger()->debug( sprintf( 'Searching page url [%s]', $endpoint ), $campaign_id );
		// phpcs:enable
		wpcp_logger()->info( sprintf( /* translators: 1: Endpoint. */ __( 'Searching page url [%s]', 'wp-content-pilot' ), $endpoint ), $campaign_id );

		$curl     = $this->setup_curl();
		$response = $curl->get( $endpoint );
		if ( $curl->isError() ) {
			wpcp_logger()->error( $curl->errorMessage, $campaign_id ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->deactivate_key( $campaign_id, $keyword );

			return $response;
		}
// phpcs:disable
//		if ( ! $response instanceof \SimpleXMLElement ) {
//			$response = simplexml_load_string( $response );
//		}
// phpcs:enable
		wpcp_logger()->info( __( 'Extracting response from request', 'wp-content-pilot' ), $campaign_id );
		$dom     = wpcp_str_get_html( $response );
		$matches = array();
		for ( $i = 0; $i < 10; $i++ ) {
			if ( $dom->getElementsByTagName( '<h2>', $i ) ) {
				$matches[] = $dom->getElementsByTagName( '<h2>', $i )->innerText();
			}
		}
		// phpcs:disable
		// preg_match_all( '/<h2><a href="([^"]+)"\s*h="ID=SERP,[0-9]{4}\.1"/', $response, $matches );
		// preg_match_all( '/<h2><a href="([^"]+)"\s*h="ID=SERP,[0-9]{4}\.1">([^"]+)</', $dom, $matches );
		// $response = json_encode( $response );
		// $response = json_decode( $response, true );
		// phpcs:enable

		// Check if links exist.
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
		$banned_hosts = array_merge(
			$banned_hosts,
			array(
				'youtube.com',
				'wikipedia',
				'dictionary',
				'youtube',
				'wikihow',
			)
		);

		$links = array();

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
				wpcp_logger()->info(
					sprintf(
					/* translators: %s URL, %s URL, %s Remove */
						__( 'The link [%s] is already in database, skipping. Remove it from database: <a href="#remove-cached-link" class="wpcp_remove_cached_link" data-link="%s">%s</a>', 'wp-content-pilot' ),
						esc_url( $link ),
						esc_url( $link ),
						__( 'Remove', 'wp-content-pilot' )
					),
					$campaign_id
				);

				continue;
			}

			$skip = apply_filters( 'wpcp_skip_duplicate_title', false, $title, $campaign_id );
			if ( $skip ) {
				continue;
			}

			$links[] = array(
				'url'     => $link,
				'title'   => $title,
				'for'     => $keyword,
				'camp_id' => $campaign_id,
			);
		}

		$total_inserted = $this->inset_links( $links );
		wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
		wpcp_logger()->info( sprintf( 'Total found links [%d] and accepted [%d] and rejected [%d]', count( $links ), $total_inserted, ( count( $links ) - $total_inserted ) ), $campaign_id );

		return true;
	}

	/**
	 * Get all supported regions for searching article.
	 *
	 * @since 1.1.1
	 * @return array
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
	 * Get all supported languages for searching article.
	 *
	 * @since 1.1.1
	 * @return array
	 */
	public function get_article_language() {

		$languages = array(
			'ar'      => 'Arabic',
			'eu'      => 'Basque',
			'bn'      => 'Bengali',
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
	 * Ensures only one instance of WPCP_Article is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return WPCP_Article Main instance.
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

WPCP_Article::instance();
