<?php
/**
 * Campaign Class
 *
 * @package     WP Content Pilot
 * @subpackage  Campaign
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPCP_Campaign {
	/**
	 * This is actually a post ID
	 *
	 * @var int
	 */
	protected $campaign_id;

	/**
	 * @var string
	 */
	protected $campaign_type;

	/**
	 * @var string
	 */
	protected $keyword;

	/**
	 * @var string
	 */
	protected $campaign_title;


	abstract function register_module( $modules );

	abstract function setup();

	abstract function discover_links();

	abstract function fetch_post($link);

	/**
	 * setup campaign id
	 *
	 * @since 1.0.0
	 *
	 * @param $campaign_id
	 */
	public function set_campaign_id( $campaign_id ) {
		$this->campaign_id = intval( $campaign_id );
	}

	/**
	 * keyword for the campaign
	 *
	 * @since 1.0.0
	 *
	 * @param $keyword
	 */
	public function set_keyword( $keyword ) {
		$this->keyword = strip_tags( $keyword );
	}

	/**
	 * set campaign type
	 *
	 * @since 1.0.0
	 *
	 * @param $campaign_type
	 */
	public function set_campaign_type( $campaign_type ) {
		$this->campaign_type = $campaign_type;
	}


	/**
	 * run the campaign
	 *
	 * @since 1.0.0
	 * @return int|\WP_Error
	 */
	public function run() {
		$link = $this->get_link();
		if ( ! $link ) {
			$links = $this->discover_links();

			if ( is_wp_error( $links ) ) {
				return $links;
			}

			//hook here for any link to subtract
			$links = apply_filters( 'wpcp_fetched_links', $links, $this->campaign_id, $this->campaign_type );

			if ( empty( $links ) ) {
				return new \WP_Error( 'no-links-found', __( 'Could not retrieve any valid links', 'wp-content-pilot' ) );
			}

			//check the result
			$str_links = implode( ' ', $links );

			if ( $this->is_result_like_last_time( $str_links ) ) {
				$msg = __( sprintf( 'Could not discover any new links to grab contents for the keyword "%s". Please try letter.', $this->keyword ), 'wp-content-pilot' );
				wpcp_log( $msg,'log' );

				return new \WP_Error( 'no-new-result', $msg );
			}


			$inserted = $this->inset_links( $links );

			wpcp_log(  __( sprintf( 'Total %d links inserted', $inserted ), 'wp-content-pilot' ), 'log' );

			$link = $this->get_link();
			if ( ! $link ) {
				return new \WP_Error( 'no-valid-links-found', __( 'Could not retrieve any valid links', 'content-pilot' ) );
			}
		}

		//set link as failed if run till end then mark as success
		wpcp_update_link( $link->id, [ 'status' => 3 ] );

		$article = $this->fetch_post( $link );
		if ( is_wp_error( $article ) ) {
			return $article;
		}






		//check for acceptance of the article

		//after posting

		//
	}

	/**
	 * Get new link
	 *
	 * @since 1.0.0
	 *
	 * @return object|bool
	 */
	protected function get_link() {
		global $wpdb;
		$table  = $wpdb->prefix . 'wpcp_links';
		$sql    = $wpdb->prepare( "select * from {$table} where keyword = %s and camp_id  = %s and camp_type= %s and status = '0'",
			$this->keyword,
			$this->campaign_id,
			$this->campaign_type
		);
		$result = $wpdb->get_row( $sql );

		if ( empty( $result ) ) {
			return false;
		}

		return $result;
	}


	/**
	 * Checks the result if its like the last run
	 *
	 * @since 1.0.0
	 *
	 * @param $html
	 *
	 * @return bool
	 *
	 */
	protected function is_result_like_last_time( $html ) {
		$hash      = @md5( (string) $html );
		$last_feed = wpcp_get_post_meta( $this->campaign_id, $this->get_uid( 'last-feed' ), '' );
		if ( $hash == $last_feed ) {
			return true;
		}

		update_post_meta( $this->campaign_id, $this->get_uid( 'last-feed' ), $hash );

		return false;
	}

	/**
	 * Insert links
	 *
	 * @since 1.0.0
	 *
	 * @param $links
	 *
	 * @return int
	 *
	 */
	protected function inset_links( $links ) {
		$counter = 0;
		foreach ( $links as $indentifier => $link ) {
			$id = wpcp_insert_link( array(
				'camp_id'    => $this->campaign_id,
				'url'        => $link,
				'keyword'    => $this->keyword,
				'identifier' => $indentifier,
				'camp_type'  => $this->campaign_type,
				'status'     => 0,
			) );

			if ( $id ) {
				$counter ++;
			}
		}

		return $counter;
	}


	/**
	 * Get unique string for the campaign
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function get_uid( $string = '' ) {
		$string = '_wpcp_' . $this->campaign_id . '-' . $this->campaign_type . '-' . $this->keyword . '-' . $string;

		return sanitize_title( $string );
	}


}
