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
	 * @return string
	 * @since 1.2.0
	 */
	public function get_default_template() {
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
				'graphicrever.net' => 'GraphicsRever',
				'3docean.net'      => '3DOcean',
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
	 * @return mixed|void
	 */
	public function get_post( $campaign_id, $keywords ) {
		$token                = wpcp_get_settings( 'token', 'wpcp_settings_envato', '' );
		$envato_impact_radius = wpcp_get_settings( 'envato_impact_radius', 'wpcp_settings_envato', '' );

		if ( empty( $api_key ) ) {
			$notice = __( 'The Envato api key is not set so the campaign won\'t run, disabling campaign.', 'wp-content-pilot-pro' );

			wpcp_logger()->error( $notice, $campaign_id );
			wpcp_disable_campaign( $campaign_id );

			return new WP_Error( 'missing-data', $notice );
		}

		$last_keyword = $this->get_last_keyword( $campaign_id );

		foreach ( $keywords as $keyword ) {
			wpcp_logger()->debug( sprintf( 'Looping through keywords [ %s ]', $keyword ), $campaign_id );
			//if more than 1 then unset last one
			if ( count( $keywords ) > 1 && $last_keyword == $keyword ) {
				wpcp_logger()->debug( sprintf( 'Keywords more than 1 and [ %s ] this keywords used last time so skipping it ', $keyword ), $campaign_id );
				continue;
			}


			$total_page_key = $this->get_unique_key( "$keyword-total-page" );
			$page_key       = $this->get_unique_key( $keyword );
			$total_page     = wpcp_get_post_meta( $campaign_id, $total_page_key, '' );
			$page_number    = wpcp_get_post_meta( $campaign_id, $page_key, 1 );
			$site           = wpcp_get_post_meta( $this->campaign_id, '_platform', null );
			$sort_by        = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_by', 'relevance' );
			$sort_direction = wpcp_get_post_meta( $this->campaign_id, '_envato_sort_direction', 'asc' );
			$price_range    = wpcp_get_post_meta( $this->campaign_id, '_price_range', '' );

			$price_range = explode( '|', $price_range );
			$min_price   = ! empty( $price_range[0] ) ? trim( $price_range[0] ) : 0;
			$max_price   = ! empty( $price_range[1] ) ? trim( $price_range[1] ) : 0;




		}
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
