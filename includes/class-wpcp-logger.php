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
	 * @var resource
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
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * @param string $log_file
	 * @param array $params
	 *
	 * @since 1.2.0
	 * WPCP_Logger constructor.
	 */
	public function __construct( $log_file = 'debug.log', $params = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->debug = true;
		}

		$this->log_file = trailingslashit( WP_CONTENT_DIR ) . ltrim( $log_file );
		$this->params   = array_merge( $this->options, $params );
		//Create log file if it doesn't exist.
		if ( $this->debug && ! file_exists( $this->log_file ) ) {
			fopen( $this->log_file, 'w' ) or exit( "Can't create $log_file!" );
		}

		//Check permissions of file.
		if ( $this->debug && ! is_writable( $this->log_file ) ) {
			//throw exception if not writable
			new WP_Error( 'write-protected', "ERROR: Unable to write to file!", 1 );
		}
	}

	/**
	 * Debug method (write debug message)
	 *
	 * @param string $message
	 * @param int $campaign_id
	 *
	 * @return void
	 */
	public function debug( $message, $campaign_id = 0 ) {
		$this->writeLog( $message, 'DEBUG', $campaign_id );
	}

	/**
	 * Info method (write info message)
	 *
	 * @param string $message
	 * @param int $campaign_id
	 *
	 * @return void
	 */
	public function info( $message, $campaign_id = 0 ) {
		$this->writeLog( $message, 'INFO', $campaign_id );
	}

	/**
	 * Warning method (write warning message)
	 *
	 * @param string $message
	 * @param int $campaign_id
	 *
	 * @return void
	 */
	public function warning( $message, $campaign_id = 0 ) {
		$this->writeLog( $message, 'WARNING', $campaign_id );
	}

	/**
	 * Error method (write error message)
	 *
	 * @param string $message
	 * @param int $campaign_id
	 *
	 * @return void
	 */
	public function error( $message, $campaign_id = 0 ) {
		$this->writeLog( $message, 'ERROR', $campaign_id = 0 );
	}

	/**
	 * Write to log file
	 *
	 * @param string $message
	 * @param string $severity
	 * @param int $campaign_id
	 *
	 * @return void
	 */
	public function writeLog( $message, $severity, $campaign_id = 0 ) {
		// open log file
		if ( $this->debug && ! is_resource( $this->file ) ) {
			$this->openLog();
		}

		if ( $this->debug ) {
			//Grab time - based on timezone in php.ini
			$time = date( $this->params['dateFormat'] );
			// Write time, url, & message to end of file
			fwrite( $this->file, strip_tags("[$time] : [$severity] - $message") . PHP_EOL );
		}

		$severities = [ 'INFO', 'WARNING', 'ERROR' ];
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$severities[] = 'DEBUG';
		}

		if ( in_array( $severity, $severities ) ) {
			wpcp_insert_log( $message, $severity, $campaign_id );
		}


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
		if ( $this->debug && $this->file ) {
			fclose( $this->file );
		}
	}

	/**
	 * @return WPCP_Logger
	 * @since 1.2.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

/**
 * @return WPCP_Logger
 * @since 1.2.0
 */
function wpcp_logger() {
	return WPCP_Logger::instance();
}

wpcp_logger();
