<?php
/**
 * Admin_Menu Class
 *
 * @package     WP Content Pilot
 * @subpackage  Admin_Menu
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

defined('ABSPATH')|| exit();

class WPCP_Admin_Menu {


	/**
	 * Admin_Menu constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'get_pro_link' ), 502 );
		add_action( 'admin_init', array( $this, 'go_pro_redirect' ) );
	}


	function admin_menu() {

		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Status', 'wp-content-pilot' ), __( 'Status', 'wp-content-pilot' ), 'manage_options', 'wpcp-status', array(
			$this,
			'status_page'
		) );

		add_submenu_page( 'edit.php?post_type=wp_content_pilot', __( 'Logs', 'wp-content-pilot' ), __( 'Logs', 'wp-content-pilot' ), 'manage_options', 'wpcp-logs', array(
			$this,
			'logs_page'
		) );

	}

	function status_page(){
		ob_start();
		include WPCP_VIEWS.'/menu/status.php';
		$html = ob_get_clean();

		echo $html;
	}

	public function get_pro_link(){
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

	function logs_page() {
		$log_remove_url = wp_nonce_url( admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' ), 'wpcp_remove_logs', 'wpcp_nonce' );
		?>
		<div class="wrap">
			<h2><?php _e( 'Campaign Log', 'wp-content-pilot' ); ?> <a
					href="<?php echo esc_url( $log_remove_url . "&remove_logs=1" ); ?>"
					class="button button-seconday"><?php _e( 'Clear Logs', 'wp-content-pilot' ); ?></a>
			</h2>

			<form method="post">
				<input type="hidden" name="page" value="ttest_list_table">
				<?php
				require_once ( WPCP_VIEWS .'/tables/log-list-table.php');

				$list_table = new WPCP_Log_List_Table();
				$list_table->prepare_items();
				$list_table->display();
				?>
			</form>

		</div>
		<?php
	}

	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wpcp_pro' === $_GET['page'] ) {
			wp_redirect(  'https://www.pluginever.com/plugins/wp-content-pilot-pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
	}
}

new WPCP_Admin_Menu();
