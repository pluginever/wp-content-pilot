<?php
namespace WPContentPilot;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Campaign class.
 *
 * @since 3.0.0
 * @package WPContentPilot
 *
 * @property-read int    $id The campaign ID.
 * @property-read string $type The campaign type.
 */
class Campaign {
	/**
	 * The campaign's id.
	 *
	 * @since 3.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Campaign's data container.
	 *
	 * @since 3.0.0
	 * @var \stdClass
	 */
	protected $data;

	/**
	 * Get the instance of the campaign.
	 *
	 * @param int $id The campaign ID.
	 *
	 * @since 3.0.0
	 * @return Campaign|null The campaign object or null if not found.
	 */
	public static function get_instance( $id ) {
		$post = get_post( $id );

		if ( ! $post || 'wp_content_pilot' !== $post->post_type ) {
			return null;
		}

		return new self( $post );
	}

	/**
	 * Campaign constructor.
	 *
	 * @param \WP_Post $post The campaign post object.
	 */
	public function __construct( $post ) {
		$this->id   = $post->ID;
		$this->data = $post;
	}

	/**
	 * Magic method to check if the campaign data is set.
	 *
	 * @param string $key The data key.
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function __isset( $key ) {
		if ( isset( $this->data->{$key} ) ) {
			return true;
		}

		return metadata_exists( 'post', $this->id, $key );
	}

	/**
	 * Magic method to get the campaign data.
	 *
	 * @param string $key The data key.
	 *
	 * @since 3.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( isset( $this->data->{$key} ) ) {
			$value = $this->data->{$key};
		} else {
			$value = get_post_meta( $this->id, $key, true );
		}

		if ( 'type' === $key && empty( $value ) ) {
			$value = 'article';
		}

		return $value;
	}

	/**
	 * Magic method to set the campaign data.
	 *
	 * @param string $key The data key.
	 * @param mixed  $value The data value.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function __set( $key, $value ) {
		if ( property_exists( $this, $key ) ) {
			$this->{$key} = $value;
		} else {
			$this->data->{$key} = $value;
		}
	}

	/**
	 * Update meta.
	 *
	 * @param string $key The meta key.
	 * @param mixed  $value The meta value.
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		return update_post_meta( $this->id, $key, $value );
	}

	/**
	 * Get the campaign's option tabs.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_option_tabs() {
		$tabs = array(
			'general'        => array(
				'label'    => __( 'General', 'wp-content-pilot' ),
				'priority' => 10,
			),
			'content'        => array(
				'label'    => __( 'Content', 'wp-content-pilot' ),
				'priority' => 20,
			),
			'authors'        => array(
				'label'    => __( 'Authors', 'wp-content-pilot' ),
				'priority' => 30,
			),
			'images'         => array(
				'label'    => __( 'Images', 'wp-content-pilot' ),
				'priority' => 40,
			),
			'search_replace' => array(
				'label'    => __( 'Search & Replace', 'wp-content-pilot' ),
				'priority' => 55,
			),
			'links'          => array(
				'label'    => __( 'Links', 'wp-content-pilot' ),
				'priority' => 60,
			),
			'misc'           => array(
				'label'    => __( 'Miscellaneous', 'wp-content-pilot' ),
				'priority' => PHP_INT_MAX,
			),
		);

		/**
		 * Filter the campaign option tabs.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tabs The tabs.
		 */
		$tabs = apply_filters( 'wpcp_' . $this->type . '_campaign_option_tabs', $tabs );

		/**
		 * Filter the campaign option tabs.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tabs The tabs.
		 */
		$tabs = apply_filters( 'wpcp_campaign_option_tabs', $tabs );

	}

	/**
	 * Get the campaign's options.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_options() {}

	/**
	 * Save the campaign's options.
	 *
	 * @param array $options The options to save.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function save_options( $options ) {}
}
