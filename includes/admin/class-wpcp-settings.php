<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class WPCP_Settings
 */
class WPCP_Settings {
	/**
	 * @var Ever_WP_Settings_API
	 */
	private $settings_api;

	/**
	 * @since 1.2.0
	 * WPCP_Settings constructor.
	 */
	function __construct() {
		$this->settings_api = new Ever_WP_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
	}

	/**
	 * @since 1.2.0
	 */
	function admin_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		//initialize settings
		$this->settings_api->admin_init();
	}

	/**
	 * @since 1.2.0
	 */
	function admin_menu() {
		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Settings', 'wp-content-pilot' ), __( 'Settings', 'wp-content-pilot' ), 'manage_options', 'wpcp-settings', array(
			$this,
			'settings_page'
		) );
	}

	/**
	 * @since 1.2.0
	 * @return mixed|void
	 */
	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'wpcp_settings_misc',
				'title' => __( 'Misc Settings', 'wp-content-pilot' )
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
			'wpcp_settings_misc'    => array(
				array(
					'name'    => 'uninstall_on_delete',
					'label'   => __( 'Remove Data on Uninstall?', 'wp-content-pilot' ),
					'desc'    => __( 'Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'wp-content-pilot' ),
					'type'    => 'checkbox',
					'default' => ''
				),
				array(
					'name'    => 'post_publish_mail',
					'label'   => __( 'Post Publish mail', 'wp-content-pilot' ),
					'desc'    => __( 'Send mail After post publish', 'wp-content-pilot' ),
					'type'    => 'checkbox',
					'default' => ''
				),
//				array(
//					'name'    => 'skip_duplicate_url',
//					'label'   => __( 'Never post duplicate title', 'wp-content-pilot' ),
//					'desc'    => __( 'Skip post having duplicate url that are already in the database and already published posts.', 'wp-content-pilot' ),
//					'type'    => 'checkbox',
//					'default' => ''
//				),
			),
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
