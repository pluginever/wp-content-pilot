<?php
/**
 * Post Type Functions
 *
 * @package     WP Content Pilot
 * @subpackage  Scripts
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

function wpcp_load_admin_scripts( $hook ) {
	$js_dir     = WPCP_ASSETS_URL . '/js/';
	$css_dir    = WPCP_ASSETS_URL . '/css/';
	$vendor_dir = WPCP_ASSETS_URL . '/vendor/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

	// These have to be global

	wp_register_style( 'jquery-chosen', $vendor_dir . 'chosen/chosen' . $suffix . '.css', array(), WPCP_VERSION );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $vendor_dir . 'chosen/chosen.jquery' . $suffix . '.js', array( 'jquery' ), WPCP_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_register_script( 'wpcp-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery-chosen', 'wp-util' ), WPCP_VERSION, false );

	wp_enqueue_script( 'wpcp-admin-scripts' );

	wp_register_style( 'wpcp-admin-styles', $css_dir . 'admin-styles' . $suffix . '.css', array(), WPCP_VERSION );
	wp_enqueue_style( 'wpcp-admin-styles' );
}

add_action( 'admin_enqueue_scripts', 'wpcp_load_admin_scripts', 100 );
