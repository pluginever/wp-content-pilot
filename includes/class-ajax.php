<?php
/**
 * Ajax Class
 *
 * @package     WP Content Pilot
 * @subpackage  Ajax
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPCP_Ajax {


	/**
	 * WPCP_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpcp_get_campaign_options_metabox_content', array( $this, 'campaign_options_metabox_content' ) );
	}

	public function campaign_options_metabox_content() {
		ob_start();

		$post_id       = intval( $_REQUEST['post_id'] );
		$campaign_type = esc_attr( $_REQUEST['campaign_type'] );

		wpcp_campaign_options_metabox_fields( $post_id, $campaign_type );
		$html = ob_get_contents();
		ob_get_clean();
		wp_send_json_success( $html );
	}
}
