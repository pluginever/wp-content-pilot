/**
 * WP Content Pilot - v1.0.0 - 2018-02-03
 * http://pluginever.com
 *
 * Copyright (c) 2018;
 * Licensed GPLv2+
 */
/*jslint browser: true */
/*global jQuery:false */

window.Wp_Content_Pilot = (function(window, document, $, undefined){
	'use strict';

	var app = {};

	app.init = function() {
	    console.log('init');
        app.update_template_tags();
        $('#_campaign_type').on('change', app.update_template_tags);
	};

	app.update_template_tags = function () {
	    var campaign_type = $('#_campaign_type').val();
        $.post(
            ajaxurl,
            {
                'action': 'wpcp_get_template_tags',
                'data':   {type:campaign_type}
            },
            function(response){
                if(response.data){
                    var tags = response.data;
                    var html = '';
                    console.log(tags);
                    $.each(tags, function (index, tag) {
                        html += '<code>{'+tag+'}</code>, ';
                    });

                    $('.wpcp-supported-tags').html(html);
                    console.log(html);
                }
            }
        );
    };


	$(document).ready( app.init );


	return app;

})(window, document, jQuery);
