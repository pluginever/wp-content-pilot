<?php
/**
 * Admin_Menu Class
 *
 * @package     WP Content Pilot
 * @subpackage  Admin_Menu
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPCP_Admin_Menu {


	/**
	 * Admin_Menu constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}


	function admin_menu() {

		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Status', 'wp-content-pilot' ), __( 'Status', 'wp-content-pilot' ), 'manage_options', 'wpcp-status', array(
			$this,
			'status_page'
		) );

//		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Logs', 'wp-content-pilot' ), __( 'Logs', 'wp-content-pilot' ), 'manage_options', 'wpcp-settings', array(
//			$this,
//			'logs_page'
//		) );
//
//		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Help', 'wp-content-pilot' ), sprintf('<span style="color: greenyellow;">%s</span>', __( 'Help', 'wp-content-pilot' )), 'manage_options', 'wpcp-settings', array(
//			$this,
//			'help_page'
//		) );

	}

	function status_page(){
		ob_start();
		include WPCP_VIEWS.'/menu/status.php';
		$html = ob_get_clean();

		echo $html;
	}
}

new WPCP_Admin_Menu();
