<?php
defined( 'ABSPATH' ) || exit();
global $post;

$reset_search_url = add_query_arg( array(
	'action'      => 'wpcp_campaign_reset_search',
	'campaign_id' => $post->ID,
	'nonce'       => wp_create_nonce( 'wpcp_campaign_reset_search' )
), esc_url( admin_url( 'admin-post.php' ) ) );

$action        = empty( $_GET['action'] ) ? '' : esc_attr( $_GET['action'] );
$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
if ( empty( $campaign_type ) ) {
	$campaign_type = 'feed';
}

$can_publish = true;
?>
<div id="submitpost" class="submitbox wpcp-submitbox">
	<?php

	echo WPCP_HTML::range_input( array(
		'name'    => '_campaign_target',
		'label'   => __( 'Campaign Target', 'wp-content-pilot' ),
		'default' => 5,
		'max'     => is_plugin_active( 'wp-content-pilot-pro/wp-content-pilot-pro.php' ) ? 5000 : 500,
		'attrs'   => array(
			'required' => 'required',
		),
		'tooltip' => __( 'For better precision use keyboard arrows ', 'wp-content-pilot' ),

	) );

	echo WPCP_HTML::range_input( array(
		'name'    => '_campaign_frequency',
		'label'   => __( 'Campaign Frequency', 'wp-content-pilot' ),
		'default' => 10,
		'min'     => '1',
		'max'     => '100',
		'tooltip' => __( 'For better precision use keyboard arrows ', 'wp-content-pilot' ),
	) );

	echo WPCP_HTML::select_input( array(
		'label'         => __( 'Frequency Unit', 'wp-content-pilot' ),
		'name'          => '_frequency_unit',
		'options'       => array(
			'minutes' => __( 'Minutes', 'wp-content-pilot' ),
			'hours'   => __( 'Hours', 'wp-content-pilot' ),
			'days'    => __( 'Days', 'wp-content-pilot' ),
		),
		'default'       => 'hours',
		'required'      => true,
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );

	echo WPCP_HTML::switch_input( array(
		'name'  => '_campaign_status',
		'check' => 'active',
		'label' => __( 'Campaign Status', 'wp-content-pilot' ),
	) );

	?>

    <div id="major-publishing-actions">
		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
            <div id="delete-action">
                <a class="submitdelete deletion"
                   href="<?php echo get_delete_post_link( esc_attr( $_REQUEST['post'] ) ) ?>"
                   title="<?php _e( 'Move to Trash', 'wp-content-pilot' ); ?>">
                    <button type="button" class="button-link button-link-delete">
						<?php _e( 'Trash', 'wp-content-pilot' ); ?>
                    </button>
                </a>
            </div>
		<?php } ?>

		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
            <a href="<?php echo esc_url( $reset_search_url ); ?>" class="button-link">
				<?php _e( 'Reset', 'wp-content-pilot' ); ?>
            </a>
		<?php } ?>
        <div id="publishing-action">
            <input name="original_publish" type="hidden" id="original_publish"
                   value="<?php esc_attr_e( 'Update', 'wp-content-pilot' ) ?>"/>
            <div class="publishing-action-btn">
                <input name="publish" type="submit" class="button button-primary button-large" id="publish"
                       value="<?php esc_attr_e( 'Update Campaign', 'wp-content-pilot' ) ?>"/>
            </div>
        </div>

        <div class="clear"></div>
        <input type="hidden" name="_campaign_type" value="<?php echo esc_attr( $campaign_type ); ?>">
        <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>
    </div>
</div>
