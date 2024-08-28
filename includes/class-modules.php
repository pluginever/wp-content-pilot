<?php

namespace WPCP;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Module class.
 *
 * @since 1.0.0
 * @package WPContentPilot
 */
class Modules {

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
	 * @var Modules $instance Instance of Modules.
	 */
	protected static $instance = null;

	/**
	 * Main Modules Instance.
	 * Ensures only one instance of Modules is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return Modules Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Modules constructor.
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

		foreach ( $modules as $module ) {
			$this->modules[ $module->slug ] = $module;
		}
	}

	/**
	 * Get modules.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_modules(){
		return $this->modules;
	}

	/**
	 * Get module by name.
	 *
	 * @since 1.0.0
	 * @param string $name Module name.
	 * @return \WPContentPilot\Module|null
	 */
	public function get_module( $name ){
		return isset( $this->modules[ $name ] ) ? $this->modules[ $name ] : null;
	}
}
