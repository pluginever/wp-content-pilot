<?php
/**
 * Plugin Name: WP Content Pilot
 * Plugin URI:  https://www.pluginever.com
 * Description: WP Content Pilot automatically posts contents from various sources based on the predefined keywords.
 * Version:     1.3.3
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
defined( 'ABSPATH' ) || exit();

/**
 * Main ContentPilot Class.
 *
 * @class ContentPilot
 */
final class ContentPilot {
	/**
	 * ContentPilot_Pro version.
	 *
	 * @var string
	 */
	protected $version = '1.3.3';

	/**
	 * The single instance of the class.
	 *
	 * @var ContentPilot
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main ContentPilot Instance.
	 *
	 * Ensures only one instance of ContentPilot is loaded or can be loaded.
	 *
	 * @return ContentPilot - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wp-content-pilot' ), '1.0.0' );
	}

	/**
	 * Universalizing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Universalizing instances of this class is forbidden.', 'wp-content-pilot' ), '1.0.0' );
	}


	/**
	 * ContentPilot constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->define_tables();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define ContentPilot Constants.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function define_constants() {
		define( 'WPCP_VERSION', $this->version );
		define( 'WPCP_FILE', __FILE__ );
		define( 'WPCP_PATH', dirname( WPCP_FILE ) );
		define( 'WPCP_INCLUDES', WPCP_PATH . '/includes' );
		define( 'WPCP_MODULES', WPCP_PATH . '/modules' );
		define( 'WPCP_LIBRARY', WPCP_INCLUDES . '/library' );
		define( 'WPCP_URL', plugins_url( '', WPCP_FILE ) );
		define( 'WPCP_VIEWS', WPCP_PATH . '/views' );
		define( 'WPCP_ASSETS_URL', WPCP_URL . '/assets' );
		define( 'WPCP_TEMPLATES_DIR', WPCP_PATH . '/templates' );
	}

	/**
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() {
		global $wpdb;
		$tables = array(
			'wpcp_links',
			'wpcp_logs',
			'wpcp_items',
		);
		foreach ( $tables as $table ) {
			$wpdb->$table   = $wpdb->prefix . $table;
			$wpdb->tables[] = $table;
		}
	}

	/**
	 * Include all required files
	 *
	 * since 1.0.0
	 *
	 * @return void
	 */
	public function includes() {
		//boot
		require_once( WPCP_INCLUDES . '/class-wpcp-install.php' );
		require_once( WPCP_PATH . '/vendor/autoload.php' );

		//functions
		require_once WPCP_INCLUDES . '/core-functions.php';
		require_once WPCP_INCLUDES . '/action-functions.php';
		require_once( WPCP_INCLUDES . '/formatting-functions.php' );
		require_once WPCP_INCLUDES . '/post-types.php';
		require_once WPCP_INCLUDES . '/script-functions.php';
		require_once WPCP_INCLUDES . '/class-wpcp-html.php';

		//core files
		//require_once( WPCP_LIBRARY . '/readability/Readability_OLD.php' );
		require_once( WPCP_LIBRARY . '/readability/Readability.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-readability.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-logger.php' );
		require_once( WPCP_INCLUDES . '/admin/class-wpcp-insight.php' );
		require_once( WPCP_INCLUDES . '/admin/class-wpcp-tracker.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-dom.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-modules.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-module.php' );
		require_once( WPCP_INCLUDES . '/class-wpcp-notices.php' );

		//modules
		require_once( WPCP_INCLUDES . '/modules/class-wpcp-article.php' );
		require_once( WPCP_INCLUDES . '/modules/class-wpcp-feed.php' );
		require_once( WPCP_INCLUDES . '/modules/class-wpcp-youtube.php' );
		require_once( WPCP_INCLUDES . '/modules/class-wpcp-envato.php' );
		require_once( WPCP_INCLUDES . '/modules/class-wpcp-flickr.php' );

		if ( is_admin() ) {
			require_once( WPCP_INCLUDES . '/admin/class-wpcp-admin.php' );
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'WPCP_Install', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'WPCP_Install', 'deactivate' ) );
		register_activation_hook( __FILE__, array( $this, 'activate_cron' ) );
		register_shutdown_function( array( $this, 'log_errors' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );

		add_action( 'init', array( $this, 'localization_setup' ) );
		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ), 20 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'check_if_cron_running' ) );
	}

	/**
	 * When WP has loaded all plugins, trigger the `content__pilot__loaded` hook.
	 *
	 * This ensures `content__pilot__loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 *
	 * @since 1.2.5
	 */
	public function on_plugins_loaded() {
		do_action( 'content__pilot__loaded' );
	}


	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wp-content-pilot', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
	}

	/**
	 * Create cron job
	 *
	 * since 1.0.7
	 *
	 * @return void
	 */
	public function activate_cron() {
		wp_schedule_event( time(), 'once_a_minute', 'wpcp_per_minute_scheduled_events' );
		wp_schedule_event( time(), 'daily', 'wpcp_daily_scheduled_events' );
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-settings' ) . '">' . __( 'Settings', 'wp-content-pilot' ) . '</a>',
		);

		$links = array_merge( $action_links, $links );

		if ( ! defined( 'WPCP_PRO_VERSION' ) ) {
			$upgrade_link = 'https://www.pluginever.com/plugins/wp-content-pilot-pro/?utm_source=plugin_action_link&utm_medium=link&utm_campaign=wp-content-pilot-pro&utm_content=Upgrade%20to%20Pro';
			$upgrade_links = array(
				'upgrade' => '<a href="'. esc_url( $upgrade_link ).'" style="color: red;font-weight: bold;" target="_blank">' . __( 'Go Pro', 'wp-content-pilot' ) . '</a>'
			);

			$links = array_merge( $links, $upgrade_links );
		}

		return $links;
	}

	/**
	 * Add plugin docs links in plugin row links
	 *
	 * @param mixed $links Links
	 * @param mixed $file File
	 *
	 * @return array
	 * @since 1.2.8
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {

			$row_meta = array(
				'docs' => '<a href="' . esc_url( apply_filters( 'wpcp_docs_url', 'https://pluginever.com/docs/wp-content-pilot/' ) ) . '" aria-label="' . esc_attr__( 'View documentation', 'wp-content-pilot' ) . '">' . esc_html__( 'Docs', 'wp-content-pilot' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

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
			'display'  => __( 'Once a Minute', 'wp-content-pilot' )
		);

		return $schedules;
	}

	/**
	 * @return void
	 */
	public function check_if_cron_running() {
		if ( current_user_can( 'manage_options' ) ) {
//			$status = wpcp_check_cron_status();
//			if ( is_wp_error( $status ) ) {
//			$this->add_admin_notice( 'db-cron-error', 'notice-error', sprintf( __( 'There was a problem spawning a call to the WP-Cron system on your site. This means WP Content Pilot on your site may not work. The problem was: %s', 'wp-content-pilot' ), '<strong>' . esc_html( $status->get_error_message() ) . '</strong>' ) );
//			}
		}
	}

	/**
	 * Log fatal error
	 *
	 * @since 1.2.4
	 */
	public function log_errors() {
		$error = error_get_last();
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			wpcp_logger()->error( sprintf( __( '%1$s in %2$s on line %3$s', 'wp-content-pilot' ), $error['message'], $error['file'], $error['line'] ) . PHP_EOL );
			do_action( 'wpcp_shutdown_error', $error );
		}
	}

	/**
	 * @return string
	 * @since 1.2.3
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Module class
	 * @return WPCP_Modules
	 * @since 1.2.0
	 *
	 */
	public function modules() {
		return WPCP_Modules::instance();
	}
}

/**
 * @return ContentPilot
 */
function content_pilot() {
	return ContentPilot::instance();
}

//fire off the plugin
content_pilot();
