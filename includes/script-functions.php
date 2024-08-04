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

/**
 * Enqueue admin scripts.
 *
 * @since  1.2.0
 * @return void
 */
function wpcp_load_admin_scripts() {
	$js_dir     = WPCP_ASSETS_URL . '/js/';
	$css_dir    = WPCP_ASSETS_URL . '/css/';
	$vendor_dir = WPCP_ASSETS_URL . '/vendor/';

	// These have to be global.
	wp_enqueue_script( 'jquery-tiptip', $vendor_dir . 'tiptip/jquery.tiptip.min.js', array( 'jquery' ), WPCP_VERSION, false );

	wp_enqueue_style( 'wpcp-select2', $vendor_dir . 'select2/css/select2.css', array(), WPCP_VERSION );
	wp_enqueue_script( 'wpcp-select2', $vendor_dir . 'select2/js/select2.js', array( 'jquery' ), WPCP_VERSION, true );

	wp_enqueue_style( 'wpcp-ionslider', $vendor_dir . 'ionslider/css/ion.rangeSlider.css', array(), WPCP_VERSION, false );
	wp_enqueue_script( 'wpcp-ionslider', $vendor_dir . 'ionslider/js/ion.rangeSlider.js', array( 'jquery' ), WPCP_VERSION, false );

	wp_enqueue_style( 'wp-content-pilot', $css_dir . 'wp-content-pilot.css', array(), WPCP_VERSION );
	wp_enqueue_script( 'wp-content-pilot', $js_dir . 'wp-content-pilot.js', array( 'jquery' ), WPCP_VERSION, false );

	wp_localize_script(
		'wp-content-pilot',
		'wp_content_pilot_i10n',
		array(
			'nonce'    => wp_create_nonce( 'ajax_action' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		)
	);
}

add_action( 'admin_enqueue_scripts', 'wpcp_load_admin_scripts' );
