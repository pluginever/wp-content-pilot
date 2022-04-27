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
//			'default'       => 'lang_en',
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
				'label'       => __( 'Banned Hosts', 'wp-content-pilot' ),
				'desc'        => __( 'Articles from the above hosts will be rejected. put single url/host per line.', 'wp-content-pilot' ),
				'placeholder' => __( "example.com \n example1.com", 'wp-content-pilot' ),
				'type'        => 'textarea',
			),
			array(
				'name'              => 'google_search_api_key',
				'label'             => __( 'Google Search Api Key', 'wp-content-pilot' ),
				'desc'              => __( 'Google custom search api key will be needed to get the result.', 'wp-content-pilot' ),
				'type'              => 'password',
				'default'           => '',
				'sanitize_callback' => 'esc_html',
			),
			array(
				'name'              => 'search_engine_id',
				'label'             => __( 'Search Engine ID', 'wp-content-pilot' ),
				'desc'              => __( 'Search engine id for searching the links', 'wp-content-pilot' ),
				'type'              => 'text',
				'default'           => '',
				'sanitize_callback' => 'esc_html'
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
		wpcp_logger()->info( __( 'Loaded Article campaign', 'wp-content-pilot' ), $campaign_id );

		$api_key          = wpcp_get_settings( 'google_search_api_key', 'wpcp_settings_article' );
		$search_engine_id = wpcp_get_settings( 'search_engine_id', 'wpcp_settings_article' );

		wpcp_logger()->info( __( 'Checking google search api key and search engine id for authentication', 'wp-content-pilot' ), $campaign_id );
		if ( empty( $api_key ) || empty( $search_engine_id ) ) {
			wpcp_disable_campaign( $campaign_id );

			$notice = __( 'Google custom search api or search engine id is not set.So, the campaign wont run, disabling campaign.', 'wp-content-pilot' );
			wpcp_logger()->error( $notice, $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		//before it was getting keywords, now we are changing to source instead of keywords
		//it can be anything
		$keywords = $this->get_campaign_meta( $campaign_id );
		if ( empty( $keywords ) ) {
			wpcp_logger()->error( __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ), $campaign_id );
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

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
		$page_key         = $this->get_unique_key( $keyword );
		$page_number      = wpcp_get_post_meta( $campaign_id, $page_key, 0 );
		$api_key          = wpcp_get_settings( 'google_search_api_key', 'wpcp_settings_article' );
		$search_engine_id = wpcp_get_settings( 'search_engine_id', 'wpcp_settings_article' );

		/*
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
		*/
		$args = apply_filters( 'wpcp_article_search_args', array(
			'key'    => $api_key,
			'cx'     => ! empty( $search_engine_id ) ? $search_engine_id : '359394892d6b9fe2c',
			'q'      => urlencode( $keyword ),
			'number' => 10,
			'gl'     => 'us',
		), $campaign_id );

		if ( ! empty( $page_number ) ) {
			$args['start'] = ( $page_number * 10 ) + 1;
		}

		$endpoint = add_query_arg( array(
			$args
		), 'https://customsearch.googleapis.com/customsearch/v1' );

		wpcp_logger()->info( sprintf( __( 'Searching page url [%s]', 'wp-content-pilot' ), preg_replace( array( '/key=([^&]+)/m', '/cx=([^&]+)/m' ), array( 'key=X', 'cx=X' ), $endpoint ), $campaign_id ) );

		$curl    = $this->setup_curl();
		$request = $curl->get( $endpoint );

		if ( $curl->isError() ) {
			wpcp_logger()->error( $curl->errorMessage, $campaign_id );
			$this->deactivate_key( $campaign_id, $keyword );

			return $request;
		}

		wpcp_logger()->info( __( 'Extracting response from request', 'wp-content-pilot' ), $campaign_id );
		$items = $curl->getResponse()->items;

		if ( empty( $items ) ) {
			$message = __( 'Could not find any links from search engine, deactivating keyword for an hour.', 'wp-content-pilot' );
			wpcp_logger()->error( $message, $campaign_id );
			$this->deactivate_key( $campaign_id, $keyword );

			return new WP_Error( 'no-links-found', $message );
		}

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
			$title = ! empty( $item->title ) ? $item->title : '';
			$link  = ! empty( $item->pagemap->metatags[0]->{'og:url'} ) ? $item->pagemap->metatags[0]->{'og:url'} : '';
			//$link  = ! empty( $item->link ) ? $item->link : '';

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


			$links[] = array(
				'url'     => $link,
				'title'   => $title,
				'for'     => $keyword,
				'camp_id' => $campaign_id
			);
		}

		/*
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
		}*/

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
			"af" => "Afghanistan",
			"al" => "Albania",
			"dz" => "Algeria",
			"as" => "American Samoa",
			"ad" => "Andorra",
			"ao" => "Angola",
			"ai" => "Anguilla",
			"aq" => "Antarctica",
			"ag" => "Antigua and Barbuda",
			"ar" => "Argentina",
			"am" => "Armenia",
			"aw" => "Aruba",
			"au" => "Australia",
			"at" => "Austria",
			"az" => "Azerbaijan",
			"bs" => "Bahamas",
			"bh" => "Bahrain",
			"bd" => "Bangladesh",
			"bb" => "Barbados",
			"by" => "Belarus",
			"be" => "Belgium",
			"bz" => "Belize",
			"bj" => "Benin",
			"bm" => "Bermuda",
			"bt" => "Bhutan",
			"bo" => "Bolivia",
			"bq" => "Bonaire, Sint Eustatius and Saba",
			"ba" => "Bosnia and Herzegovina",
			"bw" => "Botswana",
			"bv" => "Bouvet Island",
			"br" => "Brazil",
			"io" => "British Indian Ocean Territory",
			"bn" => "Brunei Darussalam",
			"bg" => "Bulgaria",
			"bf" => "Burkina Faso",
			"bi" => "Burundi",
			"kh" => "Cambodia",
			"cm" => "Cameroon",
			"ca" => "Canada",
			"cv" => "Cape Verde",
			"ky" => "Cayman Islands",
			"cf" => "Central African Republic",
			"td" => "Chad",
			"cl" => "Chile",
			"cn" => "China",
			"cx" => "Christmas Island",
			"cc" => "Cocos (Keeling) Islands",
			"co" => "Colombia",
			"km" => "Comoros",
			"cg" => "Congo",
			"cd" => "Congo, the Democratic Republic of the",
			"ck" => "Cook Islands",
			"cr" => "Costa Rica",
			"ci" => "Cote D'Ivoire",
			"hr" => "Croatia",
			"cu" => "Cuba",
			"cw" => "Curacao",
			"cy" => "Cyprus",
			"cz" => "Czech Republic",
			"dk" => "Denmark",
			"dj" => "Djibouti",
			"dm" => "Dominica",
			"do" => "Dominican Republic",
			"ec" => "Ecuador",
			"eg" => "Egypt",
			"sv" => "El Salvador",
			"gq" => "Equatorial Guinea",
			"er" => "Eritrea",
			"ee" => "Estonia",
			"et" => "Ethiopia",
			"fk" => "Falkland Islands (Malvinas)",
			"fo" => "Faroe Islands",
			"fj" => "Fiji",
			"fi" => "Finland",
			"fr" => "France",
			"gf" => "French Guiana",
			"pf" => "French Polynesia",
			"tf" => "French Southern Territories",
			"ga" => "Gabon",
			"gm" => "Gambia",
			"ge" => "Georgia",
			"de" => "Germany",
			"gh" => "Ghana",
			"gi" => "Gibraltar",
			"gr" => "Greece",
			"gl" => "Greenland",
			"gd" => "Grenada",
			"gp" => "Guadeloupe",
			"gu" => "Guam",
			"gt" => "Guatemala",
			"gg" => "Guernsey",
			"gn" => "Guinea",
			"gw" => "Guinea-Bissau",
			"gy" => "Guyana",
			"ht" => "Haiti",
			"hm" => "Heard Island and Mcdonald Islands",
			"va" => "Holy See (Vatican City State)",
			"hn" => "Honduras",
			"hk" => "Hong Kong",
			"hu" => "Hungary",
			"is" => "Iceland",
			"in" => "India",
			"id" => "Indonesia",
			"ir" => "Iran, Islamic Republic of",
			"iq" => "Iraq",
			"ie" => "Ireland",
			"im" => "Isle of Man",
			"il" => "Israel",
			"it" => "Italy",
			"jm" => "Jamaica",
			"jp" => "Japan",
			"je" => "Jersey",
			"jo" => "Jordan",
			"kz" => "Kazakhstan",
			"ke" => "Kenya",
			"ki" => "Kiribati",
			"kp" => "Korea, Democratic People's Republic of",
			"kr" => "Korea, Republic of",
			"xk" => "Kosovo",
			"kw" => "Kuwait",
			"kg" => "Kyrgyzstan",
			"la" => "Lao People's Democratic Republic",
			"lv" => "Latvia",
			"lb" => "Lebanon",
			"ls" => "Lesotho",
			"lr" => "Liberia",
			"ly" => "Libyan Arab Jamahiriya",
			"li" => "Liechtenstein",
			"lt" => "Lithuania",
			"lu" => "Luxembourg",
			"mo" => "Macao",
			"mk" => "Macedonia, the Former Yugoslav Republic of",
			"mg" => "Madagascar",
			"mw" => "Malawi",
			"my" => "Malaysia",
			"mv" => "Maldives",
			"ml" => "Mali",
			"mt" => "Malta",
			"mh" => "Marshall Islands",
			"mq" => "Martinique",
			"mr" => "Mauritania",
			"mu" => "Mauritius",
			"yt" => "Mayotte",
			"mx" => "Mexico",
			"fm" => "Micronesia, Federated States of",
			"md" => "Moldova, Republic of",
			"mc" => "Monaco",
			"mn" => "Mongolia",
			"me" => "Montenegro",
			"ms" => "Montserrat",
			"ma" => "Morocco",
			"mz" => "Mozambique",
			"mm" => "Myanmar",
			"na" => "Namibia",
			"nr" => "Nauru",
			"np" => "Nepal",
			"nl" => "Netherlands",
			"an" => "Netherlands Antilles",
			"nc" => "New Caledonia",
			"nz" => "New Zealand",
			"ni" => "Nicaragua",
			"ne" => "Niger",
			"ng" => "Nigeria",
			"nu" => "Niue",
			"nf" => "Norfolk Island",
			"mp" => "Northern Mariana Islands",
			"no" => "Norway",
			"om" => "Oman",
			"pk" => "Pakistan",
			"pw" => "Palau",
			"ps" => "Palestinian Territory, Occupied",
			"pa" => "Panama",
			"pg" => "Papua New Guinea",
			"py" => "Paraguay",
			"pe" => "Peru",
			"ph" => "Philippines",
			"pn" => "Pitcairn",
			"pl" => "Poland",
			"pt" => "Portugal",
			"pr" => "Puerto Rico",
			"qa" => "Qatar",
			"re" => "Reunion",
			"ro" => "Romania",
			"ru" => "Russian Federation",
			"rw" => "Rwanda",
			"bl" => "Saint Barthelemy",
			"sh" => "Saint Helena",
			"kn" => "Saint Kitts and Nevis",
			"lc" => "Saint Lucia",
			"mf" => "Saint Martin",
			"pm" => "Saint Pierre and Miquelon",
			"vc" => "Saint Vincent and the Grenadines",
			"ws" => "Samoa",
			"sm" => "San Marino",
			"st" => "Sao Tome and Principe",
			"sa" => "Saudi Arabia",
			"sn" => "Senegal",
			"rs" => "Serbia",
			"cs" => "Serbia and Montenegro",
			"sc" => "Seychelles",
			"sl" => "Sierra Leone",
			"sg" => "Singapore",
			"sx" => "Sint Maarten",
			"sk" => "Slovakia",
			"si" => "Slovenia",
			"sb" => "Solomon Islands",
			"so" => "Somalia",
			"za" => "South Africa",
			"gs" => "South Georgia and the South Sandwich Islands",
			"ss" => "South Sudan",
			"es" => "Spain",
			"lk" => "Sri Lanka",
			"sd" => "Sudan",
			"sr" => "Suriname",
			"sj" => "Svalbard and Jan Mayen",
			"sz" => "Swaziland",
			"se" => "Sweden",
			"ch" => "Switzerland",
			"sy" => "Syrian Arab Republic",
			"tw" => "Taiwan, Province of China",
			"tj" => "Tajikistan",
			"tz" => "Tanzania, United Republic of",
			"th" => "Thailand",
			"tl" => "Timor-Leste",
			"tg" => "Togo",
			"tk" => "Tokelau",
			"to" => "Tonga",
			"tt" => "Trinidad and Tobago",
			"tn" => "Tunisia",
			"tr" => "Turkey",
			"tm" => "Turkmenistan",
			"tc" => "Turks and Caicos Islands",
			"tv" => "Tuvalu",
			"ug" => "Uganda",
			"ua" => "Ukraine",
			"ae" => "United Arab Emirates",
			"gb" => "United Kingdom",
			"us" => "United States",
			"um" => "United States Minor Outlying Islands",
			"uy" => "Uruguay",
			"uz" => "Uzbekistan",
			"vu" => "Vanuatu",
			"ve" => "Venezuela",
			"vn" => "Viet Nam",
			"vg" => "Virgin Islands, British",
			"vi" => "Virgin Islands, U.s.",
			"wf" => "Wallis and Futuna",
			"eh" => "Western Sahara",
			"ye" => "Yemen",
			"zm" => "Zambia",
			"zw" => "Zimbabwe"
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
			'lang_ar'    => 'Arabic',
			'lang_bg'    => 'Bulgarian',
			'lang_ca'    => 'Catalan',
			'lang_cs'    => 'Czech',
			'lang_da'    => 'Danish',
			'lang_de'    => 'German',
			'lang_el'    => 'Greek',
			'lang_en'    => 'English',
			'lang_es'    => 'Spanish',
			'lang_et'    => 'Estonian',
			'lang_fi'    => 'Finish',
			'lang_fr'    => 'French',
			'lang_hu'    => 'Hungarian',
			'lang_id'    => 'Indonesian',
			'lang_is'    => 'Icelandic',
			'lang_it'    => 'Italian',
			'lang_iw'    => 'Hebrew',
			'lang_ja'    => 'Japanese',
			'lang_ko'    => 'Korean',
			'lang_lv'    => 'Latvian',
			'lang_nl'    => 'Dutch',
			'lang_no'    => 'Norwegian',
			'lang_pl'    => 'Polish',
			'lang_pt'    => 'Portugese',
			'lang_ro'    => 'Romanian',
			'lang_ru'    => 'Russian',
			'lang_sk'    => 'Slovak',
			'lang_sl'    => 'Slovenian',
			'lang_sr'    => 'Serbian',
			'lang_sv'    => 'Swedish',
			'lang_tr'    => 'Turkish',
			'lang_zh-CN' => 'Chinese (Simplified)',
			'lang_zh-TW' => 'Chinese (Traditional)',
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
