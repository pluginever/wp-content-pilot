<?php
defined( 'ABSPATH' ) || die();

class WPCP_Modules {
	/**
	 * @var array
	 */
	public $modules = array();

	/**
	 * The single instance of the class
	 *
	 * @var WPCP_Modules
	 */
	protected static $_instance = null;

	/**
	 * Main WPCP_Modules Instance.
	 *
	 * Ensures only one instance of WPCP_Modules is loaded or can be loaded.
	 *
	 * @return WPCP_Modules Main instance
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
	 * WPCP_Modules constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_modules' ) );
	}

	/**
	 * init modules
	 * This is the place where all modules
	 * will hook and register
	 *
	 * @since 1.0.0
	 */
	public function init_modules() {
		$modules = apply_filters( 'wpcp_modules', array() );

		/* @var $class WPCP_Module */
		foreach ( $modules as $key => $class ) {
			if ( class_exists( $class ) ) {
				$this->modules[ $key ] = $class::instance();
			}
		}
	}

	/**
	 * check if module exist
	 *
	 * @param $module
	 *
	 * @return bool
	 */
	public function exist( $module ) {
		if ( array_key_exists( $module, $this->modules ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $module
	 *
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
