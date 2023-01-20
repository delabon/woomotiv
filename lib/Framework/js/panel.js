(function( $, $window ){
    'use strict';

	var form = $('.dlb_form');
	window.dlbFormOptionsChanged = false;

	/**
	 * Nav 
	 */
	var navTabParent = $( '.dlb_panel_nav ._tab_active' ).parents('._tab');
	if( navTabParent.length ){
		navTabParent.addClass('_tab_active_ancestor')
	}

	/**
	 * Color Picker
	 */
	$( '.dlb_input_colorpicker', form ).wpColorPicker({
		change: function(event, ui) {
			$(event.target).trigger('dlb_input_change');			
		}
	});

	/**
	 * Checkbox option click event
	 */
	$( 'input[type="checkbox"]', form ).click( function( event ){
        var _self = $(this);
                
		if( _self.is(':checked') ){
			_self.prev('input[type="hidden"]').val(1);
		}
		else{
			_self.prev('input[type="hidden"]').val(0);
		}
	});

	/**
	 * Multiple select change event
	 */
	$( 'select[multiple]', form ).change( function( event ){
		var $this = $(this);
		$this.parent().find('input[type="hidden"]').val( $this.val().join(',') );
	});
	
	/**
	 * Radio Images
	 */
	$( '.dlb_input_wrapper._radio_images_ input', form ).click( function( event ){
        var _self = $(this);
			
		_self.closest('.dlb_input_wrapper').find('label._selected_').removeClass('_selected_');

		if( _self.is(':checked') ){
			_self.parent().addClass('_selected_');
		}
    });
	
	/**
	 * Do not exit if options changes
	 */
	$( '.dlb_input', form ).on( 'change input', function(){
		dlbFormOptionsChanged = true;
	}); 

	form.on( 'submit', function(){
		dlbFormOptionsChanged = false;
	}); 

	$window.on( 'beforeunload', function() { 
			
		if( ! dlbFormOptionsChanged ){
			return undefined;
		}

		return false 
	});

})( jQuery, jQuery( window ) );
