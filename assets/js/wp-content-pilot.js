/**
 * WP Content Pilot Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2019 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */
/*global confirm:false */
/*global wpcp_logger_offset:false */
/*global wp_content_pilot_i10n:false */
jQuery(document).ready(function ($) {
	'use strict';
	$.wp_content_pilot = {

		init: function () {
			$('.wpcp-tooltip').tipTip({
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200
			});
			$('.wpcp-range-slider').ionRangeSlider({
				skin: 'round'
			});

			$('.wpcp-select2').select2({
				theme: 'default wpcp-select2'
			});
			//position log metabox
			$('#wpcp-campaign-log').insertAfter($('#wpcp-campaign-status'));
		},
		hideKeywordField: function () {
			var keywordInput = $('#_keywords');
			var suggestionInput = $('#_keyword_suggestion');
			var keywordWrapper = keywordInput.closest('p._keywords-field');
			var suggestionWrapper = suggestionInput.closest('p._keyword_suggestion-field');
			keywordInput.attr('disabled', 'disabled');
			suggestionInput.attr('disabled', 'disabled');
			keywordWrapper.hide();
			suggestionWrapper.hide();
		},
		showKeywordField: function () {
			var keywordInput = $('#_keywords');
			var suggestionInput = $('#_keyword_suggestion');
			var keywordWrapper = keywordInput.closest('p._keywords-field');
			var suggestionWrapper = suggestionInput.closest('p._keyword_suggestion-field');
			keywordInput.removeAttr('disabled');
			suggestionInput.removeAttr('disabled');
			keywordWrapper.show();
			suggestionWrapper.show();
		},
		youtube: function () {
			$('#_youtube_search_type').on('change', function () {
				var playlistField = $('._youtube_playlist_id-field');
				var channelField = $('._youtube_channel_id-field');
				console.log($(this).val());
				if ('global' === $(this).val()) {
					playlistField.hide();
					channelField.hide();
					$.wp_content_pilot.showKeywordField();
				} else if ('channel' === $(this).val()) {
					channelField.show();
					playlistField.hide();
					$.wp_content_pilot.hideKeywordField();
				} else {
					playlistField.show();
					channelField.hide();
					$.wp_content_pilot.hideKeywordField();
				}
			}).change();
		},
		deleteCampaignPosts: function (e) {
			e.preventDefault();

			if (!confirm('Are you sure?')) {
				return;
			}

			var $el = $(this),
				spinner = $(this).next(),
				camp_id = $el.data('campid'),
				nonce = $el.data('nonce');

			$el.attr('disabled', true);
			spinner.addClass('active');
			$el.attr('disabled', true);
			spinner.addClass('active');

			wp.ajax.send({
				data: {
					action: 'wpcp_delete_all_campaign_posts',
					camp_id: camp_id,
					nonce: nonce
				},
				success: function () {
					spinner.removeClass('active');
					$el.attr('disabled', false);
					spinner.removeClass('active');
					$el.attr('disabled', false);
					window.location.reload();
				},
				error: function (error) {
					$el.attr('disabled', false);
					console.log(error);
				}
			});
		},
		clearLogs: function (e) {
			e.preventDefault();

			if (!confirm('Are you sure?')) {
				return;
			}

			var $el = $(this),
				nonce = $el.data('nonce');
			wp.ajax.send({
				data: {
					action: 'wpcp_clear_logs',
					nonce: nonce
				},
				success: function () {
					$el.attr('disabled', false);
					window.location.reload();
				},
				error: function (error) {
					$el.attr('disabled', false);
					console.log(error);
				}
			});
		},
		handle_manual_campaign: function (e) {
			e.preventDefault();
			var $button = $(this);
			$button.hide();
			$button.prev('.spinner').show().addClass('is-active');
			$button.attr('disabled', 'disabled');
			var $metabox = $('#wpcp-campaign-log');
			var $log_list = $metabox.find('.wpcp-campaign-log-list');
			var campaign_id = parseInt($button.attr('data-campaign_id'), 10);
			var instance = parseInt($button.attr('data-instance'), 10);
			var link_wrapper = $('.wpcp-last-article-link');
			var request, logger;
			window.wpcp_interval;
			window.wpcp_logger_offset = 0;
			window.wpcp_run_instance = instance;
			$log_list.html('');

			$metabox.fadeIn('slow');

			request = wp.ajax.post('wpcp_run_manual_campaign', {
				'nonce': wp_content_pilot_i10n.nonce,
				'campaign_id': campaign_id,
				'instance': instance
			});

			window.wpcp_interval = setInterval(function () {
				logger = wp.ajax.post('wpcp_get_campaign_instance_log', {
					'nonce': wp_content_pilot_i10n.nonce,
					'campaign_id': campaign_id,
					'instance': instance,
					'offset': wpcp_logger_offset
				});

				logger.always(function (logs) {
					window.wpcp_logger_offset += logs.length;
					if (logs.length) {
						logs.forEach(function (log) {
							$log_list.append(build_log_line(log));
						});
						$log_list.scrollTop($log_list.height());
					}
				});

				if (!window.wpcp_run_instance) {
					clearInterval(window.wpcp_interval);
					window.wpcp_logger_offset = 0;
				}

			}, 500);

			request.always(function (response) {
				$button.removeAttr('disabled');
				$button.prev('.spinner').hide();
				$button.show();
				$button.attr('data-instance', Math.floor(Date.now() / 1000));
				$log_list.append(build_log_line(response));
				if (response.link) {
					link_wrapper.find('a').remove();
					link_wrapper.append(response.link);
					link_wrapper.find('a').addClass('wpcp-blink');
				}

				delete window.wpcp_run_instance;
			});

			function build_log_line(data) {
				if (!data.message) {
					return '';
				}
				return $('<p>').addClass(data.level).html('[' + data.time + '] - ' + data.message + '');
			}

		}

	};
	$.wp_content_pilot.init();
	$.wp_content_pilot.youtube();
	$('#wpcp-delete-campaign-posts').on('click', $.wp_content_pilot.deleteCampaignPosts);
	$('#wpcp-clear-logs').on('click', $.wp_content_pilot.clearLogs);
	$('#wpcp-run-campaign').on('click', $.wp_content_pilot.handle_manual_campaign);
});
