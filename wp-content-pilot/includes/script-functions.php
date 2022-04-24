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
defined( 'ABSPATH' ) || exit();
function wpcp_load_admin_scripts( $hook ) {
	$js_dir     = WPCP_ASSETS_URL . '/js/';
	$css_dir    = WPCP_ASSETS_URL . '/css/';
	$vendor_dir = WPCP_ASSETS_URL . '/vendor/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

	// These have to be global
	wp_enqueue_script( 'jquery-tiptip', $vendor_dir . 'tiptip/jquery.tiptip.min.js', array( 'jquery' ), WPCP_VERSION );

	wp_enqueue_style( 'wpcp-select2', $vendor_dir . 'select2/select2' . $suffix . '.css', array(), WPCP_VERSION );
	wp_enqueue_script( 'wpcp-select2', $vendor_dir . 'select2/select2' . $suffix . '.js', array( 'jquery' ), WPCP_VERSION,true );

	wp_enqueue_style( 'wpcp-ionslider', $vendor_dir . 'ionslider/ion.rangeSlider' . $suffix . '.css', array(), WPCP_VERSION, false );
	wp_enqueue_script( 'wpcp-ionslider', $vendor_dir . 'ionslider/ion.rangeSlider' . $suffix . '.js', array( 'jquery' ), WPCP_VERSION, false );


	wp_enqueue_style( 'wp-content-pilot', $css_dir . 'wp-content-pilot' . $suffix . '.css', array(), WPCP_VERSION );
	wp_enqueue_script( 'wp-content-pilot', $js_dir . 'wp-content-pilot' . $suffix . '.js', array( 'jquery' ), WPCP_VERSION, false );

	wp_localize_script( 'wp-content-pilot', 'wp_content_pilot_i10n', array(
		'nonce'    => wp_create_nonce( 'ajax_action' ),
		'ajax_url' => admin_url( 'admin-ajax.php' ),
	) );
}

add_action( 'admin_enqueue_scripts', 'wpcp_load_admin_scripts' );
