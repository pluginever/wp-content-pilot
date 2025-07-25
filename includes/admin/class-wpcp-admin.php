<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class WPCP_Admin
 *
 * @since 1.0.0
 * @package WPContentPilot
 */
class WPCP_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var WPCP_Admin
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * WPCP_Admin constructor.
	 *
	 * @since 1.2.0
	 * WPCP_Admin constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Includes.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/admin-functions.php';
		require_once __DIR__ . '/class-wpcp-updater.php';
		require_once __DIR__ . '/class-settings-framework.php';
		require_once __DIR__ . '/class-wpcp-settings.php';
		require_once __DIR__ . '/metabox-functions.php';
	}

	/**
	 * Init hooks.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'get_pro_link' ), 502 );
		add_action( 'admin_init', array( $this, 'go_pro_redirect' ) );
		add_action( 'admin_init', array( $this, 'plugin_upgrades' ) );
		add_action( 'admin_footer_text', array( $this, 'admin_footer_note' ) );
	}

	/**
	 * Admin menu.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function admin_menu() {
		$hook = 'edit.php?post_type=wp_content_pilot';
		add_submenu_page( $hook, __( 'Status', 'wp-content-pilot' ), __( 'Status', 'wp-content-pilot' ), 'edit_others_posts', 'wpcp-status', array( $this, 'status_page' ) );
		add_submenu_page( $hook, __( 'Logs', 'wp-content-pilot' ), __( 'Logs', 'wp-content-pilot' ), 'edit_others_posts', 'wpcp-logs', array( $this, 'logs_page' ) );
	}

	/**
	 * Status page.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function status_page() {
		wpcp_get_views( 'page/status-page.php' );
	}

	/**
	 * Logs page.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function logs_page() {
		wpcp_get_views( 'page/logs-page.php' );
	}

	/**
	 * Get pro link.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function get_pro_link() {
		if ( ! defined( 'WPCP_PRO_VERSION' ) ) {
			add_submenu_page(
				'edit.php?post_type=wp_content_pilot',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wp-content-pilot' ) . '</span>',
				'edit_others_posts',
				'go_wpcp_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	/**
	 * Redirect to pro page.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function go_pro_redirect() {
		wp_verify_nonce( '_nonce' );

		if ( isset( $_GET['page'] ) && 'go_wpcp_pro' === $_GET['page'] ) {
			wp_redirect( 'https://wpcontentpilot.com/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
	}

	/**
	 * Do plugin upgrades.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugin_upgrades() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$upgrader = new WPCP_Updater();
		if ( $upgrader->needs_update() ) {
			$upgrader->perform_updates();
		}
	}

	/**
	 * 5-Star Rating banner.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function admin_footer_note() {
		$screen = get_current_screen();

		if ( 'wp_content_pilot' === $screen->post_type ) {
			$star_url = 'https://wordpress.org/support/plugin/wp-content-pilot/reviews/?filter=5#new-post';
			$text     = sprintf( /* translators: %s: 5 star rating url */ __( 'If you like <strong>WP Content Pilot</strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Your Review is very important to us as it helps us to grow more.', 'wp-content-pilot' ), $star_url );
			return $text;
		}

		return '';
	}

	/**
	 * Main WPCP_Admin Instance.
	 *
	 * Ensures only one instance of WPCP_Admin is loaded or can be loaded.
	 *
	 * @return WPCP_Admin - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

WPCP_Admin::instance();
