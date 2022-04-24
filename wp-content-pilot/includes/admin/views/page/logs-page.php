<?php
defined( 'ABSPATH') || exit();
require_once WPCP_INCLUDES . '/admin/views/tables/class-wpcp-logs-table.php';
$list_table = new WPCP_Logs_List_Table();
$list_table->prepare_items();
$base_url = admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-logs' );

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Logs', 'wp-content-pilot' ); ?></h1>
	<form method="get" action="<?php echo esc_url( $base_url ); ?>">
		<div class="wpcp-log-table">
			<?php //$list_table->search_box( __( 'Search', 'wp-content-pilot' ), 'eaccounting-contacts' ); ?>
			<input type="hidden" name="post_type" value="wp_content_pilot"/>
			<input type="hidden" name="page" value="wpcp-logs"/>
			<?php $list_table->views() ?>
			<?php $list_table->display() ?>
		</div>
	</form>
</div>
