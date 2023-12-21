<?php
defined( 'ABSPATH' ) || die();

/**
 * Class WPCP_Modules.
 *
 * @since 1.0.0
 */
class WPCP_Modules {
	/**
	 * Modules.
	 *
	 * @var array $modules Modules.
	 *
	 * @since 1.0.0
	 */
	public $modules = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WPCP_Modules $instance Instance of WPCP_Modules.
	 */
	protected static $instance = null;

	/**
	 * Main WPCP_Modules Instance.
	 * Ensures only one instance of WPCP_Modules is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return WPCP_Modules Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WPCP_Modules constructor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_modules' ) );
	}

	/**
	 * Init modules.
	 * This is the place where all modules will hook and register.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_modules() {
		$modules = apply_filters( 'wpcp_modules', array() );

		/* @var WPCP_Module $class WPCP_Module */
		foreach ( $modules as $key => $class ) {
			if ( class_exists( $class ) ) {
				$this->modules[ $key ] = $class::instance();
			}
		}
	}

	/**
	 * Check if module exist.
	 *
	 * @param mixed $module Module.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function exist( $module ) {
		if ( array_key_exists( $module, $this->modules ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Load modules.
	 *
	 * @param mixed $module Module.
	 *
	 * @since 1.0.0
	 * @return WPCP_Module|object
	 */
	public function load( $module ) {
		if ( array_key_exists( $module, $this->modules ) ) {
			return $this->modules[ $module ];
		}

		return new stdClass();
	}
}

WPCP_Modules::instance();
