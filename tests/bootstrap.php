<?php
/**
 * WPCP_Unit_Tests_Bootstrap
 *
 * @since 2.0
 */
class WPCP_Unit_Tests_Bootstrap {

	/**
	 * @var self
	 */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	// directory storing dependency plugins
	public $modules_dir;

	/**
	 * Setup the unit testing environment
	 *
	 * @since 2.0
	 */
	function __construct() {

		ini_set( 'display_errors','on' );
		error_reporting( E_ALL );

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( $this->tests_dir );
		$this->modules_dir  = dirname( dirname( $this->tests_dir ) );
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : $this->plugin_dir . '/tmp/wordpress-tests-lib';

		$_SERVER['REMOTE_ADDR'] = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SERVER['SERVER_NAME'] = ( isset( $_SERVER['SERVER_NAME'] ) ) ? $_SERVER['SERVER_NAME'] : 'wpcp_test';

		// load test function so tests_add_filter() is available
		require_once( $this->wp_tests_dir  . '/includes/functions.php' );

		// load WPCP
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_wpcp' ) );

		// install WPCP
		tests_add_filter( 'setup_theme', array( $this, 'install_wpcp' ) );

		$GLOBALS['wp_options'] = array(
			'active_plugins' => array(
				$this->modules_dir . '/wp-content-pilot/wp-content-pilot.php',
			),
		);

		// load the WP testing environment
		require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

		// load testing framework
		$this->includes();


		// Set Subcriptions install data so that the Importer won't exit early
		$active_plugins   = get_option( 'active_plugins', array() );
		$active_plugins[] = 'wp-content-pilot/wp-content-pilot.php';
		update_option( 'active_plugins', $active_plugins );
		update_option( 'wpcp_version', content_pilot()->get_version() );
	}

	/**
	 * Load WooCommerce
	 *
	 * @since 2.0
	 */
	public function load_wpcp() {
		require_once( $this->modules_dir . '/wp-content-pilot/wp-content-pilot.php' );
	}

	/**
	 * Load WooCommerce for testing
	 *
	 * @since 2.0
	 */
	function install_wpcp() {

		echo "Installing Content Pilot..." . PHP_EOL;

		define( 'WP_UNINSTALL_PLUGIN', true );

		include( $this->modules_dir . '/wp-content-pilot/uninstall.php' );

		WPCP_Install::activate();

		content_pilot();

		echo "Content Pilot Finished Installing..." . PHP_EOL;
	}

	/**
	 * Load test cases and factories
	 *
	 * @since 2.0
	 */
	public function includes() {
		// Load WPCP Helper Functions
		include( dirname( __FILE__ ) . '/helpers/class-helper-module.php' );
	}

	/**
	 * Get the single class instance
	 *
	 * @since 2.0
	 * @return WPCP_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
WPCP_Unit_Tests_Bootstrap::instance();
