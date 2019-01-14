<?php
$can_publish = true;
$action = empty($_GET['action'])? '': esc_attr($_GET['action']);
?>
<div id="submitpost" class="submitbox">
	<div id="minor-publishing">
		<?php

		echo content_pilot()->elements->input( array(
			'label'       => __( 'Campaign Target', 'wp-content-pilot' ),
			'name'        => '_campaign_target',
			'type'        => 'number',
			'placeholder' => '10',
			'required'    => true,
		) );

		echo content_pilot()->elements->select( array(
			'label'            => __( 'Campaign Frequency', 'wp-content-pilot' ),
			'name'             => '_campaign_frequency',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => wpcp_get_campaign_schedule_options(),
			'required'         => true,
			'selected'         => '',
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
			'selected'         => '',
		) );
		?>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
			<a class="submitdelete deletion" href="http://wpcontentpilot.test/wp-admin/post.php?post=196&amp;action=trash&amp;_wpnonce=a317267be5">Move to Trash</a>
		</div>
		<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>
		<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if ( $action !== 'edit' ) {
				if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>"/>
					<?php submit_button( __( 'Create Campaign', 'wp-content-pilot' ), 'primary button-large', 'publish', false ); ?>
				<?php
				endif;
			} else { ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>"/>
				<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update Campaign', 'wp-content-pilot' ) ?>"/>
				<?php
			} ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
