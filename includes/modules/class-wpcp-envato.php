<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Envato extends WPCP_Module {

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
		add_action( 'wpcp_envato_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_envato_campaign_options_meta_fields', 'wpcp_keyword_field' );

		parent::__construct( 'envato' );
	}

	public function get_module_icon() {
		// TODO: Implement get_module_icon() method.
	}

	/**
	 * @return array
	 * @since 1.2.0
	 */
	public function get_template_tags() {
		return array(
			'title'                  => __( 'Title', 'wp-content-pilot' ),
			'content'                => __( 'Content', 'wp-content-pilot' ),
			'image_url'              => __( 'Main image url', 'wp-content-pilot' ),
			'source_url'             => __( 'Source link', 'wp-content-pilot' ),
			'classification'         => __( 'Item classification', 'wp-content-pilot' ),
			'classification_url'     => __( 'Item classification url', 'wp-content-pilot' ),
			'price'                  => __( 'Price USD', 'wp-content-pilot' ),
			'number_of_sales'        => __( 'Number of sales', 'wp-content-pilot' ),
			'rating'                 => __( 'Rating', 'wp-content-pilot' ),
			'author_username'        => __( 'Author user name', 'wp-content-pilot' ),
			'author_url'             => __( 'Author url', 'wp-content-pilot' ),
			'author_image'           => __( 'Author image url', 'wp-content-pilot' ),
			'summary'                => __( 'Item summary', 'wp-content-pilot' ),
			'tags'                   => __( 'Tags', 'wp-content-pilot' ),
			'description_html'       => __( 'HTML description', 'wp-content-pilot' ),
			'affiliate_url'          => __( 'Affiliate URL', 'wp-content-pilot' ),
			'affiliate_live_preview' => __( 'Affiliate Live Preview URL', 'wp-content-pilot' ),
			'preview'                => __( 'Audio/Video Preview.', 'wp-content-pilot' ),
			'categories'               => __( 'Categories', 'wp-content-pilot' ),
		);
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_default_template() {
		$template
			= <<<EOT
<img src="{image_url}" alt="">
<br>
<a target="_blank" href="{affiliate_live_preview}">LIVE PREVIEW</a>
<a target="_blank" href="{affiliate_url}">BUY FOR {price}</a>
{content}
<br>
<a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {

		echo WPCP_HTML::start_double_columns();

		echo WPCP_HTML::select_input( array(
			'label'       => __( 'Platform', 'wp-content-pilot' ),
			'name'        => '_platform',
			'placeholder' => '',
			'options'     => array(
				'themeforest.net'  => 'ThemeForest',
				'codecanyon.net'   => 'CodeCanyon',
				'photodune.net'    => 'PhotoDune',
				'videohive.net'    => 'VideoHive',
				'graphicriver.net' => 'GraphicRiver',
				'3docean.net'      => '3DOcean',
				'audiojungle.net'  => 'AudioJungle',
			),
			'tooltip'     => __( 'Select envato platform', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::text_input( array(
			'label'       => __( 'Price Range', 'wp-content-pilot' ),
			'name'        => '_price_range',
			'placeholder' => '20|100',
			'desc'        => __( 'separate min max price with (|). e.g. 20|100', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'label'   => __( 'Sort By', 'wp-content-pilot' ),
			'name'    => '_envato_sort_by',
			'options' => array(
				'relevance' => __( 'Relevance', 'wp-content-pilot' ),
				'following' => __( 'Following', 'wp-content-pilot' ),
				'rating'    => __( 'Rating', 'wp-content-pilot' ),
				'sales'     => __( 'Sales', 'wp-content-pilot' ),
				'price'     => __( 'Price', 'wp-content-pilot' ),
				'date'      => __( 'Date', 'wp-content-pilot' ),
				'updated'   => __( 'Updated', 'wp-content-pilot' ),
				'name'      => __( 'Name', 'wp-content-pilot' ),
				'Trending'  => __( 'Trending', 'wp-content-pilot' ),
			),
			'desc'    => __( 'Select how the result will be sorted', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'label'   => __( 'Sort Direction', 'wp-content-pilot' ),
			'name'    => '_envato_sort_direction',
			'options' => array(
				'asc'  => __( 'ASC', 'wp-content-pilot' ),
				'desc' => __( 'DESC', 'wp-content-pilot' ),
			),
			'desc'    => __( 'Select sort direction for the result set', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::text_input( array(
			'label' => __( 'Search in specific category', 'wp-content-pilot' ),
			'name'  => '_envato_search_category',
			'desc'  => sprintf( __( 'For example: Use <a href="%s" target="_blank">site-templates</a> for this category.', 'wp-content-pilot' ), 'https://themeforest.net/category/site-templates' ),
		) );

		echo WPCP_HTML::text_input( array(
			'label' => __( 'Search in specific author', 'wp-content-pilot' ),
			'name'  => '_envato_search_author',
			'desc'  => sprintf( __( 'For example: Use <a href="%s" target="_blank">2codethemes</a> for this author.', 'wp-content-pilot' ), 'https://themeforest.net/user/2codethemes' ),
		) );

		echo WPCP_HTML::end_double_columns();

	}

	/**
	 * @param $campaign_id
	 * @param $posted
	 */
	public function save_campaign_meta( $campaign_id, $posted ) {
		$price_range        = empty( $posted['_price_range'] ) ? '' : sanitize_text_field( $posted['_price_range'] );
		$price_range_ranges = wpcp_string_to_array( $price_range, '|', array( 'trim', 'intval' ) );
		$price_range_ranges = empty( $price_range_ranges ) ? '' : implode( '|', $price_range_ranges );
		update_post_meta( $campaign_id, '_platform', empty( $posted['_platform'] ) ? 'no' : sanitize_text_field( $posted['_platform'] ) );
		update_post_meta( $campaign_id, '_price_range', $price_range_ranges );
		update_post_meta( $campaign_id, '_envato_sort_by', empty( $posted['_envato_sort_by'] ) ? 'no' : sanitize_text_field( $posted['_envato_sort_by'] ) );
		update_post_meta( $campaign_id, '_envato_sort_direction', empty( $posted['_envato_sort_direction'] ) ? 'no' : sanitize_text_field( $posted['_envato_sort_direction'] ) );
		update_post_meta( $campaign_id, '_envato_search_category', empty( $posted['_envato_search_category'] ) ? '' : sanitize_text_field( $posted['_envato_search_category'] ) );
		update_post_meta( $campaign_id, '_envato_search_author', empty( $posted['_envato_search_author'] ) ? '' : sanitize_text_field( $posted['_envato_search_author'] ) );
	}

	/**
	 * @param $section
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_setting_section( $sections ) {
		$sections[] = [
			'id'    => 'wpcp_settings_envato',
			'title' => __( 'Envato', 'wp-content-pilot' )
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
		$fields['wpcp_settings_envato'] = [
			array(
				'name'    => 'token',
				'label'   => __( 'Envato token', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Check this tutorial to get your <a href="%s" target="_blank">Envato token</a>.', 'wp-content-pilot' ), 'https://www.pluginever.com/docs/wp-content-pilot/how-to-create-envato-token/' ),
				'type'    => 'password',
				'default' => ''
			),
			array(
				'name'    => 'envato_impact_radius',
				'label'   => __( 'Impact radius affiliate url', 'wp-content-pilot' ),
				'desc'    => sprintf( __( 'Learn how to get your impact radius affiliate url <a href="%s">here</a>.', 'wp-content-pilot' ), 'https://www.pluginever.com/docs/wp-content-pilot/get-your-envato-impact-radius-affiliate-url/' ),
				'type'    => 'text',
				'default' => ''
			),
		];

		return $fields;
	}


	/**
	 * @param $campaign_id , $keywords
	 *
	 * @return mixed|void
	 * @throws ErrorException
	 */
	public function get_post( $campaign_id ) {
		wpcp_logger()->info( __( 'Loaded Envato Campaign', 'wp-content-pilot' ), $campaign_id );

		$token                = wpcp_get_settings( 'token', 'wpcp_settings_envato', '' );
		$envato_impact_radius = wpcp_get_settings( 'envato_impact_radius', 'wpcp_settings_envato', '' );

		wpcp_logger()->info( __( "Checking envato api key and impact radius url for authentication..", 'wp-content-pilot' ), $campaign_id );
		if ( empty( $token ) ) {
			$notice = __( 'The Envato api key is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot' );

			wpcp_logger()->error( $notice, $campaign_id );
			wpcp_disable_campaign( $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		if ( empty( $envato_impact_radius ) ) {
			$affiliate_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-settings#wpcp_settings_envato' );
			$warning       = sprintf( __( "The Impact  Radius affiliate url is not set. Set it from <a href='%s'>here</a>", 'wp-content-pilot' ), $affiliate_url );
			wpcp_logger()->error( $warning, $campaign_id );

			wpcp_admin_notice( $warning );
		}

		$keywords = $this->get_campaign_meta( $campaign_id );
		if ( empty( $keywords ) ) {
			return new WP_Error( 'missing-data', __( 'Campaign do not have keyword to proceed, please set keyword', 'wp-content-pilot' ) );
		}

		foreach ( $keywords as $keyword ) {
			wpcp_logger()->info( sprintf( __( 'Looping through keywords [ %s ]', 'wp-content-pilot' ), $keyword ), $campaign_id );


			$total_page_key = $this->get_unique_key( "$keyword-total-page" );
			$page_key       = $this->get_unique_key( $keyword );
			$total_page     = wpcp_get_post_meta( $campaign_id, $total_page_key, '' );
			$page_number    = wpcp_get_post_meta( $campaign_id, $page_key, 1 );
			$site           = wpcp_get_post_meta( $this->campaign_id, '_platform', null );
			$sort_by        = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_by', 'relevance' );
			$sort_direction = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_direction', 'asc' );
			$price_range    = wpcp_get_post_meta( $this->campaign_id, '_price_range', '' );
			$category       = wpcp_get_post_meta( $this->campaign_id, '_envato_search_category', '' );
			$author         = wpcp_get_post_meta( $this->campaign_id, '_envato_search_author', '' );

			$price_range = explode( '|', $price_range );
			$min_price   = ! empty( $price_range[0] ) ? trim( $price_range[0] ) : 0;
			$max_price   = ! empty( $price_range[1] ) ? trim( $price_range[1] ) : 0;

			$query_args = [
				'site'           => $site,
				'term'           => urlencode( $keyword ),
				'category'       => '',
				'page'           => $page_number,
				'page_size'      => 1,
				'sort_by'        => $sort_by,
				'sort_direction' => $sort_direction,
			];

			if ( ! empty( $min_price ) ) {
				$query_args['price_min'] = $min_price;
			}
			if ( ! empty( $max_price ) ) {
				$query_args['price_max'] = $max_price;
			}

			if ( '' !== $category ) {
				$query_args['category'] = $category;
			}
			if ( '' !== $author ) {
				$query_args['username'] = $author;
			}
			$api_url  = 'https://api.envato.com/v1/discovery/search/search/item';
			$endpoint = add_query_arg( $query_args, $api_url );
			wpcp_logger()->info( sprintf( __( 'Searching for items url [ %s ]', 'wp-content-pilot' ), $endpoint ), $campaign_id );

			$curl = $this->setup_curl();
			$curl->setHeader( 'Authorization', sprintf( 'bearer %s', trim( $token ) ) );
			$curl->get( $endpoint );

			if ( $curl->isError() ) {
				$message = sprintf( __( 'Envato api request failed response [ %s ]', 'wp-content-pilot' ), $curl->getResponse()->error );
				wpcp_logger()->error( $message, $campaign_id );
				wpcp_disable_campaign( $campaign_id );

				return new WP_Error( 'missing-data', $message );
			}

			$response = $curl->getResponse();

			if ( empty( $response->matches ) ) {
				$message = __( 'No matching data found from api disabling the keyword for 1 hour', 'wp-content-pilot' );
				$this->deactivate_key( $campaign_id, $keyword );
				wpcp_logger()->error( $message, $campaign_id );
				continue;
			}

			foreach ( $response->matches as $item ) {
				wpcp_logger()->info( __( 'Checking duplicate links in store...', 'wp-content-pilot' ), $campaign_id );
				if ( wpcp_is_duplicate_url( $item->url ) ) {
					wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
					continue;
				}

				$skip = apply_filters( 'wpcp_skip_duplicate_title', false, $item->name, $campaign_id );
				if ( $skip ) {
					wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );
					continue;
				}

				wpcp_logger()->info( __( 'Finding images from item preview', 'wp-content-pilot' ), $campaign_id );
				$image  = '';
				$images = $item->previews;
				/** Specific for audiojungle and videohive*/

				$preview_url = '';

				if ( 'codecanyon.net' === $site || 'themeforest.net' === $site || '3docean.net' === $site ) {
					if ( ! empty( $images ) && ! empty( $images->landscape_preview ) ) {
						$images = $images->landscape_preview;
					}
					if ( ! empty( $images->landscape_url ) ) {
						$image = $images->landscape_url;
					}
				} elseif ( 'photodune.net' === $site ) {
					$image = $images->icon_with_thumbnail_preview->thumbnail_url;
				} elseif ( 'videohive.net' === $site ) {
					$image       = $images->icon_with_video_preview->landscape_url;
					$preview_url = $images->icon_with_video_preview->video_url;
				} elseif ( 'audiojungle.net' === $site ) {
					$image       = $images->icon_with_audio_preview->icon_url;
					$preview_url = $images->icon_with_audio_preview->mp3_url;
				} elseif ( 'graphicriver.net' === $site ) {
					$image = $images->icon_with_square_preview->square_url;
				}

				wpcp_logger()->info( __( 'Making affiliate url', 'wp-content-pilot' ), $campaign_id );
//				$affiliate_url = add_query_arg( array(
//					'u' => urlencode( $item->url )
//				), $envato_impact_radius );

				$affiliate_url         = $item->url;
				$affiliate_preview_url = isset( $item->previews->live_site->url ) ? $item->previews->live_site->url : $affiliate_url;

				//check if the envato impact radius is not empty and change the url with impact radius
				if ( $envato_impact_radius != '' ) {
					$affiliate_url         = add_query_arg( array( 'u' => urlencode( $item->url ) ), $envato_impact_radius );
					$affiliate_preview_url = isset( $item->previews->live_site->url ) ? add_query_arg( array( 'u' => urlencode( $item->previews->live_site->url ) ), $envato_impact_radius ) : '';
				}


				wpcp_logger()->info( __( 'Extracting tags from item', 'wp-content-pilot' ), $campaign_id );
//				$tags = [];
//				if ( @$item->tags ) {
//					$tags = $item->tags;
//				}
//				$tags  = wpcp_array_to_html( $tags );
				$price = wpcp_cent_to_usd( @$item->price_cents );
				wpcp_logger()->info( sprintf( __( 'Generating envato article from [ %s ]', 'wp-content-pilot' ), $item->url ), $campaign_id );

				//check if the clean title metabox is checked and perform title cleaning
				$check_clean_title = wpcp_get_post_meta( $campaign_id, '_clean_title', 'off' );

				if ( 'on' == $check_clean_title ) {
					wpcp_logger()->info( __( 'Cleaning title', 'wp-content-pilot' ), $campaign_id );
					$title = wpcp_clean_title( $item->name );
				} else {
					$title = html_entity_decode( $item->name, ENT_QUOTES );
				}
				$categories = isset( $response->aggregations->category ) ? $response->aggregations->category : array();
				$tags       = isset( $response->aggregations->tags ) ? $response->aggregations->tags : array();

				wpcp_logger()->info( __( 'Extracting categories from response', 'wp-content-pilot' ), $campaign_id );
				$item_category = [];
				foreach ( $categories as $category ) {
					$item_category[] = $category->key;
				}

				wpcp_logger()->info( __( 'Extracting tags from response', 'wp-content-pilot' ), $campaign_id );
				$item_tag = [];
				foreach ( $tags as $tag ) {
					$item_tag[] = $tag->key;
				}

				wpcp_logger()->info( __( 'Combining all parts for article', 'wp-content-pilot' ), $campaign_id );
				$article = [
					'title'                  => $title,
					'content'                => $item->description_html,
					'image_url'              => $image,
					'source_url'             => $item->url,
					'classification'         => sanitize_text_field( @$item->classification ),
					'classification_url'     => sanitize_text_field( @$item->classification ),
					'price'                  => wpcp_price( $price ),
					'number_of_sales'        => intval( @$item->number_of_sales ),
					'rating'                 => $item->rating->rating,
					'author_username'        => sanitize_key( @$item->author_username ),
					'author_url'             => esc_url( @$item->author_url ),
					'author_image'           => esc_url( @$item->author_image ),
					'summary'                => esc_html( @$item->summary ),
					'tags'                   => wpcp_array_to_html( $item_tag ),
					'description_html'       => wp_kses_post( @$item->description_html ),
					'affiliate_url'          => esc_url( $affiliate_url ),
					'affiliate_live_preview' => esc_url( $affiliate_preview_url ),
					'preview'                => esc_url( $preview_url ),
					'categories'               => wpcp_array_to_html( $item_category ),
				];
				wpcp_logger()->info( __( 'Article processed from campaign', 'wp-content-pilot' ), $campaign_id );
				wpcp_update_post_meta( $campaign_id, $page_key, $page_number + 1 );

				wpcp_logger()->info( __( 'Inserting link into store...', 'wp-content-pilot' ), $campaign_id );
				$this->insert_link( array(
					'for'     => $keyword,
					'title'   => $item->name,
					'url'     => $item->url,
					'camp_id' => $campaign_id,
					'status'  => 'success',
					'meta'    => '',
				) );

				return $article;
			}
		}

		$log_url = admin_url( '/edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

		return new WP_Error( 'campaign-error', __( sprintf( 'No envato article generated check <a href="%s">log</a> for details.', $log_url ), 'wp-content-pilot' ) );
	}

	/**
	 * Main WPCP_Envato Instance.
	 *
	 * Ensures only one instance of WPCP_Envato is loaded or can be loaded.
	 *
	 * @return WPCP_Envato Main instance
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

WPCP_Envato::instance();
