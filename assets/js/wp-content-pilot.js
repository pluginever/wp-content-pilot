/**
 * WP Content Pilot Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2019 pluginever
 * Licensed under the GPLv2+ license.
 */

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
		},
		youtube: function () {
			$('#_youtube_search_type').on('change', function () {
				var channnelField = $('._youtube_channel_id-field');
				if('global' === $(this).val()){
					channnelField.hide();
				}else{
					channnelField.show();
				}
			}).change();
		}
	};
	$.wp_content_pilot.init();
	$.wp_content_pilot.youtube();
});
