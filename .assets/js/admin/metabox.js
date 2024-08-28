/* global wpcp_admin_meta_boxes_vars, tb_show, tb_remove */
jQuery( document ).ready( ( $ ) => {
	'use strict';
	$('.wpcp-campaign-settings-tabs').on('click', 'a', function (e) {
		e.preventDefault();
		var $this = $(this);
		var $target = $($this.attr('href'));
		var $tab = $this.closest('li');
		var $tabs = $tab.siblings();
		var $panels = $tab.closest('.wpcp-campaign-settings-tabs').next('.wpcp-campaign-settings-panels').children();

		$tabs.removeClass('active');
		$panels.hide();
		$tab.addClass('active');
		$target.show();
	});

	// Show the first tab by default.
	$('.wpcp-campaign-settings-tabs li').eq(0).find('a').trigger('click');
});
