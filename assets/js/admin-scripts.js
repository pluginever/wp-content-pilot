/**
 * WP Content Pilot Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2019 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */
jQuery(document).ready(function ($, window, document, undefined) {
	'use strict';
	$.wp_content_pilot = {
		init: function () {
			$('#_campaign_type').on('change', this.triggerCampaignTypeChange);
			$('body').bind('campaign_type_changed', this.getCampaignOptions);
			$('body').bind('campaign_type_changed', this.getCampaignTemplateTags);
		},
		initPlugins: function () {
			$('.ever-select-chosen').chosen({
				inherit_select_classes: true,
				// placeholder_text_single: edd_vars.one_option,
				// placeholder_text_multiple: edd_vars.one_or_more_option,
			});
		},
		triggerCampaignTypeChange: function () {
			var campaign_type = $(this).val();
			var post_id = $('#post_ID').val();
			if (campaign_type && post_id) {
				var data = {
					campaign_type: campaign_type,
					post_id: post_id,
					type: campaign_type,
				};
				$('body').trigger('campaign_type_changed', data);
			}
		},
		getCampaignOptions: function (e, data) {
			wp.ajax.send({
				data: {
					action: 'wpcp_get_campaign_options_metabox_content',
					campaign_type: data.type,
					post_id: data.post_id,
					nonce: '',
				},
				success: function (res) {
					$('#campaign-options .inside').html(res);
				},
				error: function (error) {
					alert('Something happend wrong');
					console.log(error);
				}
			});
		},
		getCampaignTemplateTags: function (e, data) {
			wp.ajax.send({
				data: {
					action: 'wpcp_get_campaign_template_tags_metabox_content',
					campaign_type: data.type,
					post_id: data.post_id,
					nonce: '',
				},
				success: function (res) {
					$('#campaign-template-tags .inside').html(res);
				},
				error: function (error) {
					alert('Something happend wrong');
					console.log(error);
				}
			});
		}
	};


	$.wp_content_pilot.init();
	$.wp_content_pilot.initPlugins();
});
