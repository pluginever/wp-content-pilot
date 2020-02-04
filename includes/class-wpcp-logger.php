<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit();

class WPCP_Logger {
	/**
	 * $log_file - path and log file name
	 * @var string
	 */
	protected $log_file;

	/**
	 * $file - file
	 * @var string
	 */
	protected $file;

	/**
	 * $options - settable options - future use - passed through constructor
	 * @var array
	 */
	protected $options = array(
		'dateFormat' => 'd-M-Y H:i:s'
	);

	/**
	 * @var WPCP_Logger
	 */
	protected static $_instance = null;

	/**
	 * @param string $log_file
	 * @param array $params
	 *
	 * @since 1.2.0
	 * WPCP_Logger constructor.
	 */
	public function __construct( $log_file = 'content-pilot.log', $params = array() ) {
		$this->log_file = trailingslashit( WP_CONTENT_DIR ).ltrim( $log_file);
		$this->params   = array_merge( $this->options, $params );
		//Create log file if it doesn't exist.
		if ( ! file_exists( $this->log_file ) ) {
			fopen( $this->log_file, 'w' ) or exit( "Can't create $log_file!" );
		}

		//Check permissions of file.
		if ( ! is_writable( $this->log_file ) ) {
			//throw exception if not writable
			new WP_Error( 'write-protected',"ERROR: Unable to write to file!", 1 );
		}
	}
	/**
	 * Debug method (write debug message)
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function debug( $message ) {
		$this->writeLog( $message, 'DEBUG' );
	}

	/**
	 * Info method (write info message)
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function info( $message ) {
		$this->writeLog( $message, 'INFO' );
	}

	/**
	 * Warning method (write warning message)
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function warning( $message ) {
		$this->writeLog( $message, 'WARNING' );
	}

	/**
	 * Error method (write error message)
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function error( $message ) {
		$this->writeLog( $message, 'ERROR' );
	}

	/**
	 * Write to log file
	 *
	 * @param string $message
	 * @param string $severity
	 *
	 * @return void
	 */
	public function writeLog( $message, $severity ) {
		// open log file
		if ( ! is_resource( $this->file ) ) {
			$this->openLog();
		}
		// grab the url path ( for troubleshooting )
		$path = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		//Grab time - based on timezone in php.ini
		$time = date( $this->params['dateFormat'] );
		// Write time, url, & message to end of file
		fwrite( $this->file, "[$time] [$path] : [$severity] - $message" . PHP_EOL );
	}

	/**
	 * Open log file
	 * @return void
	 */
	private function openLog() {
		$openFile = $this->log_file;
		// 'a' option = place pointer at end of file
		$this->file = fopen( $openFile, 'a' ) or exit( "Can't open $openFile!" );
	}

	/**
	 * Class destructor
	 */
	public function __destruct() {
		if ( $this->file ) {
			fclose( $this->file );
		}
	}

	/**
	 * @since 1.2.0
	 * @return WPCP_Logger
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

/**
 * @since 1.2.0
 * @return WPCP_Logger
 */
function wpcp_logger(){
	return WPCP_Logger::instance();
}

wpcp_logger();
