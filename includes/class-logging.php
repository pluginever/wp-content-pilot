<?php
/**
 * Class for logging events and errors
 *
 * @package     WPCP
 * @subpackage  Logging
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPCP_Logging Class
 *
 * A general use class for logging events and errors.
 *
 * @since 1.2.0
 */
class WPCP_Logging {

	public $is_writable = true;
	private $filename   = '';
	private $file       = '';

	/**
	 * Set up the WPCP Logging Class
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'setup_log_file' ), 0 );
	}

	/**
	 * Sets up the log file if it is writable
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function setup_log_file() {

		$upload_dir       = wp_upload_dir();
		$this->filename   = wp_hash( home_url( '/' ) ) . '-wpcp-debug.log';
		$this->file       = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

	}

	/**
	 * Retrieve the log data
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function get_file_contents() {
		return $this->get_file();
	}

	/**
	 * Log message to file
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function log_to_file( $message = '' ) {
		$message = date( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		$this->write_to_log( $message );

	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @since 1.2.0
	 * @return string
	 */
	protected function get_file() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );

		}

		return $file;
	}

	/**
	 * Write the log message
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function write_to_log( $message = '' ) {
		$file = $this->get_file();
		$file .= $message;
		@file_put_contents( $this->file, $file );
	}

	/**
	 * Delete the log file or removes all contents in the log file if we cannot delete it
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function clear_log_file() {
		@unlink( $this->file );

		if ( file_exists( $this->file ) ) {

			// it's still there, so maybe server doesn't have delete rights
			chmod( $this->file, 0664 ); // Try to give the server delete rights
			@unlink( $this->file );

			// See if it's still there
			if ( @file_exists( $this->file ) ) {

				/*
				 * Remove all contents of the log file if we cannot delete it
				 */
				if ( is_writeable( $this->file ) ) {

					file_put_contents( $this->file, '' );

				} else {

					return false;

				}

			}

		}

		$this->file = '';
		return true;

	}

	/**
	 * Return the location of the log file that WPCP_Logging will use.
	 *
	 * Note: Do not use this file to write to the logs, please use the `wpcp_debug_log` function to do so.
	 *
	 * @since 2.9.1
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		return $this->file;
	}

}

// Initiate the logging system
$GLOBALS['wpcp_logs'] = new WPCP_Logging();


/**
 * Logs a message to the debug log file
 *
 * @since 1.2.0
 *
 * @param string $message
 * @global $wpcp_logs \WPCP_Logging Object
 * @return void
 */
function wpcp_debug_log( $message = '', $force = false ) {
	global $wpcp_logs;

	if ( wpcp_is_debug_mode() || $force ) {

		if( function_exists( 'mb_convert_encoding' ) ) {

			$message = mb_convert_encoding( $message, 'UTF-8' );

		}

		$wpcp_logs->log_to_file( $message );

	}
}
