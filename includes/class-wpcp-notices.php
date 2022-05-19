<?php
defined( 'ABSPATH' ) || exit();

/**
 * @since
 * Class WPCP_Admin_Notices
 */
class WPCP_Admin_Notices {
	/**
	 * @var array
	 */
	private static $notices = array();
	/**
	 * @var array
	 */
	private static $saved_notices = array();
	/**
	 * @var array
	 */
	private static $dismissible_notices = array();
	/**
	 * @var array
	 */
	private static $dismissed_notices = array();
	/**
	 * @var array
	 */
	private static $predefined_notices = array(
		'upgrade_notice' => 'upgrade_notice',
		//'spinner_notice' => 'spinner_notice',
		'article_notice' => 'article_notice'
	);

	/**
	 * Constructor.
	 */
	public static function init() {
		//set already dismissed notices.
		$dismissed_notices       = get_user_meta( get_current_user_id(), 'wpcp_dismissed_notices', true );
		self::$dismissed_notices = empty( $dismissed_notices ) || ! is_array( $dismissed_notices ) ? array() : $dismissed_notices;

		//enqueue scripts
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		//dismiss notice
		add_action( 'wp_ajax_wpcp_dismiss_notice', array( __CLASS__, 'dismiss_notice' ) );

		//output notices
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );

		// Show maintenance notices.
		add_action( 'admin_init', array( __CLASS__, 'predefined_notices' ) );

		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ), 100 );
	}

	/**
	 * @since 1.2.6
	 */
	public static function enqueue_scripts() {
		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_add_inline_script( 'wp-content-pilot', "
					jQuery( function( $ ) {

						jQuery( '.wpcp_notice' ).on( 'click', '.notice-dismiss', function() {
							var data = {
								action: 'wpcp_dismiss_notice',
								notice: jQuery( this ).parent().data( 'dismiss_class' ),
								security: '" . wp_create_nonce( 'wpcp_dismiss_notice_nonce' ) . "'
							};

							jQuery.post( '" . admin_url( 'admin-ajax.php' ) . "', data );
						} );
					} );
				" );
		}
	}

	/**
	 * Dismiss notice on user action.
	 * @since 1.2.0
	 */
	public static function dismiss_notice() {
		$failure = array(
			'result' => 'failure'
		);

		if ( ! check_ajax_referer( 'wpcp_dismiss_notice_nonce', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST['notice'] ) ) {
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( $failure );
		}

		$dismissed = self::dismiss_dismissible_notice( sanitize_text_field( $_POST['notice'] ) );

		if ( ! $dismissed ) {
			wp_send_json( $failure );
		}

		$response = array(
			'result' => 'success'
		);

		wp_send_json( $response );
	}

	/**
	 * Output all available notices.
	 *
	 * @since 1.2.6
	 */
	public static function output_notices() {

		$saved_notices       = get_option( 'wpcp_admin_notices', array() );
		$dismissible_notices = get_option( 'wpcp_dismissible_notices', array() );
		$notices             = $saved_notices + $dismissible_notices + self::$notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$notice_classes = array( 'wpcp_notice', 'notice', 'notice-' . $notice['type'] );
				$dismiss_attr   = $notice['dismiss_class'] ? 'data-dismiss_class="' . $notice['dismiss_class'] . '"' : '';

				if ( $notice['dismiss_class'] ) {
					$notice_classes[] = $notice['dismiss_class'];
					$notice_classes[] = 'is-dismissible';
				}

				echo '<div class="' . implode( ' ', $notice_classes ) . '"' . $dismiss_attr . '>';
				echo wpautop( wp_kses_post( $notice['content'] ) );
				echo '</div>';
			}

			// Clear.
			delete_option( 'wpcp_admin_notices' );
		}
	}

	public static function predefined_notices(){

		foreach ( self::$predefined_notices as $notice_name => $callback ) {
			if ( ! self::is_dismissible_notice_dismissed( $notice_name ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}
	}

	/**
	 * Save all notices.
	 *
	 * @since 1.2.6
	 */
	public static function save_notices() {
		update_option( 'wpcp_admin_notices', self::$saved_notices );
		update_option( 'wpcp_dismissible_notices', self::$dismissible_notices );
	}

	/**
	 * Add a notice/error.
	 *
	 * @param string $text
	 * @param mixed $args
	 * @param boolean $save_notice
	 */
	public static function add_notice( $text, $args = array( 'type' => 'success' ), $save_notice = false ) {
		if ( is_array( $args ) ) {
			$type          = $args['type'];
			$dismiss_class = isset( $args['dismiss_class'] ) ? $args['dismiss_class'] : false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class
		);

		if ( $dismiss_class && ! self::is_dismissible_notice_dismissed( $dismiss_class ) ) {
			self::$dismissible_notices[] = $notice;
		} else if ( $save_notice ) {
			self::$saved_notices[] = $notice;
		} else {
			self::$notices[] = $notice;
		}
	}

	/**
	 * Add a dimissible notice/error.
	 *
	 * @param string $text
	 * @param mixed $args
	 *
	 * @since  1.2.6
	 *
	 */
	public static function add_dismissible_notice( $text, $args ) {
		if ( isset( $args['dismiss_class'] ) || ! self::is_dismissible_notice_dismissed( $args['dismiss_class'] ) ) {
			self::add_notice( $text, $args );
		}
	}

	/**
	 * Checks if a dismissible notice has been dismissed in the past.
	 *
	 * @param string $notice_name
	 *
	 * @return boolean
	 * @since  1.2.6
	 *
	 */
	public static function is_dismissible_notice_dismissed( $notice_name ) {
		return in_array( $notice_name, self::$dismissed_notices );
	}

	/**
	 * Remove a dismissible notice.
	 *
	 * @param string $notice_name
	 *
	 * @since  1.2.6
	 *
	 */
	public static function dismiss_dismissible_notice( $notice_name ) {
		// Remove if not already removed.
		if ( ! self::is_dismissible_notice_dismissed( $notice_name ) ) {
			self::$dismissed_notices = array_merge( self::$dismissed_notices, array( $notice_name ) );
			update_user_meta( get_current_user_id(), 'wpcp_dismissed_notices', self::$dismissed_notices );

			return true;
		}

		return false;
	}

	/**
	 * Add 'upgrade_notice' notice.
	 *
	 * @since  1.2.6
	 */
	public static function upgrade_notice() {
		if ( defined( 'WPCP_PRO_VERSION' ) ) {
			return;
		}
		$notice = __( '<b>WP Content Pilot</b> is powering <b>5000+ companies</b> in generating automatic contents and affiliation with its <b>25+</b> types of campaign. Upgrade to Pro now & get 10% discount using coupon <strong>WPCPFREE2PRO</strong>', 'wp-content-pilot' );
		$notice .= '  <a href="https://www.pluginever.com/plugins/wp-content-pilot-pro/?utm_source=admin-notice&utm_campaign=getpro&utm_medium=admin-dashboard" class="button button-pro promo-btn" target="_blank">Upgrade to Pro</a>';

		self::add_dismissible_notice( $notice, array( 'type' => 'native notice-info', 'dismiss_class' => 'upgrade_notice' ) );
	}

	/**
	 * Add 'spinner_support' notice.
	 *
	 * @since  1.2.6
	 */
	public static function spinner_notice() {
		$notice = sprintf( __( 'The most wanted feature <b>article spinner</b> is now available with <b>WP Content Pilot</b>. We have integrated spinrewriter support. If you do not have account %ssignup now%s and configure in settings page.', 'wp-content-pilot' ), '<a href="https://bit.ly/spinrewriterpluginever" target="_blank">', '</a>' );
		self::add_dismissible_notice( $notice, array( 'type' => 'native notice-info', 'dismiss_class' => 'spinner_notice' ) );
	}

	/**
	 * Add 'article_notice' notice
	 *
	 * @since 1.3.2
	*/
   public static function article_notice() {
	   $notice = __( 'Article search options will be changed in the next version of WP Content Pilot. Bing search will be replaced with Google Custom Search.', 'wp-content-pilot' );
	   self::add_dismissible_notice( $notice, array( 'type' => 'native notice-info', 'dismiss_class' => 'article_notice' ) );
   }




}

add_action( 'admin_init', array( 'WPCP_Admin_Notices', 'init' ), -1 );
//WPCP_Admin_Notices::init();
