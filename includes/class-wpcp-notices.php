<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class WPCP_Admin_Notices
 *
 * @since 1.0.0
 */
class WPCP_Admin_Notices {

	/**
	 * Notices.
	 *
	 * @var array $notices Notices.
	 *
	 * @since 1.0.0
	 */
	private static $notices = array();

	/**
	 * Saved notices.
	 *
	 * @var array $saved_notices Saved notices.
	 *
	 * @since 1.0.0
	 */
	private static $saved_notices = array();

	/**
	 * Dismissible notices.
	 *
	 * @var array $dismissible_notices Dismissible notices.
	 *
	 * @since 1.0.0
	 */
	private static $dismissible_notices = array();

	/**
	 * Dismissed notices.
	 *
	 * @var array $dismissed_notices Dismissed notices.
	 *
	 * @since 1.0.0
	 */
	private static $dismissed_notices = array();

	/**
	 * Predefined notices.
	 *
	 * @var array $predefined_notices Predefined notices.
	 *
	 * @since 1.0.0
	 */
	private static $predefined_notices = array(
		'black_friday_2025' => 'black_friday_2025',
		// 'upgrade_notice' => 'upgrade_notice',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		// Set already dismissed notices.
		$dismissed_notices       = get_user_meta( get_current_user_id(), 'wpcp_dismissed_notices', true );
		self::$dismissed_notices = empty( $dismissed_notices ) || ! is_array( $dismissed_notices ) ? array() : $dismissed_notices;

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		// Dismiss notice.
		add_action( 'wp_ajax_wpcp_dismiss_notice', array( __CLASS__, 'dismiss_notice' ) );

		// Output notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );

		// Show maintenance notices.
		add_action( 'admin_init', array( __CLASS__, 'predefined_notices' ) );

		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ), 100 );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.2.6
	 * @return void
	 */
	public static function enqueue_scripts() {
		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_add_inline_script(
				'wp-content-pilot',
				"
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
				"
			);
		}
	}

	/**
	 * Dismiss notice on user action.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function dismiss_notice() {
		$failure = array(
			'result' => 'failure',
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

		$dismissed = self::dismiss_dismissible_notice( sanitize_text_field( wp_unslash( $_POST['notice'] ) ) );

		if ( ! $dismissed ) {
			wp_send_json( $failure );
		}

		$response = array(
			'result' => 'success',
		);

		wp_send_json( $response );
	}

	/**
	 * Output all available notices.
	 *
	 * @since 1.2.6
	 * @return void
	 */
	public static function output_notices() {
		$saved_notices       = get_option( 'wpcp_admin_notices', array() );
		$dismissible_notices = get_option( 'wpcp_dismissible_notices', array() );
		$notices             = $saved_notices + $dismissible_notices + self::$notices;

		// Output the notices.
		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$notice_classes = array( 'wpcp_notice', 'notice', 'notice-' . $notice['type'] );
				$dismiss_attr   = $notice['dismiss_class'] ? 'data-dismiss_class=' . $notice['dismiss_class'] : '';

				if ( $notice['dismiss_class'] ) {
					$notice_classes[] = $notice['dismiss_class'];
					$notice_classes[] = 'is-dismissible';
				}

				echo '<div class="' . esc_html( implode( ' ', $notice_classes ) ) . '"' . esc_html( $dismiss_attr ) . '>';
				echo wp_kses_post( wpautop( $notice['content'] ) );
				echo '</div>';
			}

			// Clear.
			delete_option( 'wpcp_admin_notices' );
		}
	}

	/**
	 * Predefined notices.
	 *
	 * @since 1.2.6
	 * @return void
	 */
	public static function predefined_notices() {

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
	 * @return void
	 */
	public static function save_notices() {
		update_option( 'wpcp_admin_notices', self::$saved_notices );
		update_option( 'wpcp_dismissible_notices', self::$dismissible_notices );
	}

	/**
	 * Add a notice/error.
	 *
	 * @param string  $text Text.
	 * @param mixed   $args Array of arguments.
	 * @param boolean $save_notice Notice text.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_notice( $text, $args = array( 'type' => 'success' ), $save_notice = false ) {
		if ( is_array( $args ) ) {
			$type          = $args['type'];
			$dismiss_class = $args['dismiss_class'] ?? false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class,
		);

		if ( $dismiss_class && ! self::is_dismissible_notice_dismissed( $dismiss_class ) ) {
			self::$dismissible_notices[] = $notice;
		} elseif ( $save_notice ) {
			self::$saved_notices[] = $notice;
		} else {
			self::$notices[] = $notice;
		}
	}

	/**
	 * Add a dismissible notice/error.
	 *
	 * @param string $text Text.
	 * @param mixed  $args Array of arguments.
	 *
	 * @since  1.2.6
	 * @return void
	 */
	public static function add_dismissible_notice( $text, $args ) {
		if ( isset( $args['dismiss_class'] ) || ! self::is_dismissible_notice_dismissed( $args['dismiss_class'] ) ) {
			self::add_notice( $text, $args );
		}
	}

	/**
	 * Checks if a dismissible notice has been dismissed in the past.
	 *
	 * @param string $notice_name The name of the notice.
	 *
	 * @since  1.2.6
	 * @return boolean
	 */
	public static function is_dismissible_notice_dismissed( $notice_name ) {
		return in_array( $notice_name, self::$dismissed_notices, true );
	}

	/**
	 * Remove a dismissible notice.
	 *
	 * @param string $notice_name The name of the notice.
	 *
	 * @since  1.2.6
	 * @return bool
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
	 * @return void
	 */
	public static function upgrade_notice() {
		if ( defined( 'WPCP_PRO_VERSION' ) ) {
			return;
		}
		$notice  = __( '<b>Content Pilot</b> is powering <b>5000+ companies</b> in generating automatic contents and affiliation with its <b>25+</b> types of campaign. Upgrade to Pro now & get 10% discount using coupon <strong>WPCPFREE2PRO</strong>', 'wp-content-pilot' );
		$notice .= '  <a href="https://wpcontentpilot.com/?utm_source=admin-notice&utm_campaign=getpro&utm_medium=admin-dashboard" class="button button-pro promo-btn" target="_blank">Upgrade to Pro</a>';

		self::add_dismissible_notice(
			$notice,
			array(
				'type'          => 'native notice-info',
				'dismiss_class' => 'upgrade_notice',
			)
		);
	}

	/**
	 * Add 'black_friday_2025' notice.
	 *
	 * @since  2.1.6
	 * @return void
	 */
	public static function black_friday_2025() {
		if ( defined( 'WPCP_PRO_VERSION' ) ) {
			return;
		}

		// Black Friday offer notice.
		$current_time          = absint( wp_date( 'U' ) );
		$black_friday_end_time = strtotime( '2025-12-05 00:00:00' );
		if ( $current_time > $black_friday_end_time ) {
			return;
		}
		$notice  = __( '<b>🖤 Black Friday Mega Sale!</b> Enjoy 40% OFF on all Content Pilot Pro plans. Use coupon code <strong>BFCM25</strong> at checkout. Don\'t miss out on this limited-time offer! 🛍️ &nbsp;&nbsp;', 'wp-content-pilot' );
		$notice .= '&nbsp;<a href="https://wpcontentpilot.com/pricing/?utm_source=admin-notice&utm_campaign=black_friday_2025&utm_medium=admin-dashboard&discount=BFCM25" class="button button-pro promo-btn" target="_blank">Claim Your Discount</a>';
		self::add_dismissible_notice(
			$notice,
			array(
				'type'          => 'native notice-info',
				'dismiss_class' => 'black_friday_2025',
			)
		);
	}

	/**
	 * Add 'spinner_support' notice.
	 *
	 * @since  1.2.6
	 * @return void
	 */
	public static function spinner_notice() {
		$notice = sprintf( /* translators: 1. HTML anchor tag, 2. HTML anchor end tag */ __( 'The most wanted feature <b>article spinner</b> is now available with <b>Content Pilot</b>. We have integrated spinrewriter support. If you do not have account %1$ssignup now%2$s and configure in settings page.', 'wp-content-pilot' ), '<a href="https://bit.ly/spinrewriterpluginever" target="_blank">', '</a>' );
		self::add_dismissible_notice(
			$notice,
			array(
				'type'          => 'native notice-info',
				'dismiss_class' => 'spinner_notice',
			)
		);
	}
}

add_action( 'admin_init', array( 'WPCP_Admin_Notices', 'init' ), -1 );
