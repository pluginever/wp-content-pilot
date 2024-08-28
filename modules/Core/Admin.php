<?php

namespace WPCP\Modules\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @since 3.0.0
 * @package ContentPilot\Core
 */
class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes_wp_content_pilot', array( $this, 'register_metaboxes' ), 20 );
		add_action( 'wpcp_campaign_general_settings_panel', array( $this, 'render_general_settings_panel' ) );
	}

	/**
	 * Register metaboxes.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function register_metaboxes() {
		// remove all metaboxes from campaign post type.
		remove_meta_box( 'wpcp-campaign-status', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-campaign-options', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-post-template', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-post-settings', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-post-filters', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-advanced-settings', 'wp_content_pilot', 'normal' );
		remove_meta_box( 'wpcp-spinner-settings', 'wp_content_pilot', 'normal' );
		add_meta_box( 'wpcp-campaign-settings', __( 'Campaign Settings', 'content-pilot' ), array( $this, 'render_campaign_settings' ), 'wp_content_pilot', 'normal', 'high' );
	}

	/**
	 * Render campaign settings.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function render_campaign_settings() {
		wp_nonce_field( 'campaign_settings' );
		include __DIR__ . '/views/campaign-settings.php';
	}

	/**
	 * Render general settings panel.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function render_general_settings_panel() {
		?>
		Lorem ipsum dolor sit amet, consectetur adipisicing elit. Error, nisi.
		<?php
	}

}
