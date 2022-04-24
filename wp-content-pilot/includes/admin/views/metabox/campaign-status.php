<?php
defined( 'ABSPATH' ) || exit();
global $post;

$run_campaign_url = add_query_arg( array(
	'action'      => 'wpcp_run_campaign',
	'campaign_id' => $post->ID,
	'nonce'       => wp_create_nonce( 'wpcp_run_campaign' )
), esc_url( admin_url( 'admin-post.php' ) ) );
$last_run         = wpcp_get_post_meta( $post->ID, '_last_run', 0 );
if ( $last_run ) {
	$last_run = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_run ) );
}
$campaign_type = wpcp_get_post_meta( $post->ID, '_campaign_type', '' );
$status        = wpcp_get_post_meta( $post->ID, '_campaign_status', '' );
$last_post     = wpcp_get_post_meta( $post->ID, '_last_post', '' );
if ( ! empty( $last_post ) && $last_post = get_post( $last_post ) ) {
	$last_post = get_post( $last_post );
}
?>
<div class="wpcp-campaign-status-wrap">
	<div class="wpcp-campaign-statue-item">
		<?php echo sprintf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', __( 'Status', 'wp-content-pilot' ) ); ?>
		<?php echo $status == 'active' ? 'Active' : 'Deactivated' ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php echo sprintf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', __( 'Campaign Type', 'wp-content-pilot' ) ); ?>
		<?php echo empty( $campaign_type ) ? '&mdash;' : ucfirst( $campaign_type ); ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php echo sprintf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', __( 'Last Run', 'wp-content-pilot' ) ); ?>
		<?php echo empty( $last_run ) ? '&mdash;' : $last_run; ?>
	</div>
	<div class="wpcp-campaign-statue-item wpcp-last-article-link">
		<?php echo sprintf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', __( 'Last Post', 'wp-content-pilot' ) ); ?>
		<?php
		if ( empty( $last_post ) ) {
			echo '&mdash;';
		} else {
			echo sprintf( '<a href="%s" target="_blank">%s</a>', get_permalink( $last_post ), wp_trim_words( get_the_title( $last_post ), '4' ) );
		}
		?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php echo sprintf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', __( 'Run Campaign', 'wp-content-pilot' ) ); ?>
		<span class="spinner" style="float: none;margin-left: 0;display: none;"></span>
		<?php echo sprintf( '<a id="wpcp-run-campaign" class="button button-secondary" href="%s" data-campaign_id="%d" data-instance="%d">%s</a>', esc_url( $run_campaign_url ), $post->ID, current_time( 'timestamp' ), __( 'Run Now', 'wp-content-pilot' ) ); ?>
	</div>
</div>
