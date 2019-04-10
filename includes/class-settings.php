<?php

class WPCP_Settings {
	private $settings_api;

	function __construct() {
		$this->settings_api = new Ever_WP_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		//initialize settings
		$this->settings_api->admin_init();
	}

	function admin_menu() {
		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Settings', 'wp-content-pilot' ), __( 'Settings', 'wp-content-pilot' ), 'manage_options', 'wpcp-settings', array(
			$this,
			'settings_page'
		) );
	}

	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'wpcp_settings_article',
				'title' => __( 'Article Settings', 'wp-content-pilot' )
			),
			array(
				'id'    => 'wpcp_settings_youtube',
				'title' => __( 'Youtube Settings', 'wp-content-pilot' )
			),
			array(
				'id'    => 'wpcp_settings_flickr',
				'title' => __( 'Flickr Settings', 'wp-content-pilot' )
			),
			array(
				'id'    => 'wpcp_settings_envato',
				'title' => __( 'Envato Settings', 'wp-content-pilot' )
			)
		);

		return apply_filters( 'wpcp_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	function get_settings_fields() {
		$settings_fields = array(
			'wpcp_settings_article' => array(
				array(
					'name'        => 'banned_hosts',
					'label'       => __( 'Banned Hosts', 'wp-content-pilot' ),
					'desc'        => __( 'Articles from the above hosts will be rejected. put single url/host per line.', 'wp-content-pilot' ),
					'placeholder' => __( "example.com \n example1.com", 'wp-content-pilot' ),
					'type'        => 'textarea',
				),
			),
			'wpcp_settings_youtube' => array(
				array(
					'name'    => 'api_key',
					'label'   => __( 'Youtube API Key', 'wp-content-pilot' ),
					'desc'    => __( 'Youtube campaigns wont run without settings this.', 'wp-content-pilot' ),
					'type'    => 'text',
					'default' => ''
				),
			),
			'wpcp_settings_flickr'  => array(
				array(
					'name'    => 'api_key',
					'label'   => __( 'Flickr API Key', 'wp-content-pilot' ),
					'desc'    => __( 'Flickr campaigns wont run without settings this.', 'wp-content-pilot' ),
					'type'    => 'text',
					'default' => ''
				),
			),
			'wpcp_settings_envato'  => array(
				array(
					'name'    => 'token',
					'label'   => __( 'Envato Token', 'wp-content-pilot' ),
					'desc'    => __( 'Check this tutorial <a href="https://www.pluginever.com/docs/wp-content-pilot/how-to-create-envato-token/" target="_blank">Here</a> to get your token.', 'wp-content-pilot' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'    => 'user_name',
					'label'   => __( 'Envato Username', 'wp-content-pilot' ),
					'desc'    => __( 'Your username (Affiliate ID) for affiliate integration. e.g. "pluginever"' ),
					'type'    => 'text',
					'default' => ''
				),
			)
		);

		return apply_filters( 'wpcp_settings_fields', $settings_fields );
	}

	function settings_page() {
		?>
		<?php
		echo '<div class="wrap">';
		echo sprintf( "<h2>%s</h2>", __( 'WP Content Pilot Settings', 'wp-content-pilot' ) );
		$this->settings_api->show_settings();
		echo '</div>';
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	function get_pages() {
		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}
}

new WPCP_Settings();
