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
	protected $version = '1.2.0';

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $min_wp = '4.0.0';

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $min_php = '5.6';

	/**
	 * admin notices
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $notices = array();

	/**
	 * The single instance of the class.
	 *
	 * @var ContentPilot_Pro
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
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'WP Content Pilot';

	/**
	 * ContentPilot constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );

		add_action( 'admin_init', array( $this, 'check_environment' ) );
		add_action( 'admin_init', array( $this, 'add_plugin_notices' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

		add_action( 'init', array( $this, 'localization_setup' ) );
		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ) );

		add_action( 'plugins_loaded', array( $this, 'instantiate' ) );
		//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		// if the environment check fails, initialize the plugin
		if ( $this->is_environment_compatible() ) {
			$this->init_plugin();
		}
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
	 * Universalizing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Universalizing instances of this class is forbidden.', 'wp-content-pilot' ), '1.0.0' );
	}


	/**
	 * Checks the environment on loading WordPress, just in case the environment changes after activation.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function check_environment() {

		if ( ! $this->is_environment_compatible() && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			$this->deactivate_plugin();

			$this->add_admin_notice( 'bad_environment', 'error', $this->plugin_name . ' has been deactivated. ' . $this->get_environment_message() );
		}
	}

	/**
	 * Adds notices for out-of-date WordPress and/or WP Content Pilot versions.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_notices() {

		if ( ! $this->is_wp_compatible() ) {

			$this->add_admin_notice( 'update_wordpress', 'error', sprintf(
				'%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
				'<strong>' . $this->plugin_name . '</strong>',
				$this->min_wp,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
			) );
		}
	}

	/**
	 * Determines if the server environment is compatible with this plugin.
	 *
	 * Override this method to add checks for more than just the PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_environment_compatible() {

		return version_compare( PHP_VERSION, $this->min_php, '>=' );
	}

	/**
	 * Determines if the WordPress compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_wp_compatible() {

		return version_compare( get_bloginfo( 'version' ), $this->min_wp, '>=' );
	}

	/**
	 * Determines if the required plugins are compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function plugins_compatible() {

		return $this->is_wp_compatible();
	}

	/**
	 * Deactivates the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function deactivate_plugin() {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug the notice slug
	 * @param string $class the notice class
	 * @param string $message the notice message body
	 */
	public function add_admin_notice( $slug, $class, $message ) {

		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message
		);
	}


	/**
	 * Displays any admin notices added
	 *
	 * @internal
	 *
	 * @since 2.8.0
	 */
	public function admin_notices() {
		$notices = (array) array_merge($this->notices, get_option( 'wpcp_admin_notifications', [] ));
		foreach ( $notices as $notice_key => $notice ) :

			?>
			<div class="<?php echo esc_attr( $notice['class'] ); ?>">
				<p><?php echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) ); ?></p>
			</div>
		<?php

		endforeach;
	}

	/**
	 * Returns the message for display when the environment is incompatible with this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_environment_message() {

		return sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', $this->min_php, PHP_VERSION );
	}

	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function activation_check() {

		if ( ! $this->is_environment_compatible() ) {

			$this->deactivate_plugin();

			wp_die( $this->plugin_name . ' could not be activated. ' . $this->get_environment_message() );
		}
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

	/**
	 * Add notice to database
	 *
	 * since 1.0.0
	 * @param        $message
	 * @param string $type
	 */
	public function add_notice( $message, $type = 'success' ) {
			$notices = get_option( 'wpcp_admin_notifications', [] );
		if ( is_string( $message ) && is_string( $type ) && !wp_list_filter($notices, array('message' => $message)) ) {

			$notices[] = array(
				'message' => $message,
				'class'    => $type
			);

			update_option( 'wpcp_admin_notifications', $notices );
		}
	}

	/**
	 * Initializes the plugin.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {
		if ( $this->plugins_compatible() ) {
			$this->define_constants();
			$this->includes();
			do_action( 'content_pilot_loaded' );
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
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		//vendor
		require_once WPCP_PATH . '/vendor/autoload.php';

		//functions
		require_once WPCP_INCLUDES . '/core-functions.php';
		require_once WPCP_INCLUDES . '/formatting-functions.php';
		require_once WPCP_INCLUDES . '/action-functions.php';
		require_once WPCP_INCLUDES . '/class-install.php';
		require_once WPCP_INCLUDES . '/post-types.php';
		require_once WPCP_INCLUDES . '/script-functions.php';
		require_once WPCP_INCLUDES . '/metabox-functions.php';

		//core files
		require_once WPCP_INCLUDES . '/wp-async-request.php';
		require_once WPCP_INCLUDES . '/wp-background-process.php';
		require_once WPCP_INCLUDES . '/class-automatic-campaign.php';
		require_once WPCP_INCLUDES . '/class-fetch-contents.php';
		require_once WPCP_INCLUDES . '/class-elements.php';
		require_once WPCP_INCLUDES . '/class-ajax.php';
		require_once WPCP_INCLUDES . '/class-campaign.php';
		require_once WPCP_INCLUDES . '/class-module.php';

		//settings
		require_once WPCP_INCLUDES . '/class-admin-menu.php';
		require_once WPCP_INCLUDES . '/class-settings-api.php';
		require_once WPCP_INCLUDES . '/class-settings.php';

		//misc
		require_once WPCP_INCLUDES . '/class-promotion.php';

		//modules
		require_once WPCP_MODULES . '/class-feed.php';
		require_once WPCP_MODULES . '/class-article.php';
		require_once WPCP_MODULES . '/class-envato.php';
		require_once WPCP_MODULES . '/class-youtube.php';
		require_once WPCP_MODULES . '/class-flickr.php';
	}

	/**
	 * instantiate plugins
	 * since 1.0.0
	 */
	public function instantiate() {
		if ( $this->plugins_compatible() ) {
			new WPCP_Ajax();
			new WPCP_Automatic_Campaign();
			new WPCP_Fetch_Contents();
			new WPCP_Feed();
			new WPCP_Article();
			new WPCP_Envato();
			new WPCP_Youtube();
			new WPCP_Flickr();

			$this->elements = new WPCP_Elements();
			$this->modules  = new WPCP_Module();
		}
	}

	/**
	 * Returns the plugin loader main instance.
	 *
	 * @since 1.0.0
	 * @return \ContentPilot
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

}

function content_pilot() {
	return ContentPilot::instance();
}

//fire off the plugin
content_pilot();
