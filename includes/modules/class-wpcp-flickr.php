<?php
// don't call the file directly
defined( 'ABSPATH' ) || exit();

class WPCP_Flickr extends WPCP_Module {
	/**
	 * The single instance of the class
	 *
	 * @var $this;
	 */
	protected static $_instance = null;

	/**
	 * WPCP_Module constructor.
	 */
	public function __construct() {
		add_filter( 'wpcp_modules', array( $this, 'register_module' ) );
		add_action( 'wpcp_flickr_campaign_options_meta_fields', 'wpcp_keyword_suggestion_field' );
		add_action( 'wpcp_flickr_campaign_options_meta_fields', 'wpcp_keyword_field' );

		add_action( 'wpcp_campaign_flickr_options_meta_fields', array( $this, 'add_campaign_option_fields' ) );
		add_action( 'wpcp_update_campaign_settings_youtube', array( $this, 'save_campaign_meta' ), 10, 2 );
	}


	/**
	 * @return string
	 * @since 1.2.0
	 */
	public function get_campaign_type() {
		return 'flickr';
	}

	/**
	 * @param $modules
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function register_module( $modules ) {
		$modules['flickr'] = __CLASS__;

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
			'content'    => __( 'Content', 'wp-content-pilot' ),
			'date'       => __( 'Published date', 'wp-content-pilot' ),
			'image_url'  => __( 'Main image url', 'wp-content-pilot' ),
			'source_url' => __( 'Source link', 'wp-content-pilot' ),
			'author'     => __( 'Author Name', 'wp-content-pilot' ),
			'author_url' => __( 'Author Url', 'wp-content-pilot' ),
			'tags'       => __( 'Photo Tags', 'wp-content-pilot' ),
			'views'      => __( 'Photo Views', 'wp-content-pilot' ),
			'user_id'    => __( 'User Id', 'wp-content-pilot' ),
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
<a href="{image_url}">Posted</a> by <a href="http://flicker.com/{author_url}">{author}</a>
<br>
{tags}
<br>
<a href="{source_url}">Source</a>
EOT;

		return $template;
	}

	/**
	 * @param $post
	 */
	public function add_campaign_option_fields( $post ) {

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
	public function get_post( $keywords = null ) {

	}

	/**
	 * Main WPCP_Flickr Instance.
	 *
	 * Ensures only one instance of WPCP_Flickr is loaded or can be loaded.
	 *
	 * @return WPCP_Flickr Main instance
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

WPCP_Flickr::instance();
