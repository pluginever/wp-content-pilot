<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WPCP_Promotion {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'promotional_offer' ) );
		add_action( 'wp_ajax_wpcp-dismiss-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );
		add_action( 'admin_menu', array( $this, 'get_pro_link' ), 502 );
		add_action( 'admin_init', array( $this, 'go_pro_redirect' ) );

	}

	public function get_pro_link() {
		if ( ! defined( 'WPCP_PRO_VERSION' ) ) {
			add_submenu_page(
				'edit.php?post_type=wp_content_pilot',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wp-content-pilot' ) . '</span>',
				'manage_options',
				'go_wpcp_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wpcp_pro' === $_GET['page'] ) {
			wp_redirect(  'https://www.pluginever.com/plugins/woocommerce-category-showcase-pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
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

		// 2018-03-26 23:59:00
//		if ( time() > strtotime('30-4-2018') ) {
//			return;
//		}

		// check if it has already been dismissed
		$hide_notice = get_option( 'wpcp_pro_release_announcement', 'no' );

		if ( 'hide' == $hide_notice ) {
			return;
		}

		$offer_msg = sprintf( __( '<p><strong class="highlight-text" style="font-size: 18px">We are going to rebuild the plugin
									
                                        <a target="_blank" href="%1$s"><strong> Birthday2018 </strong></a>
                                        <br>
                                        Offer ending soon!
                                    </p>', 'wpcp' ), 'https://pluginever.com' );

		?>
		<div class="notice is-dismissible" id="wpcp-promotional-offer-notice">
			<table>
				<tbody>
				<tr>
					<td class="image-container">
						<img src="<?php echo WPCP_ASSETS_URL.'/images/roboto-logo.svg';?>" alt="">
					</td>
					<td class="message-container">
						<?php echo $offer_msg; ?>
					</td>
				</tr>
				</tbody>
			</table>

			<span class="dashicons dashicons-megaphone"></span>
			<a href="https://wpwpcp.com/in/wordpress-wpcp-3rd-birthday" class="button button-primary promo-btn" target="_blank"><?php _e( 'Get the Offer', 'wpcp' ); ?></a>
		</div><!-- #wpcp-promotional-offer-notice -->

		<style>
			#wpcp-promotional-offer-notice {
				background-color: #00aeef;
				border: 0px;
				padding: 0;
				opacity: 0;
			}

			.wrap > #wpcp-promotional-offer-notice {
				opacity: 1;
			}

			#wpcp-promotional-offer-notice table {
				border-collapse: collapse;
				width: 100%;
			}

			#wpcp-promotional-offer-notice table td {
				padding: 0;
			}

			#wpcp-promotional-offer-notice table td.image-container {
				background-color: #fff;
				vertical-align: middle;
				width: 95px;
			}


			#wpcp-promotional-offer-notice img {
				max-width: 100%;
				max-height: 100px;
				vertical-align: middle;
			}

			#wpcp-promotional-offer-notice table td.message-container {
				padding: 0 10px;
			}

			#wpcp-promotional-offer-notice h2{
				color: rgba(250, 250, 250, 0.77);
				margin-bottom: 10px;
				font-weight: normal;
				margin: 16px 0 14px;
				-webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
			}


			#wpcp-promotional-offer-notice h2 span {
				position: relative;
				top: 0;
			}

			#wpcp-promotional-offer-notice p{
				color: rgba(250, 250, 250, 0.77);
				font-size: 14px;
				margin-bottom: 10px;
				-webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
			}

			#wpcp-promotional-offer-notice p strong.highlight-text{
				color: #fff;
			}

			#wpcp-promotional-offer-notice p a {
				color: #fafafa;
			}

			#wpcp-promotional-offer-notice .notice-dismiss:before {
				color: #fff;
			}

			#wpcp-promotional-offer-notice span.dashicons-megaphone {
				position: absolute;
				bottom: 46px;
				right: 248px;
				color: rgba(253, 253, 253, 0.29);
				font-size: 96px;
				transform: rotate(-21deg);
			}

			#wpcp-promotional-offer-notice a.promo-btn{
				background: #fff;
				border-color: #fafafa #fafafa #fafafa;
				box-shadow: 0 1px 0 #fafafa;
				color: #4caf4f;
				text-decoration: none;
				text-shadow: none;
				position: absolute;
				top: 30px;
				right: 26px;
				height: 40px;
				line-height: 40px;
				width: 130px;
				text-align: center;
				font-weight: 600;
			}

		</style>

		<script type='text/javascript'>
			jQuery('body').on('click', '#wpcp-promotional-offer-notice .notice-dismiss', function(e) {
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
	 * @since  2.5
	 *
	 * @return void
	 */
	public function dismiss_promotional_offer() {
		if ( ! empty( $_POST['dismissed'] ) ) {
			$offer_key = 'wpcp_pro_release_announcement';
			update_option( $offer_key, 'hide' );
		}
	}
}

new WPCP_Promotion();
