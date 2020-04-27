<?php
defined( 'ABSPATH' ) || exit();

class WPCP_Promotion {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'promotional_offer' ) );
		add_action( 'wp_ajax_wpcp-dismiss-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );
	}

	/**
	 *
	 * since 1.0.0
	 */
	public function promotional_offer() {
		// Show only to Admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( defined( 'WPCP_PRO_VERSION' ) ) {
			return;
		}

		// check if it has already been dismissed
		$hide_notice = get_option( sprintf( 'wpcp_promotion_%s', content_pilot()->get_version() ), 'no' );

		if ( 'hide' == $hide_notice ) {
			return;
		}

		$button = '<a href="https://www.pluginever.com/plugins/wp-content-pilot-pro/?utm_source=admin-notice&utm_campaign=getpro&utm_medium=admin-dashboard"
				   class="button button-pro promo-btn" target="_blank">Upgrade to Pro</a>';
		$message =__('<b>WP Content Pilot</b> is powering <b>2000+ companies</b> in generating automatic contents and affiliation with its <b>20+</b> types of campaign. Upgrade to Pro now & get 10% discount using coupon <strong>WPCPFREE2PRO</strong>', 'wp-content-pilot')
		?>
		<div class="notice notice-info is-dismissible" id="wpcp-promotional-offer-notice">
			<p>
				<?php echo $message; ?>
				&nbsp;
				<?php echo $button; ?>
			</p>
		</div><!-- #wpcp-promotional-offer-notice -->

		<script type='text/javascript'>
			jQuery('body').on('click', '#wpcp-promotional-offer-notice .notice-dismiss', function (e) {
				e.preventDefault();

				wp.ajax.post('wpcp-dismiss-promotional-offer-notice', {
					dismissed: true
				});
			});
		</script>
		<?php
	}


	/**
	 * Dismiss promotion notice
	 *
	 * @return void
	 * @since  2.5
	 *
	 */
	public function dismiss_promotional_offer() {
		if ( ! empty( $_POST['dismissed'] ) ) {
			$offer_key = 'wpcp_pro_free2pro_promotion_1_2_0';
			update_option( $offer_key, 'hide' );
		}
	}
}

new WPCP_Promotion();
