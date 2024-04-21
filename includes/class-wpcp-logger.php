<?php
/**
 * WPCP Logger.
 *
 * @package WP Content Pilot
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();

/**
 * WPCP_Logger Class.
 *
 * @package WP Content Pilot
 * @since 1.0.0
 */
class WPCP_Logger {
	/**
	 * $log_file - path and log file name.
	 *
	 * @var string $log_file Path and log file name.
	 *
	 * @since 1.0.0
	 */
	protected $log_file;

	/**
	 * $file - file
	 *
	 * @var resource $file File
	 *
	 * @since 1.0.0
	 */
	protected $file;

	/**
	 *  Settable options - future use - passed through constructor.
	 *
	 * @var array $options Settable options.
	 *
	 * @since 1.0.0
	 */
	protected $options = array(
		'dateFormat' => 'd-M-Y H:i:s',
	);

	/**
	 * Instance of WPCP_Logger class.
	 *
	 * @var WPCP_Logger $instance Instance of WPCP_Logger.
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Debug status.
	 *
	 * @var bool $debug Debug.
	 *
	 * @since 1.0.0
	 */
	protected $debug = false;

	/**
	 * Array of parameters.
	 *
	 * @var array|string[]
	 *
	 * @since 1.0.0
	 */
	private array $params;

	/**
	 * WPCP_Logger constructor.
	 *
	 * @param string $log_file Log file.
	 * @param array  $params Parameters.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function __construct( $log_file = 'debug.log', $params = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->debug = true;
		}

		$this->log_file = trailingslashit( WP_CONTENT_DIR ) . ltrim( $log_file );
		$this->params   = array_merge( $this->options, $params );
		// Create log file if it doesn't exist.
		if ( $this->debug && ! file_exists( $this->log_file ) ) {
			fopen( $this->log_file, 'w' ) or exit( esc_html__( "Can't create file!", 'wp-content-pilot' ) ); // phpcs:ignore Squiz.Operators.ValidLogicalOperators.NotAllowed
		}

		// Check permissions of file.
		if ( $this->debug && ! is_writable( $this->log_file ) ) {
			// Throw exception if not writable.
			new WP_Error( 'write-protected', __( 'ERROR: Unable to write to file!', 'wp-content-pilot' ), 1 );
		}
	}

	/**
	 * Debug method (write debug message).
	 *
	 * @param string $message Debug message.
	 * @param int    $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function debug( $message, $campaign_id = 0 ) {
		$this->write_log( $message, 'DEBUG', $campaign_id );
	}

	/**
	 * Info method (write info message).
	 *
	 * @param string $message Info message.
	 * @param int    $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function info( $message, $campaign_id = 0 ) {
		$this->write_log( $message, 'INFO', $campaign_id );
	}

	/**
	 * Warning method (write warning message).
	 *
	 * @param string $message Warning message.
	 * @param int    $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function warning( $message, $campaign_id = 0 ) {
		$this->write_log( $message, 'WARNING', $campaign_id );
	}

	/**
	 * Error method (write error message).
	 *
	 * @param string $message Error message.
	 * @param int    $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function error( $message, $campaign_id = 0 ) {
		$this->write_log( $message, 'ERROR', $campaign_id );
	}

	/**
	 * Write to log file.
	 *
	 * @param string $message Log message.
	 * @param string $severity Severity text.
	 * @param int    $campaign_id The campaign ID.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function write_log( $message, $severity, $campaign_id = 0 ) {
		// Open log file.
		if ( $this->debug && ! is_resource( $this->file ) ) {
			$this->open_log();
		}

		if ( $this->debug ) {
			// Grab time.
			$time = gmdate( $this->params['dateFormat'] );
			// Write time, url, & message to end of file.
			fwrite( $this->file, wp_strip_all_tags( "[$time] : [$severity] - $message" ) . PHP_EOL );
		}

		$severities = array( 'INFO', 'WARNING', 'ERROR' );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$severities[] = 'DEBUG';
		}

		if ( in_array( $severity, $severities, true ) ) {
			wpcp_insert_log( $message, $severity, $campaign_id );
		}
	}

	/**
	 * Open log file.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function open_log() {
		$open_file = $this->log_file;
		// 'a' option = place pointer at end of file.
		$this->file = fopen( $open_file, 'a' ) or exit( esc_html__( "Can't open file!", 'wp-content-pilot' ) ); // phpcs:ignore Squiz.Operators.ValidLogicalOperators.NotAllowed
	}

	/**
	 * Class destructor.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function __destruct() {
		if ( $this->debug && $this->file ) {
			fclose( $this->file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}
	}

	/**
	 * Instance of the class.
	 *
	 * @since 1.2.0
	 * @return WPCP_Logger
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Instance of the class.
 *
 * @since 1.2.0
 * @return WPCP_Logger
 */
function wpcp_logger() {
	return WPCP_Logger::instance();
}

/**
 * WPCP Logger..
 *
 * @since 1.2.0
 * @return void
 */
wpcp_logger();
