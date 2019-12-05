<?php
/**
 * Module Class
 *
 * @package     WP Content Pilot
 * @subpackage  Module
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
defined('ABSPATH')|| exit();

class WPCP_Module {
	/**
	 * Hold the modules
	 *
	 * @var array
	 */
	protected $modules;

	/**
	 * WPCP_Campaign constructor.
	 */
	public function __construct() {
		$this->init_modules();
	}

	/**
	 * init modules
	 * This is the place where all modules
	 * will hook and register
	 *
	 * @since 1.0.0
	 */
	protected function init_modules() {
		$modules = apply_filters( 'wpcp_modules', array() );

		$this->modules = $modules;
	}


	/**
	 * Get all the registered modules
	 *
	 * @return array
	 */
	public function get_modules() {
		return wpcp_array_sort($this->modules, 'title');
	}

	/**
	 * Get a module
	 *
	 * @param $module
	 *
	 * @return array|boolean
	 */
	public function get_module( $module ) {
		if ( array_key_exists( $module, $this->modules ) ) {
			return $this->modules[ $module ];
		}

		return false;
	}

	/**
	 * Get the module property
	 *
	 * @since 1.0.0
	 *
	 * @param $module
	 * @param $property
	 *
	 * @return bool|mixed
	 */
	public function get_module_property( $module, $property ) {
		$called_module = array();
		if ( array_key_exists( $module, $this->modules ) ) {
			$called_module = $this->modules[ $module ];
		}

		if ( array_key_exists( $property, $called_module ) ) {
			return $called_module[ $property ];
		}

		return false;

	}

}
