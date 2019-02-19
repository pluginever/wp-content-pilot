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
			this.initPlugins();
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
	Ever_Repeatable.init();
});


var Ever_Repeatable = {
	init : function() {
		this.add();
		this.move();
		this.remove();
		this.type();
		this.prices();
		this.files();
		this.updatePrices();
		console.log('init');
	},
	clone_repeatable : function(row) {

		// Retrieve the highest current key
		var key = highest = 1;
		row.parent().find( '.ever_repeatable_row' ).each(function() {
			var current = $(this).data( 'key' );
			if( parseInt( current ) > highest ) {
				highest = current;
			}
		});
		key = highest += 1;

		clone = row.clone();

		clone.removeClass( 'ever_add_blank' );

		clone.attr( 'data-key', key );
		clone.find( 'input, select, textarea' ).val( '' ).each(function() {
			var name = $( this ).attr( 'name' );
			var id   = $( this ).attr( 'id' );

			if( name ) {

				name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
				$( this ).attr( 'name', name );

			}

			$( this ).attr( 'data-key', key );

			if( typeof id != 'undefined' ) {

				id = id.replace( /(\d+)/, parseInt( key ) );
				$( this ).attr( 'id', id );

			}

		});

		/** manually update any select box values */
		clone.find( 'select' ).each(function() {
			$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
		});

		/** manually uncheck any checkboxes */
		clone.find( 'input[type="checkbox"]' ).each(function() {

			// Make sure checkboxes are unchecked when cloned
			var checked = $(this).is(':checked');
			if ( checked ) {
				$(this).prop('checked', false);
			}

			// reset the value attribute to 1 in order to properly save the new checked state
			$(this).val(1);
		});

		clone.find( 'span.edd_price_id' ).each(function() {
			$( this ).text( parseInt( key ) );
		});

		clone.find( 'span.edd_file_id' ).each(function() {
			$( this ).text( parseInt( key ) );
		});

		clone.find( '.edd_repeatable_default_input' ).each( function() {
			$( this ).val( parseInt( key ) ).removeAttr('checked');
		});

		clone.find( '.edd_repeatable_condition_field' ).each ( function() {
			$( this ).find( 'option:eq(0)' ).prop( 'selected', 'selected' );
		});

		// Remove Chosen elements
		clone.find( '.search-choice' ).remove();
		clone.find( '.chosen-container' ).remove();
		//edd_attach_tooltips(clone.find('.edd-help-tip'));

		return clone;
	},

	add : function() {
		$( document.body ).on( 'click', '.submit .edd_add_repeatable', function(e) {
			e.preventDefault();
			var button = $( this ),
				row = button.parent().parent().prev( '.ever_repeatable_row' ),
				clone = Ever_Repeatable.clone_repeatable(row);

			clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

			// Setup chosen fields again if they exist
			// clone.find('.edd-select-chosen').chosen({
			// 	inherit_select_classes: true,
			// 	placeholder_text_single: edd_vars.one_option,
			// 	placeholder_text_multiple: edd_vars.one_or_more_option,
			// });
			// clone.find( '.edd-select-chosen' ).css( 'width', '100%' );
			// clone.find( '.edd-select-chosen .chosen-search input' ).attr( 'placeholder', edd_vars.search_placeholder );
		});
	},

	move : function() {

		$(".edd_repeatable_table .edd-repeatables-wrap").sortable({
			handle: '.edd-draghandle-anchor', items: '.ever_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
				var count  = 0;
				$(this).find( '.ever_repeatable_row' ).each(function() {
					$(this).find( 'input.edd_repeatable_index' ).each(function() {
						$( this ).val( count );
					});
					count++;
				});
			}
		});

	},

	remove : function() {
		$( document.body ).on( 'click', '.edd-remove-row, .edd_remove_repeatable', function(e) {
			e.preventDefault();

			var row   = $(this).parents( '.ever_repeatable_row' ),
				count = row.parent().find( '.ever_repeatable_row' ).length,
				type  = $(this).data('type'),
				repeatable = 'div.edd_repeatable_' + type + 's',
				focusElement,
				focusable,
				firstFocusable;

			// Set focus on next element if removing the first row. Otherwise set focus on previous element.
			if ( $(this).is( '.ui-sortable .ever_repeatable_row:first-child .edd-remove-row, .ui-sortable .ever_repeatable_row:first-child .edd_remove_repeatable' ) ) {
				focusElement  = row.next( '.ever_repeatable_row' );
			} else {
				focusElement  = row.prev( '.ever_repeatable_row' );
			}

			focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
			firstFocusable = focusable.eq(0);

			if ( type === 'price' ) {
				var price_row_id = row.data('key');
				/** remove from price condition */
				$( '.edd_repeatable_condition_field option[value="' + price_row_id + '"]' ).remove();
			}

			if ( count > 1 ) {
				$( 'input, select', row ).val( '' );
				row.fadeOut( 'fast' ).remove();
				firstFocusable.focus();
			} else {
				switch( type ) {
					case 'price' :
						alert( edd_vars.one_price_min );
						break;
					case 'file' :
						$( 'input, select', row ).val( '' );
						break;
					default:
						alert( edd_vars.one_field_min );
						break;
				}
			}

			/* re-index after deleting */
			$(repeatable).each( function( rowIndex ) {
				$(this).find( 'input, select' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
					$( this ).attr( 'name', name ).attr( 'id', name );
				});
			});
		});
	},

	type : function() {

		$( document.body ).on( 'change', '#_edd_product_type', function(e) {

			var edd_products            = $( '#edd_products' ),
				edd_download_files      = $( '#edd_download_files' ),
				edd_download_limit_wrap = $( '#edd_download_limit_wrap' );

			if ( 'bundle' === $( this ).val() ) {
				edd_products.show();
				edd_download_files.hide();
				edd_download_limit_wrap.hide();
			} else {
				edd_products.hide();
				edd_download_files.show();
				edd_download_limit_wrap.show();
			}

		});

	},

	prices : function() {
		$( document.body ).on( 'change', '#edd_variable_pricing', function(e) {
			var checked   = $(this).is(':checked');
			var single    = $( '#edd_regular_price_field' );
			var variable  = $( '#edd_variable_price_fields, .edd_repeatable_table .pricing' );
			var bundleRow = $( '.edd-bundled-product-row, .edd-repeatable-row-standard-fields' );
			if ( checked ) {
				single.hide();
				variable.show();
				bundleRow.addClass( 'has-variable-pricing' );
			} else {
				single.show();
				variable.hide();
				bundleRow.removeClass( 'has-variable-pricing' );
			}
		});
	},

	files : function() {
		var file_frame;
		window.formfield = '';

		$( document.body ).on('click', '.edd_upload_file_button', function(e) {

			e.preventDefault();

			var button = $(this);

			window.formfield = $(this).closest('.edd_repeatable_upload_wrapper');

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media( {
				frame: 'post',
				state: 'insert',
				title: button.data( 'uploader-title' ),
				button: {
					text: button.data( 'uploader-button-text' )
				},
				multiple: $( this ).data( 'multiple' ) == '0' ? false : true  // Set to true to allow multiple files to be selected
			} );

			file_frame.on( 'menu:render:default', function( view ) {
				// Store our views in an object.
				var views = {};

				// Unset default menu items
				view.unset( 'library-separator' );
				view.unset( 'gallery' );
				view.unset( 'featured-image' );
				view.unset( 'embed' );

				// Initialize the views in our view object.
				view.set( views );
			} );

			// When an image is selected, run a callback.
			file_frame.on( 'insert', function() {

				var selection = file_frame.state().get('selection');
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();

					var selectedSize = 'image' === attachment.type ? $('.attachment-display-settings .size option:selected').val() : false;
					var selectedURL  = attachment.url;
					var selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

					if ( selectedSize && typeof attachment.sizes[selectedSize] != "undefined" ) {
						selectedURL = attachment.sizes[selectedSize].url;
					}

					if ( 'image' === attachment.type ) {
						if ( selectedSize && typeof attachment.sizes[selectedSize] != "undefined" ) {
							selectedName = selectedName + '-' + attachment.sizes[selectedSize].width + 'x' + attachment.sizes[selectedSize].height;
						} else {
							selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
						}
					}

					if ( 0 === index ) {
						// place first attachment in field
						window.formfield.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
						window.formfield.find( '.edd_repeatable_thumbnail_size_field').val( selectedSize );
						window.formfield.find( '.edd_repeatable_upload_field' ).val( selectedURL );
						window.formfield.find( '.edd_repeatable_name_field' ).val( selectedName );
					} else {
						// Create a new row for all additional attachments
						var row = window.formfield,
							clone = Ever_Repeatable.clone_repeatable( row );

						clone.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
						clone.find( '.edd_repeatable_thumbnail_size_field' ).val( selectedSize );
						clone.find( '.edd_repeatable_upload_field' ).val( selectedURL );
						clone.find( '.edd_repeatable_name_field' ).val( selectedName );
						clone.insertAfter( row );
					}
				});
			});

			// Finally, open the modal
			file_frame.open();
		});


		var file_frame;
		window.formfield = '';

	},

	updatePrices: function() {
		$( '#edd_price_fields' ).on( 'keyup', '.edd_variable_prices_name', function() {

			var key = $( this ).parents( '.ever_repeatable_row' ).data( 'key' ),
				name = $( this ).val(),
				field_option = $( '.edd_repeatable_condition_field option[value=' + key + ']' );

			if ( field_option.length > 0 ) {
				field_option.text( name );
			} else {
				$( '.edd_repeatable_condition_field' ).append(
					$( '<option></option>' )
						.attr( 'value', key )
						.text( name )
				);
			}
		} );
	}

};
