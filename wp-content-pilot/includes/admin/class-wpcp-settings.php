<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class WPCP_Settings
 */
class WPCP_Settings {
	/**
	 * @var Ever_Settings_Framework
	 */
	private $settings_api;

	/**
	 * @since 1.2.0
	 * WPCP_Settings constructor.
	 */
	function __construct() {
		$this->settings_api = new Ever_Settings_Framework();
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
		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Settings', 'wp-content-pilot' ), __( 'Settings', 'wp-content-pilot' ), 'edit_others_posts', 'wpcp-settings', array(
			$this,
			'settings_page'
		) );
	}

	/**
	 * @return mixed|void
	 * @since 1.2.0
	 */
	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'wpcp_settings_misc',
				'title' => __( 'Misc Settings', 'wp-content-pilot' )
			),
			array(
				'id'    => 'wpcp_article_spinner',
				'title' => __( 'Article Spinner', 'wp-content-pilot' )
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
			'wpcp_settings_misc'   => array(
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
			'wpcp_article_spinner' => array(
				array(
					'name'    => 'spinrewriter_head',
					'label'   => __( 'SpinreWriter', 'wp-content-pilot' ),
					'desc'    => sprintf( __( 'SpinreWriter is one of the best Article Spinner, if you do not have account please %ssign up%s.', 'wp-content-pilot' ), '<a href="https://bit.ly/spinrewriterpluginever" target="_blank">', '</a>' ),
					'type'    => 'html',
					'default' => ''
				),
				array(
					'name'    => 'spinrewriter_email',
					'label'   => __( 'Email', 'wp-content-pilot' ),
					'desc'    => __( 'Input your email address of SpinreWriter.', 'wp-content-pilot' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'    => 'spinrewriter_api_key',
					'label'   => __( 'API Key', 'wp-content-pilot' ),
					'desc'    => __( 'Input API key of SpinreWriter.', 'wp-content-pilot' ),
					'type'    => 'text',
					'default' => ''
				),
			),
		);

		return apply_filters( 'wpcp_settings_fields', $settings_fields );
	}

	function settings_page() {
		?>
		<div class="wrap">
			<?php echo sprintf( "<h2>%s</h2>", __( 'WP Content Pilot Settings', 'wp-content-pilot' ) ); ?>
			<div id="poststuff">
				<div id="post-body" class="columns-2">
					<div id="post-body-content">
						<?php $this->settings_api->show_settings(); ?>
					</div>
					<div id="postbox-container-1" class="postbox-container" style="margin-top: 15px;">
						<?php if ( ! defined( 'WPCP_PRO_VERSION' ) ): ?>
							<div class="postbox" style="min-width: inherit;">
								<h3 class="hndle"><label for="title"><?php _e( 'Upgrade to PRO', 'wp-content-pilot' ); ?></label></h3>
								<div class="inside">
									<?php
									echo sprintf( __( 'Pro version support 15+ campaign sources with exclusive features, %supgrade to pro now%s.', 'wp-content-pilot' ), '<a href="https://pluginever.com/plugins/wp-content-pilot-pro/" target="_blank">', '</a>' )
									?>
								</div>
							</div>
						<?php endif; ?>

						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'Documentation', 'wp-content-pilot' ); ?></label></h3>
							<div class="inside">
								<?php
								echo sprintf( __( 'Please visit the %s WP Content Pilot %s  plugin\'s documentation page to learn how to use this plugin', 'wp-content-pilot' ), '<a href="https://pluginever.com/docs/wp-content-pilot/" target="_blank">', '</a>' )
								?>
							</div>
						</div>

						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'Support', 'wp-content-pilot' ); ?></label></h3>
							<div class="inside">
								<?php
								echo sprintf( __( 'Having issues or difficulties? You can post your issue on the %s Support Forum.%s', 'wp-content-pilot' ), '<a href="https://pluginever.com/support/" target="_blank">', '</a>' )
								?>

							</div>
						</div>

						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title">Rate Us</label></h3>
							<div class="inside">
								<?php
								echo sprintf( __( 'Like the plugin? Please give us a  %s rating.%s', 'wp-content-pilot' ), '<a href="https://wordpress.org/support/plugin/wp-content-pilot/reviews/#new-post" target="_blank">', '</a>' )
								?>
								<div class="ratings-stars-container">
									<a href="https://wordpress.org/support/plugin/wp-content-pilot/reviews/?filter=5"
									   target="_blank"><span class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span>
									</a>
								</div>
							</div>
						</div>

					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
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
