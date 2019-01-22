<?php
/**
 * Plugin Name: WP Content Pilot
 * Plugin URI:  https://www.pluginever.com
 * Description: The Best WordPress Plugin ever made!
 * Version:     1.2.0
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wp-content-pilot
 * Domain Path: /i18n/languages/
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Main initiation class
 *
 * @since 1.0.0
 */

/**
 * Main ContentPilot Class.
 *
 * @class ContentPilot
 */
final class ContentPilot {
	/**
	 * ContentPilot version.
	 *
	 * @var string
	 */
	public $version = '1.2.0';

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.6.0';

	/**
	 * The single instance of the class.
	 *
	 * @var ContentPilot
	 * @since 1.0.0
	 */
	protected static $instance = null;


	/**
	 * @since 1.2.0
	 *
	 * @var \WPCP_Module
	 */
	public $modules;


	/**
	 * @since 1.0.0
	 *
	 * @var \WPCP_Elements
	 */
	public $elements;

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Main ContentPilot Instance.
	 *
	 * Ensures only one instance of ContentPilot is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return ContentPilot - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wp-content-pilot' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wp-content-pilot' ), '2.1' );
	}

	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * EverProjects Constructor.
	 */
	public function setup() {
		$this->check_environment();
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->boot();
		do_action( 'content_pilot_loaded' );
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
	public function check_environment() {
		if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die( "Unsupported PHP version Min required PHP Version:{$this->min_php}" );
		}
	}

	/**
	 * Define EverProjects Constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_constants() {
		//$upload_dir = wp_upload_dir( null, false );
		define( 'WPCP_VERSION', $this->version );
		define( 'WPCP_FILE', __FILE__ );
		define( 'WPCP_PATH', dirname( WPCP_FILE ) );
		define( 'WPCP_INCLUDES', WPCP_PATH . '/includes' );
		define( 'WPCP_MODULES', WPCP_PATH . '/modules' );
		define( 'WPCP_URL', plugins_url( '', WPCP_FILE ) );
		define( 'WPCP_VIEWS', WPCP_PATH . '/views' );
		define( 'WPCP_ASSETS_URL', WPCP_URL . '/assets' );
		define( 'WPCP_TEMPLATES_DIR', WPCP_PATH . '/templates' );
	}


	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		//core includes
		require_once WPCP_PATH . '/vendor/autoload.php';

		//background processing
		require_once WPCP_INCLUDES . '/wp-async-request.php';
		require_once WPCP_INCLUDES . '/wp-background-process.php';
		require_once WPCP_INCLUDES . '/class-automatic-campaign.php';
		require_once WPCP_INCLUDES . '/class-fetch-contents.php';

		require_once WPCP_INCLUDES . '/core-functions.php';
		require_once WPCP_INCLUDES . '/formatting-functions.php';
		require_once WPCP_INCLUDES . '/action-functions.php';
		require_once WPCP_INCLUDES . '/class-install.php';
		require_once WPCP_INCLUDES . '/post-types.php';
		require_once WPCP_INCLUDES . '/class-elements.php';

		require_once WPCP_INCLUDES . '/class-ajax.php';
		require_once WPCP_INCLUDES . '/class-campaign.php';
		require_once WPCP_INCLUDES . '/class-module.php';
		require_once WPCP_MODULES . '/class-feed.php';
		require_once WPCP_MODULES . '/class-article.php';
		require_once WPCP_MODULES . '/class-envato.php';
		require_once WPCP_MODULES . '/class-youtube.php';
		require_once WPCP_MODULES . '/class-flicker.php';


		//
		require_once WPCP_INCLUDES . '/script-functions.php';

		//admin includes
		if ( $this->is_request( 'admin' ) ) {
			require_once WPCP_INCLUDES . '/metabox-functions.php';
			require_once WPCP_INCLUDES . '/class-settings-api.php';
			require_once WPCP_INCLUDES . '/class-settings.php';
		}

		//frontend includes
		if ( $this->is_request( 'frontend' ) ) {
			require_once WPCP_INCLUDES . '/class-frontend.php';
		}

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {
		// Localize our plugin
		add_action( 'init', array( $this, 'localization_setup' ) );

		//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ) );

		add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wp-content-pilot', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		//$links[] = '<a href="' . admin_url( 'admin.php?page=' ) . '">' . __( 'Settings', '' ) . '</a>';
		return $links;
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute' )
		);

		return $schedules;
	}

	public function add_notice( $message, $type = 'success' ) {

		if ( is_string( $message ) &&   is_string( $type ) ) {
			$notices = get_option( 'wpcp_admin_notifications', [] );

			$notices[] = array(
				'message' => $message,
				'type'    => $type
			);

			update_option( 'wpcp_admin_notifications', $notices );
		}
	}

	/**
	 * Show admin notifications
	 *
	 * @since 1.1.0
	 */
	public function show_admin_notice() {
		$notices = get_option( 'wpcp_admin_notifications', [] );
		if ( empty( $notices ) || ! is_array( $notices ) ) {
			return;
		}
		foreach ( $notices as $notice ) {
			?>
			<div class="notice notice-<?php echo sanitize_html_class( $notice['type'] ); ?> is-dismissible">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}

		delete_option( 'wpcp_admin_notifications' );
	}

	/**
	 * Boot the plugin
	 *
	 * @since 1.0.0
	 */
	public function boot() {
		new WPCP_Install();
		new WPCP_Ajax();
		new WPCP_Automatic_Campaign();
		new WPCP_Fetch_Contents();
		new WPCP_Feed();
		new WPCP_Article();
		new WPCP_Envato();
		new WPCP_Youtube();
		new WPCP_Flicker();

		$this->elements = new WPCP_Elements();
		$this->modules  = new WPCP_Module();

	}


}

function content_pilot() {
	return ContentPilot::instance();
}

//fire off the plugin
content_pilot();
