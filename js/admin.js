(function( $, $window, body ){
    'use strict';

	var nonce = $('[name="woomotiv_nonce"]').val();
	var popup = $('.woomotiv-popup');
	var positionUI = $('.vvoo_input_position');
	var animationUI = $('.vvoo_input_animation');
	var shapeUI = $('.vvoo_input_shape');
	var sizeUI = $('.woomotiv_style_size');
	var fontSizeUI = $('[name="woomotiv_font_size"]');

    /**
     * Wrapper for $.ajax
     * @param {String} action 
     * @param {Object} data 
     */
    var ajax = function( data ){
        return $.ajax({
            type: "POST",
            url: ajaxurl,
			data: data,
			cache: false,
        });
    }

	/**
	 * Color Picker
	 */
	$('.dlb_input_colorpicker').on('dlb_input_change', function(){

		var self = $( this );

		if( self.is('[data-css="bg"]') ){
			popup.css( 'background-color', self.val() );
		}
		else if( self.is('[data-css="text"]') ){
			popup.find('.woomotiv-popup > p').css( 'color', self.val() );
		}
		else if( self.is('[data-css="strong"]') ){
			popup.find('.woomotiv-popup > p strong').css( 'color', self.val() );
		}
		else if( self.is('[data-css="close"]') ){
			popup.find('.woomotiv-close').css( 'color', self.val() );
		}
		else if( self.is('[data-css="close_bg"]') ){
			popup.find('.woomotiv-close').css( 'background-color', self.val() );
		}
		
	});

	/**
	 * Size change
	 */
	sizeUI.on( 'change', function( event ){
		popup.attr( 'data-size', sizeUI.val() );
	});

	/**
	 * Position change
	 */
	positionUI.on( 'change', function( event ){
		popup.attr( 'data-position', positionUI.val() );
	});

	/**
	 * Shape change
	 */
	shapeUI.on( 'change', function( event ){
		console.log(shapeUI.val());
		
		popup.attr( 'data-shape', shapeUI.val() );
	});

	/**
	 * Animation change
	 */
	animationUI.on( 'change', function( event ){
		popup.removeClass( 'wmt-current' ).attr( 'data-animation', animationUI.val() );

		setTimeout( function(){
			popup.addClass( 'wmt-current' );
		}, 500 );

	});
	
	/**
	 * Font Size Change
	 */
	fontSizeUI.on('input', function(event){
		popup.find('> p').css('font-size', fontSizeUI.val() + 'px' );
	})

	/*****************************************************
	 # Table Sorter & Filters
	*****************************************************/ 
	$(".tablesorter").tablesorter(); 

	/**
	 * Report Filter
	 */
	var yearUI = $('.dlb_panel .vvoo_input_report_year');
	var monthUI = $('.dlb_panel .vvoo_input_report_month');
	var dayUI = $('.dlb_panel .vvoo_input_report_day');

	yearUI.on( 'change', function(){
		location.href = woomotiv_params.panel_url + '&tab=report&year=' + yearUI.val();
	});

	monthUI.on( 'change', function(){
		location.href = woomotiv_params.panel_url + '&tab=report&year=' + yearUI.val() + '&month=' + monthUI.val();
	});

	dayUI.on( 'change', function(){
		location.href = woomotiv_params.panel_url + '&tab=report&year=' + yearUI.val() + '&month=' + monthUI.val() + '&day=' + dayUI.val();
	});


	/*****************************************************
	 # Prevents exit if an option is changed
	*****************************************************/ 
	$('.dlb_panel .vvoo_input').on( 'change input', function(){

		var self = $(this);

		if( self.hasClass('vvoo_input_report_year') || self.hasClass('vvoo_input_report_month') || self.hasClass('vvoo_input_report_day') ){
			dlbFormOptionsChanged = false;
		}

	}); 

	/*****************************************************
	 # Media Upload :: WP Image Uploader
	*****************************************************/ 
	var frame;
	
	function mediaUpload( event ){
		event.preventDefault();

		var _self = $( this );
		var parent = _self.parents('.dlb_image_upload_container');

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create a new media frame
		frame = wp.media({
			// title: 'Select or Upload Media Of Your Chosen Persuasion',
			// button: {
			// 	text: 'Use this media'
			// },
			multiple: false  // Set to true to allow multiple files to be selected
		});


		// When an image is selected in the media frame...
		frame.on( 'select', function() {

			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();

			// Send the attachment URL to our custom image input field.
			parent.find('img').attr('src', attachment.url );
			parent.find('[name="image_id"]').val( attachment.id );
		});

		// Finally, open the modal on click
		frame.open();
	}

	/*****************************************************
	 # Handles custom add/edit ajax response
	*****************************************************/ 
	function customPopupCrud( response ){

		body.append( response );

		var modal = $('.woomotiv-custom-popup-modal');
		var closeBtn = $('.woomotiv_modal_close');
		var saveBtn = $('.woomotiv_modal_save');

		modal.addClass('_open');

		$( ".woomotiv-custom-popup-modal .dlb_datepicker" ).datepicker();

		$('.woomotiv_upload_image').on( 'click', mediaUpload );

		closeBtn.on('click', function( e ){
			e.preventDefault();
			modal.removeClass('_open');
		});

		saveBtn.on( 'click', customPopupSave );

	}

	/*****************************************************
	 # Ajax :: Custom Popup - Save
	*****************************************************/ 
	function customPopupSave( e ){
		e.preventDefault();

		var self = $( this );
		var modal = $('.woomotiv-custom-popup-modal');
		var data = modal.find('form').serialize();
		data = data + '&action=woomotiv_custom_popup_save&nonce=' + nonce;

		ajax( data ).done(function( response ){	
			location.reload();
		});
	}

	/*****************************************************
	 # Ajax :: Custom Popup - Add Modal
	*****************************************************/ 
	$('#woomotiv-add-custom-popup').click(function( event ){
		event.preventDefault();

		var self = $( this );
		$('.woomotiv-custom-popup-modal').remove();

		ajax( {
			action: 'woomotiv_custom_popup_add_form',
			nonce: nonce,
		} ).done( customPopupCrud );

	});

	/*****************************************************
	 # Ajax :: Custom Popup - Edit
	*****************************************************/ 
	$('.woomotiv_custom_popup_edit').on('click', function( e ){
		e.preventDefault();

		var _self = $( this );
		$('.woomotiv-custom-popup-modal').remove();
		
		ajax({
			action: 'woomotiv_custom_popup_edit_form',
			nonce: nonce,
			id: _self.closest('.woomotiv_custom_item').data('id'),
		}).done( customPopupCrud );
	});

	/*****************************************************
	 # Ajax :: Custom Popup - Delete
	*****************************************************/ 
	$('.woomotiv_custom_popup_delete').on('click', function( e ){
		e.preventDefault();

		var _self = $( this );

		if( confirm(  woomotiv_params.delete_text ) ){	
			ajax({
				action: 'woomotiv_custom_popup_delete',
				nonce: nonce,
				id: _self.closest('.woomotiv_custom_item').data('id'),
			}).done(function( response ){	
				location.reload();
			});
		}
	});

})( jQuery, jQuery( window ), jQuery('body') );