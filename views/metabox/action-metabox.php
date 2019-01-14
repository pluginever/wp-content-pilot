<?php
$can_publish = true;
$action      = empty($_GET['action']) ? '' : esc_attr($_GET['action']);
?>
<div id="submitpost" class="submitbox ever-submitbox">
	<div id="minor-publishing">
		<?php

		echo content_pilot()->elements->input(array(
			'label'       => __('Campaign Target', 'wp-content-pilot'),
			'name'        => '_campaign_target',
			'type'        => 'number',
			'placeholder' => '10',
			'required'    => true,
		));

		echo content_pilot()->elements->select(array(
			'label'            => __('Campaign Frequency', 'wp-content-pilot'),
			'name'             => '_campaign_frequency',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => wpcp_get_campaign_schedule_options(),
			'required'         => true,
			'selected'         => '',
		));

		echo content_pilot()->elements->select(array(
			'label'            => __('Campaign Status', 'wp-content-pilot'),
			'name'             => '_campaign_status',
			'show_option_all'  => '',
			'show_option_none' => '',
			'options'          => array(
				'active'   => __('Active', 'wp-content-pilot'),
				'inactive' => __('Inactive', 'wp-content-pilot'),
			),
			'required'         => true,
			'selected'         => '',
		));
		?>
	</div>
	<div id="major-publishing-actions">

		<?php if (!empty($_REQUEST['post'])) { ?>
			<div id="delete-action">
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link(esc_attr($_REQUEST['post'])) ?>" title="<?php _e('Move to Trash', 'wp-content-pilot'); ?>">
					<button type="button" class="button button-link-delete"><?php _e('Trash', 'wp-content-pilot'); ?></button>
				</a>
			</div>
		<?php } ?>

		<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if ($action !== 'edit') {
				if ($can_publish) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>"/>
					<?php submit_button(__('Create Campaign', 'wp-content-pilot'), 'primary button-large', 'publish', false); ?><?php
				endif;
			} else { ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>"/>
				<div class="publishing-action-btn">
					<a href="#">
						<button type="button" class="button"><?php _e('Test run', 'wp-content-pilot'); ?></button>
					</a>
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e('Update Campaign', 'wp-content-pilot') ?>"/>
				</div>
				<?php
			} ?>
		</div>

		<div class="clear"></div>
	</div>
</div>
