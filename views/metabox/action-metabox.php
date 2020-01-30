<?php
defined( 'ABSPATH' ) || exit();
$can_publish = true;
global $post_id;
$action        = empty( $_GET['action'] ) ? '' : esc_attr( $_GET['action'] );
$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );

?>
<div id="submitpost" class="submitbox wpcp-submitbox">
	<?php
	if ( ! empty( $campaign_type ) ) {
		echo wpcp_range_input( array(
			'name'       => '_campaign_target',
			'id'         => '_campaign_target',
			'label'      => __( 'Campaign Target', 'wp-content-pilot' ),
			'default'    => 5,
			'attributes' => array(
				'required' => 'required'
			),
		) );

		echo wpcp_range_input( array(
			'name'       => '_campaign_frequency',
			'id'         => '_campaign_frequency',
			'label'      => __( 'Campaign Frequency', 'wp-content-pilot' ),
			'default'    => 10,
			'min'        => '1',
			'max'        => '100',
			'attributes' => array(
				'required' => 'required',
//			'data-prefix' => __( 'Every ', 'wp-content-pilot' ),
			),
		) );

		echo wpcp_select_input( array(
			'label'         => __( 'Frequency Unit', 'wp-content-pilot' ),
			'name'          => '_frequency_unit',
			'options'       => apply_filters( 'wpcp_campaign_post_types', array(
				'minutes' => __( 'Minutes', 'wp-content-pilot' ),
				'hours'   => __( 'Hours', 'wp-content-pilot' ),
				'days'    => __( 'Days', 'wp-content-pilot' ),
			) ),
			'default'       => 'hours',
			'required'      => true,
			'wrapper_class' => 'pro',
			'attributes'    => array(
				'disabled' => 'disabled',
			)
		) );

		echo wpcp_switch_input( array(
			'label' => __( 'Campaign Status', 'wp-content-pilot' ),
			'name'  => '_campaign_status',
			'desc'  => __( 'On', 'wp-content-pilot' ),
		) );

	}
	?>
	<div id="major-publishing-actions">

		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
			<div id="delete-action">
				<a class="submitdelete deletion"
				   href="<?php echo get_delete_post_link( esc_attr( $_REQUEST['post'] ) ) ?>"
				   title="<?php _e( 'Move to Trash', 'wp-content-pilot' ); ?>">
					<button type="button"
					        class="button-link button-link-delete"><?php _e( 'Trash', 'wp-content-pilot' ); ?></button>
				</a>
			</div>
		<?php } ?>

		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
			<?php
			$reset_search_url = add_query_arg( array(
				'action'      => 'wpcp_campaign_reset_search',
				'campaign_id' => intval( $_REQUEST['post'] ),
				'nonce'       => wp_create_nonce( 'wpcp_campaign_reset_search' )
			), esc_url( admin_url( 'admin-post.php' ) ) )
			?>
			<a href="<?php echo esc_url( $reset_search_url ); ?>"
			   class="button-link"><?php _e( 'Reset', 'wp-content-pilot' ); ?></a>
		<?php } ?>

		<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>

		<div id="publishing-action">
			<?php
			if ( $action !== 'edit' ) {
				if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish"
					       value="<?php esc_attr_e( 'Publish', 'wp-content-pilot' ) ?>"/>
					<?php submit_button( __( 'Create Campaign', 'wp-content-pilot' ), 'primary button-large', 'publish', false ); ?><?php
				endif;
			} else { ?>
				<input name="original_publish" type="hidden" id="original_publish"
				       value="<?php esc_attr_e( 'Update', 'wp-content-pilot' ) ?>"/>
				<div class="publishing-action-btn">
					<input name="save" type="submit" class="button button-primary button-large" id="publish"
					       value="<?php esc_attr_e( 'Update Campaign', 'wp-content-pilot' ) ?>"/>
				</div>
				<?php
			} ?>
		</div>

		<div class="clear"></div>
	</div>
</div>
