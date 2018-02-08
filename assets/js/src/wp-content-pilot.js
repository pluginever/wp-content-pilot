/**
 * WP Content Pilot
 * http://pluginever.com
 *
 * Copyright (c) 2018 PluginEver
 * Licensed under the GPLv2+ license.
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
