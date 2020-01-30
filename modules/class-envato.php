<?php
/**
 * Envato Class
 *
 * @package     WP Content Pilot
 * @subpackage  Envato
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

defined( 'ABSPATH' ) || exit();

class WPCP_Envato extends WPCP_Campaign {
	protected $token;
	protected $envato_impact_radius;

	/**
	 * WPCP_Envato constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_keyword_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', array( $this, 'campaign_option_fields' ) );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_featured_image_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_strip_links_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_use_excerpt_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_external_link_field' );
		add_action( 'wpcp_campaign_envato_options_meta_fields', 'wpcp_featured_image_random_field' );


		add_action( 'wpcp_update_campaign_settings', array( $this, 'update_campaign_settings' ), 10, 2 );
		add_action( 'wpcp_fetching_campaign_contents', array( $this, 'prepare_contents' ) );

		add_filter( 'wpcp_replace_template_tags', array( $this, 'replace_template_tags' ), 10, 2 );
	}

	/**
	 * Get WPCP_Envato default template tags
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public static function get_default_template() {
		$template
			= <<<EOT
<img src="{image_url}" alt="">
<br>
<a target="_blank" href="{affiliate_url}">LIVE PREVIEW</a>
<a target="_blank" href="{affiliate_url}">BUY FOR {price}</a>
{content}
<br>
<a href="{source_url}" target="_blank">Source</a>
EOT;

		return $template;
	}

	/**
	 * Register article module
	 *
	 * @param $modules
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function register_module( $modules ) {
		$modules['envato'] = [
			'title'       => __( 'Envato', 'wp-content-pilot' ),
			'description' => __( 'Scraps articles based on keywords from envato', 'wp-content-pilot' ),
			'supports'    => self::get_template_tags(),
			'callback'    => __CLASS__,
		];

		return $modules;
	}

	/**
	 * Supported template tags
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_template_tags() {
		return array(
			'title'              => __( 'Title', 'wp-content-pilot' ),
			'content'            => __( 'Content', 'wp-content-pilot' ),
			'image_url'          => __( 'Main image url', 'wp-content-pilot' ),
			'source_url'         => __( 'Source link', 'wp-content-pilot' ),
			'date'               => __( 'Published date', 'wp-content-pilot' ),
			'site'               => __( 'Envato platform', 'wp-content-pilot' ),
			'classification'     => __( 'Item classification', 'wp-content-pilot' ),
			'classification_url' => __( 'Item classification url', 'wp-content-pilot' ),
			'price'              => __( 'Price USD', 'wp-content-pilot' ),
			'number_of_sales'    => __( 'Number of sales', 'wp-content-pilot' ),
			'author_username'    => __( 'Author user name', 'wp-content-pilot' ),
			'author_url'         => __( 'Author url', 'wp-content-pilot' ),
			'author_image'       => __( 'Author image url', 'wp-content-pilot' ),
			'summary'            => __( 'Item summary', 'wp-content-pilot' ),
			'tags'               => __( 'tags', 'wp-content-pilot' ),
			'description_html'   => __( 'HTML description', 'wp-content-pilot' ),
			'affiliate_url'      => __( 'Affiliate URL', 'wp-content-pilot' ),
		);
	}

	/**
	 * Conditionally show meta fields
	 *
	 * @param $post_id
	 * @param $campaign_type
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function campaign_option_fields( $post ) {

		echo WPCP_HTML::select_input( array(
			'label'       => __( 'Platform', 'wp-content-pilot' ),
			'name'        => '_platform',
			'placeholder' => '',
			'options'     => array(
				'themeforest.net'  => 'ThemeForest',
				'codecanyon.net'   => 'CodeCanyon',
				'photodune.net'    => 'PhotoDune',
				'videohive.net'    => 'VideoHive',
				'graphicrever.net' => 'GraphicsRever',
				'3docean.net'      => '3DOcean',
			),
			'tooltip'     => __( 'Select envato platform', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::text_input( array(
			'label'          => __( 'Price Range', 'wp-content-pilot' ),
			'name'           => '_price_range',
			'desc'           => __( 'separate min max price with (|). e.g. 20|100', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'label'            => __( 'Sort By', 'wp-content-pilot' ),
			'name'             => '_envato_sort_by',
			'options'          => array(
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
			'desc'             => __( 'Select how the result will be sorted', 'wp-content-pilot' ),
		) );

		echo WPCP_HTML::select_input( array(
			'label'            => __( 'Sort Direction', 'wp-content-pilot' ),
			'name'             => '_envato_sort_direction',
			'options'          => array(
				'asc'  => __( 'ASC', 'wp-content-pilot' ),
				'desc' => __( 'DESC', 'wp-content-pilot' ),
			),
			'desc'             => __( 'Select sort direction for the result set', 'wp-content-pilot' ),
		) );

	}

	/**
	 * update campaign settings
	 *
	 * @param $post_id
	 * @param $posted
	 *
	 * @since 1.0.0
	 *
	 */
	public function update_campaign_settings( $post_id, $posted ) {
		$price_range        = empty( $posted['_price_range'] ) ? '' : sanitize_text_field( $posted['_price_range'] );
		$price_range_ranges = wpcp_string_to_array( $price_range, '|', array( 'trim', 'intval' ) );
		$price_range_ranges = empty( $price_range_ranges ) ? '' : implode( '|', $price_range_ranges );
		update_post_meta( $post_id, '_platform', empty( $posted['_platform'] ) ? 'no' : sanitize_text_field( $posted['_platform'] ) );
		update_post_meta( $post_id, '_price_range', $price_range_ranges );
		update_post_meta( $post_id, '_envato_sort_by', empty( $posted['_envato_sort_by'] ) ? 'no' : sanitize_text_field( $posted['_envato_sort_by'] ) );
		update_post_meta( $post_id, '_envato_sort_direction', empty( $posted['_envato_sort_direction'] ) ? 'no' : sanitize_text_field( $posted['_envato_sort_direction'] ) );
	}

	/**
	 * Hook in background process and prepare contents
	 *
	 * @param $link
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function prepare_contents( $link ) {

		if ( 'envato' != $link->camp_type ) {
			return false;
		}

		$raw = maybe_unserialize( $link->raw_content );

		$envato_impact_radius = wpcp_get_settings( 'envato_impact_radius', 'wpcp_settings_envato', '' );

		$affiliate_url = add_query_arg( array(
			'u' => urlencode( $link->url )
		), $envato_impact_radius );

		$article = array(
			'site'               => sanitize_text_field( @$raw->site ),
			'classification'     => sanitize_text_field( @$raw->classification ),
			'classification_url' => sanitize_text_field( @$raw->classification ),
			'price'              => wpcp_cent_to_usd( @$raw->price_cents ),
			'number_of_sales'    => intval( @$raw->number_of_sales ),
			'author_username'    => sanitize_key( @$raw->author_username ),
			'author_url'         => esc_url( @$raw->author_url ),
			'author_image'       => esc_url( @$raw->author_image ),
			'summary'            => esc_html( @$raw->summary ),
			'tags'               => sanitize_text_field( implode( ',', @$raw->tags ) ),
			'description_html'   => wp_kses_post( @$raw->description_html ),
			'affiliate_url'      => esc_url( $affiliate_url ),
		);


		wpcp_update_link( $link->id, array(
			'content'     => empty( $raw->description_html ) ? $raw->description : trim( $raw->description_html ),
			'raw_content' => serialize( $article ),
			'score'       => wpcp_get_read_ability_score( isset( $raw->description_html ) ? $raw->description_html : $link->content ),
			'status'      => 'ready',
		) );

	}

	/**
	 * Replace additional template tags
	 *
	 * @param $content
	 * @param $article
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function replace_template_tags( $content, $article ) {

		if ( 'envato' !== $article['campaign_type'] ) {
			return $content;
		}

		$link        = wpcp_get_link( $article['link_id'] );
		$raw_content = maybe_unserialize( $link->raw_content );

		foreach ( $raw_content as $tag => $tag_content ) {
			$content = str_replace( '{' . $tag . '}', $tag_content, $content );
		}

		return $content;
	}

	/**
	 * Check if everything ok or not
	 *
	 * @return bool|\WP_Error
	 * @since 1.0.0
	 */
	public function setup() {
		$token                = wpcp_get_settings( 'token', 'wpcp_settings_envato', '' );
		$envato_impact_radius = wpcp_get_settings( 'envato_impact_radius', 'wpcp_settings_envato', '' );
		if ( empty( $token ) ) {
			$msg = __( 'Envato API is not set. Please configure Envato API.', 'wp-content-pilot' );
			wpcp_log( $msg );

			return new \WP_Error( 'invalid-api-settings', $msg );
		}

		$this->token = $token;

		$this->envato_impact_radius = empty( $envato_impact_radius ) ? '' : $envato_impact_radius;

		return true;
	}

	/**
	 *
	 *
	 * @return array|\WP_Error
	 * @since 1.0.0
	 */
	public function discover_links() {
		$page           = $this->get_page_number( '1' );
		$site           = wpcp_get_post_meta( $this->campaign_id, '_platform', null );
		$sort_by        = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_by', 'relevance' );
		$sort_direction = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_direction', 'asc' );
		$price_range    = wpcp_get_post_meta( $this->campaign_id, '_price_range', '' );

		$price_range = explode( '|', $price_range );
		$min_price   = ! empty( $price_range[0] ) ? trim( $price_range[0] ) : 0;
		$max_price   = ! empty( $price_range[1] ) ? trim( $price_range[1] ) : 0;
		$page_size   = wpcp_perpage_data_fetch_limit( $this->campaign_id );
		$query_args  = [
			'site'           => $site,
			'term'           => $this->keyword,
			'category'       => '',
			'page'           => $page,
			'page_size'      => $page_size,
			'sort_by'        => $sort_by,
			'sort_direction' => $sort_direction,
		];

		if ( ! empty( $min_price ) ) {
			$query_args['price_min'] = $min_price;
		}
		if ( ! empty( $max_price ) ) {
			$query_args['price_max'] = $max_price;
		}


		$headers = array( 'Authorization' => 'bearer ' . trim( $this->token ) );
		$request = wpcp_remote_get( 'https://api.envato.com/v1/discovery/search/search/item', $query_args, array(), $headers );

		$response = wpcp_retrieve_body( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$items = $response->matches;
		$links = [];
		foreach ( $items as $item ) {
			$image  = '';
			$images = $item->previews;
			if ( ! empty( $images ) && ! empty( $images->landscape_preview ) ) {
				$images = $images->landscape_preview;
			}

			if ( ! empty( $images->landscape_url ) ) {
				$image = $images->landscape_url;
			}

			$links[] = array(
				'title'       => $item->name,
				'content'     => $item->description_html,
				'url'         => $item->url,
				'image'       => $image,
				'raw_content' => serialize( $item ),
				'score'       => '0',
				'gmt_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $item->published_at ) ),
				'status'      => 'fetched',
			);
		}

		$this->set_page_number( intval( $page ) + 1 );

		return $links;
	}

	/**
	 * Fetch post finally
	 *
	 * @param $link
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_post( $link ) {
		$article = array(
			'title'         => $link->title,
			'content'       => $link->content,
			'image_url'     => $link->image,
			'source_url'    => $link->url,
			'date'          => $link->gmt_date ? get_date_from_gmt( $link->gmt_date ) : current_time( 'mysql' ),
			'score'         => $link->score,
			'campaign_id'   => $link->camp_id,
			'campaign_type' => $link->camp_type,
			'link_id'       => $link->id
		);

		return $article;
	}
}
