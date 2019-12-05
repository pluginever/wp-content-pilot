<?php
defined('ABSPATH')|| exit();
$can_publish = true;
global $post_id;
$action = empty( $_GET['action'] ) ? '' : esc_attr( $_GET['action'] );
?>
<div id="submitpost" class="submitbox ever-submitbox">
	<div id="minor-publishing">
		<?php
		do_action('wpcp_before_campaign_action_metabox', $post_id);
		echo content_pilot()->elements->input( array(
			'label'          => __( 'Campaign Target', 'wp-content-pilot' ),
			'name'           => '_campaign_target',
			'type'           => 'number',
			'placeholder'    => '10',
			'value'          => wpcp_get_post_meta( $post_id, '_campaign_target', 5 ),
			'required'       => true,
			'class'       => 'long',
		) );

		echo content_pilot()->elements->select( array(
			'label'            => __( 'Campaign Frequency', 'wp-content-pilot' ),
			'name'             => '_campaign_frequency',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => wpcp_get_campaign_schedule_options(),
			'selected'         => wpcp_get_post_meta( $post_id, '_campaign_frequency' ),
			'required'         => true,
			'class'       => 'long',
		) );

		echo content_pilot()->elements->select( array(
			'label'            => __( 'Campaign Status', 'wp-content-pilot' ),
			'name'             => '_campaign_status',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => array(
				'active'   => __( 'Active', 'wp-content-pilot' ),
				'inactive' => __( 'Inactive', 'wp-content-pilot' ),
			),
			'required'         => true,
			'class'       => 'long',
			'selected'         => wpcp_get_post_meta( $post_id, '_campaign_status' ),
		) );

		echo content_pilot()->elements->input( apply_filters( 'wpcp_readability_score_meta', array(
			'label'          => __( 'Readability Score', 'wp-content-pilot' ),
			'name'           => '_readability_score',
			'type'           => 'number',
			'placeholder'    => '40',
			'desc'           => __( 'Min readability score required to post (PRO)', 'wp-content-pilot' ),
			'value'          => wpcp_get_post_meta( $post_id, '_readability_score', 0 ),
			'disabled'       => true,
			'class'       => 'long',
		), $post_id ) );

		do_action('wpcp_after_campaign_action_metabox', $post_id);
		?>
	</div>
	<div id="major-publishing-actions">

		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
			<div id="delete-action">
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( esc_attr( $_REQUEST['post'] ) ) ?>" title="<?php _e( 'Move to Trash', 'wp-content-pilot' ); ?>">
					<button type="button" class="button button-link-delete"><?php _e( 'Trash', 'wp-content-pilot' ); ?></button>
				</a>
			</div>
		<?php } ?>

		<?php if ( ! empty( $_REQUEST['post'] ) ) { ?>
			<?php
			$reset_search_url = add_query_arg(array(
				'action' => 'wpcp_campaign_reset_search',
				'campaign_id' => intval($_REQUEST['post']),
				'nonce' => wp_create_nonce('wpcp_campaign_reset_search')
			),esc_url( admin_url('admin-post.php') ))
			?>
			<a href="<?php echo esc_url( $reset_search_url ); ?>" class="button"><?php _e( 'Reset', 'wp-content-pilot' ); ?></a>
		<?php } ?>


		<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if ( $action !== 'edit' ) {
				if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'wp-content-pilot' ) ?>"/>
					<?php submit_button( __( 'Create Campaign', 'wp-content-pilot' ), 'primary button-large', 'publish', false ); ?><?php
				endif;
			} else { ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'wp-content-pilot' ) ?>"/>
				<div class="publishing-action-btn">
					<?php
					$test_run_url = add_query_arg(array(
						'action' => 'wpcp_campaign_test_run',
						'campaign_id' => intval($_REQUEST['post']),
						'nonce' => wp_create_nonce('wpcp_campaign_test_run')
					),esc_url( admin_url('admin-post.php') ))
					?>
					<a href="<?php echo esc_url( $test_run_url ); ?>" class="button"><?php _e( 'Test Run', 'wp-content-pilot' ); ?></a>
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update Campaign', 'wp-content-pilot' ) ?>"/>
				</div>
				<?php
			} ?>
		</div>

		<div class="clear"></div>
	</div>
</div>
