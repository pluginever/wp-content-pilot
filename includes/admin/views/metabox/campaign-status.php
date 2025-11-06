<?php
defined( 'ABSPATH' ) || exit();
global $post;

$last_run = wpcp_get_post_meta( $post->ID, '_last_run', 0 );

if ( $last_run ) {
	$last_run = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_run ) );
}

$campaign_type = wpcp_get_post_meta( $post->ID, '_campaign_type', '' );
$status        = wpcp_get_post_meta( $post->ID, '_campaign_status', '' );
$last_post     = wpcp_get_post_meta( $post->ID, '_last_post', '' );

if ( ! empty( $last_post ) && get_post( $last_post ) ) {
	$last_post = get_post( $last_post );
}
?>
<div class="wpcp-campaign-status-wrap">
	<div class="wpcp-campaign-statue-item">
		<?php printf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', esc_html__( 'Status', 'wp-content-pilot' ) ); ?>
		<?php echo 'active' === $status ? 'Active' : 'Deactivated'; ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php printf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', esc_html__( 'Campaign Type', 'wp-content-pilot' ) ); ?>
		<?php echo empty( $campaign_type ) ? '&mdash;' : esc_html( ucfirst( $campaign_type ) ); ?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php printf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', esc_html__( 'Last Run', 'wp-content-pilot' ) ); ?>
		<?php echo empty( $last_run ) ? '&mdash;' : esc_html( $last_run ); ?>
	</div>

	<div class="wpcp-campaign-statue-item wpcp-last-article-link">
		<?php printf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', esc_html__( 'Last Post', 'wp-content-pilot' ) ); ?>
		<?php
		if ( empty( $last_post ) ) {
			echo '&mdash;';
		} else {
			printf( '<a href="%s" target="_blank">%s</a>', esc_url( get_permalink( $last_post ) ), esc_html( wp_trim_words( get_the_title( $last_post ), '4' ) ) );
		}
		?>
	</div>

	<div class="wpcp-campaign-statue-item">
		<?php printf( '<h2 class="wpcp-campaign-statue-title">%s</h2>', esc_html__( 'Run Campaign', 'wp-content-pilot' ) ); ?>
		<span class="spinner" style="float: none;margin-left: 0;display: none;"></span>
		<?php printf( '<button id="wpcp-run-campaign" class="button button-secondary" data-campaign_id="%d" data-instance="%d">%s</button>', intval( $post->ID ), intval( current_time( 'mysql' ) ), esc_html__( 'Run Now', 'wp-content-pilot' ) ); ?>
	</div>
</div>
