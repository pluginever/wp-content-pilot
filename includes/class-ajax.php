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
defined('ABSPATH')|| exit();

class WPCP_Ajax {
	/**
	 * WPCP_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpcp_delete_all_posts_by_campaign_id', array( $this, 'delete_all_posts_by_campaign_id' ) );
	}

	public function delete_all_posts_by_campaign_id() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['camp_id'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wpcp_delete_all_posts_' . $_REQUEST['camp_id'] ) ) {
			wp_send_json_error( 'Unauthorized!!!' );
		}

		$camp_id = isset( $_REQUEST['camp_id'] ) && ! empty( $_REQUEST['camp_id'] ) ? $_REQUEST['camp_id'] : false;

		if ( $camp_id !== false ) {
			$args = array(
				'meta_key'   => '_wpcp_campaign_generated_post',
				'meta_value' => $camp_id,
			);

			$posts = wpcp_get_posts( $args );

			if ( is_array( $posts ) && count( $posts ) ) {
				foreach ( $posts as $post ) {
					wp_delete_post( $post->ID, true );
				}
			}
		} else {
			wp_send_json_error( 'Invalid campaign ID.' );
		}

		wp_send_json_success( 'Done' );
	}
}
