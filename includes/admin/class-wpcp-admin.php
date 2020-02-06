<?php
defined( 'ABSPATH' ) || exit();

class WPCP_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var WPCP_Admin
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * @since 1.2.0
	 * WPCP_Admin constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Includes
	 * @since 1.2.0
	 */
	public function includes() {
		require_once( dirname( __FILE__ ) . '/admin-functions.php' );
		//require_once ( dirname( __FILE__ ). '/class-wpcp-upgrades.php');
		require_once( dirname( __FILE__ ) . '/class-settings-api.php' );
		require_once( dirname( __FILE__ ) . '/class-wpcp-settings.php' );
		require_once( dirname( __FILE__ ) . '/class-wpcp-settings.php' );
		require_once( dirname( __FILE__ ) . '/metabox-functions.php' );
	}

	/**
	 * Init hooks
	 * @since 1.2.0
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'get_pro_link' ), 502 );
		add_action( 'admin_init', array( $this, 'go_pro_redirect' ) );
		add_action( 'admin_init', array( $this, 'plugin_upgrades' ) );
	}

	/**
	 * @since 1.2.0
	 */
	function admin_menu() {
		$hook = 'edit.php?post_type=wp_content_pilot';
		add_submenu_page( $hook, __( 'Status', 'wp-content-pilot' ), __( 'Status', 'wp-content-pilot' ), 'manage_options', 'wpcp-status', array( $this, 'status_page' ) );
		add_submenu_page( $hook, __( 'Logs', 'wp-content-pilot' ), __( 'Logs', 'wp-content-pilot' ), 'manage_options', 'wpcp-logs', array( $this, 'logs_page' ) );
		add_submenu_page( $hook, 'Help', '<span style="color:orange;">Help</span>', 'manage_options', 'wpcp-help', array( $this, 'help_page' ) );
	}

	/**
	 * status page
	 * @since 1.2.0
	 */
	public function status_page(){
		wpcp_get_views( 'page/status-page.php');
	}

	/**
	 * Logs page
	 * @since 1.2.0
	 */
	public function logs_page(){
		wpcp_get_views( 'page/logs-page.php');
	}

	/**
	 * Help Page
	 * @since 1.2.0
	 */
	public function help_page(){
		wpcp_get_views( 'page/help-page.php');
	}

	/**
	 * @since 1.2.0
	 */
	public function get_pro_link() {
		if ( ! defined( 'WPCP_PRO_VERSION' ) ) {
			add_submenu_page(
				'edit.php?post_type=wp_content_pilot',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wp-content-pilot' ) . '</span>',
				'manage_options',
				'go_wpcp_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	/**
	 * @since 1.2.0
	 */
	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wpcp_pro' === $_GET['page'] ) {
			wp_redirect( 'https://www.pluginever.com/plugins/wp-content-pilot-pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
	}

	/**
	 * Do plugin upgrades
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function plugin_upgrades() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
//		$upgrader = new ContentPilot_Upgrades();
//
//		if ( $upgrader->needs_update() ) {
//			$upgrader->perform_updates();
//		}
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
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

WPCP_Admin::instance();
