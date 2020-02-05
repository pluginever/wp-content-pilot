<?php
defined( 'ABSPATH' ) || exit();
global $post;

$run_campaign_url = add_query_arg( array(
	'action'      => 'wpcp_run_campaign',
	'campaign_id' => $post->ID,
	'nonce'       => wp_create_nonce( 'wpcp_run_campaign' )
), esc_url( admin_url( 'admin-post.php' ) ) );
$last_run     = wpcp_get_post_meta( $post->ID, '_last_run', 0 );
if ( $last_run ) {
	$last_run = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_run );
}
$campaign_type = wpcp_get_post_meta( $post->ID, '_campaign_type', '' );
$status        = wpcp_get_post_meta( $post->ID, '_campaign_status', '' );
$last_post     = wpcp_get_post_meta( $post->ID, '_last_post', '' );
?>
<div class="wpcp-campaign-status-wrap">
	<div class="wpcp-campaign-statue-item">
		<h2 class="wpcp-campaign-statue-title">Status</h2>
		<?php echo $status == 'active' ? 'Active' : 'Deactivated' ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<h2 class="wpcp-campaign-statue-title">Campaign Type</h2>
		<?php echo empty( $campaign_type ) ? '&mdash;' : ucfirst( $campaign_type ); ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<h2 class="wpcp-campaign-statue-title">Last Run</h2>
		<?php echo empty( $last_run ) ? '&mdash;' : $last_run; ?>
	</div>
	<div class="wpcp-campaign-statue-item">
		<h2 class="wpcp-campaign-statue-title">Last Post</h2>
		<?php
		if ( empty( $last_post ) ) {
			echo '&mdash;';
		} else {
			echo sprintf( '<a href="%s" target="_blank">%s</a>', get_permalink( $last_post ), wp_trim_words( get_the_title( $last_post ), '4' ) );
		}
		?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<h2 class="wpcp-campaign-statue-title">Run Campaign</h2>
		<span class="spinner" style="float: none;margin-left: 0;display: none;"></span>
		<a id="wpcp-run-campaign" class="button button-secondary" href="<?php echo esc_url( $run_campaign_url ); ?>">Run Now</a>
	</div>
</div>
