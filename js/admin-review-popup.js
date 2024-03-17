(function( $, $window, body ){
    'use strict';

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

	/*****************************************************
	 # Ajax :: Cancel review clicked
	*****************************************************/ 
	$('.woomotiv-reviews-popup .__cancel_review').on('click', function( e ){
		e.preventDefault();
		
		ajax({
			action: 'woomotiv_cancel_review',
			nonce: woomotiv_admin_review_popup.nonce
		}).done(function( response ){	
			$('.woomotiv-reviews-popup').fadeOut();
		});
	});

	/*****************************************************
	 # Ajax :: Go review clicked
	*****************************************************/ 
	$('.woomotiv-reviews-popup .__go_review').on('click', function( e ){
		e.preventDefault();

		var url = $(this).attr('href');

		ajax({
			action: 'woomotiv_cancel_review',
			nonce: woomotiv_admin_review_popup.nonce
		}).done(function( response ){	
			$('.woomotiv-reviews-popup').fadeOut();
			location.href = url;
		});
	});

})( jQuery, jQuery( window ), jQuery('body') );