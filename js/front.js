/**
 * Plugin
 */
(function( $ ){
    
    var win = $( window );
    var body = $('body');
    var nonce = woomotivObj.nonce;
    var ajax_url = woomotivObj.ajax_url;
    var timer = false;
    var secTimer = false;
    var resizeTimer = false;
    var intervalTime = parseInt( woomotivObj.interval ) * 1000;
    var hideTime = parseInt( woomotivObj.hide ) * 1000;
    var items;
    var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
    var requestAnimID;

    function whichTransitionEvent(){
        var t;
        var el = document.createElement('fakeelement');
        var transitions = {
          'transition':'transitionend',
          'OTransition':'oTransitionEnd',
          'MozTransition':'transitionend',
          'WebkitTransition':'webkitTransitionEnd'
        }
    
        for(t in transitions){
            if( el.style[t] !== undefined ){
                return transitions[t];
            }
        }
    }

    function whichAnimationEvent(){
        
        var t;
        var el = document.createElement('fakeelement');

        var transitions = {
          'animation':'animationend',
          'OAnimation':'oAnimationEnd',
          'MozAnimation':'animationend',
          'WebkitAnimation':'webkitAnimationEnd'
        }
    
        for(t in transitions){            
            if( el.style[t] !== undefined ){
                return transitions[t];
            }
        }
    }

    var transitionEvent = whichTransitionEvent();
    var animationEvent = whichAnimationEvent();
    var transitionAnimations = [ 'fade', 'slideup', 'slidedown', 'slideright', 'slideleft' ];

    /**
     * Adds time to a date. Modelled after MySQL DATE_ADD function.
     * Example: dateAdd(new Date(), 'minutes', 30)  //returns 30 minutes from now.
     * 
     * @param date  Date to start with
     * @param interval  One of: year, quarter, month, week, day, hour, minute, second
     * @param units  Number of units of the given interval to add.
     */
    function dateAdd(date, interval, units) {
        var ret = new Date(date); //don't change original date
        var checkRollover = function() { if(ret.getDate() != date.getDate()) ret.setDate(0);};
        switch(interval.toLowerCase()) {
            case 'year'   :  ret.setFullYear(ret.getFullYear() + units); checkRollover();  break;
            case 'quarter':  ret.setMonth(ret.getMonth() + 3*units); checkRollover();  break;
            case 'month'  :  ret.setMonth(ret.getMonth() + units); checkRollover();  break;
            case 'week'   :  ret.setDate(ret.getDate() + 7*units);  break;
            case 'day'    :  ret.setDate(ret.getDate() + units);  break;
            case 'hour'   :  ret.setTime(ret.getTime() + units*3600000);  break;
            case 'minute' :  ret.setTime(ret.getTime() + units*60000);  break;
            case 'second' :  ret.setTime(ret.getTime() + units*1000);  break;
            default       :  ret = undefined;  break;
        }
        return ret;
    }

    /**
     * Wrapper for $.ajax
     * @param {String} action 
     * @param {Object} data 
     */
    function ajax( action, data ){

        data = typeof data === 'object' ? data : {};

        data.action = 'woomotiv_' + action;
        
        if( ! data.hasOwnProperty('nonce') ){
            data.nonce = nonce;
        }

        return $.ajax({
            type: "POST",
            url: ajax_url,
            data: data,
        });

    }


    /**
     * Renders the order popup
     * @param {object} item 
     */
    function renderOrder( item, iii ){
        item = item.popup;
         
        if( ! item.user.first_name || item.user.first_name === '' ){
            item.user.first_name = item.user.username;
        }

        if( ! item.user.last_name || item.user.last_name === '' ){
            item.user.last_name = '';
        }

        var content_lines = woomotivObj.template_content;
        content_lines = content_lines.replace( /{date}/gi, '<span class="wmt-date">' + item.date_completed + '</span>');
        content_lines = content_lines.replace( /{new_line}/gi, '<br>');
        content_lines = content_lines.replace( /{buyer}/gi, '<strong class="wmt-buyer">' + item.user.first_name + ' ' + item.user.last_name.charAt( 0 ) + '</strong>');
        content_lines = content_lines.replace( /{product}/gi, '<strong class="wmt-product">' + item.product.name + '</strong>');
        content_lines = content_lines.replace( /{by_woomotiv}/gi, '<span class="wmt-by">' + 'by <span>woomotiv</span>' + '</strong>');
        content_lines = content_lines.replace( /{city}/gi, '<strong class="wmt-city">' + item.city  + '</strong>');
        content_lines = content_lines.replace( /{country}/gi, '<strong class="wmt-country">' +  item.country + '</strong>');
        content_lines = content_lines.replace( /{state}/gi, '<strong class="wmt-state">' + item.state + '</strong>');
        content_lines = content_lines.replace( /{buyer_first_name}/gi, '<strong class="wmt-buyer-first-name">' + item.user.first_name + '</strong>');
        content_lines = content_lines.replace( /{buyer_last_name}/gi, '<strong class="wmt-buyer-last-name">' + item.user.last_name + '</strong>');
        content_lines = content_lines.replace( /{buyer_username}/gi, '<strong class="wmt-buyer-username">' + item.user.username + '</strong>');
        
        body.append(
            '<div data-index="' + iii + '" data-type="order" data-product="' + item.product.id + '" class="woomotiv-popup wmt-index-' + iii + '" data-size="'+woomotivObj.size+'" data-shape="'+woomotivObj.shape+'" data-animation="'+woomotivObj.animation+'" data-position="'+woomotivObj.position+'" data-hideonmobile="'+woomotivObj.hide_mobile+'">\
                <div class="woomotiv-container">\
                    <div class="woomotiv-image" >\
                        ' + ( woomotivObj.user_avatar == 0 ? item.product.thumbnail_img : item.user.avatar_img ) + '\
                    </div>\
                    <div class="woomotiv-content"><p>' + content_lines + '</p></div>\
                </div>\
                <a class="woomotiv-link"'+( woomotivObj.disable_link == 1 ? '' : ' href="' + item.product.url + '"' )+'></a>\
                <a class="woomotiv-close ' + ( woomotivObj.hide_close_button == 1 ? '__hidden__' : '' ) + '"><svg viewBox="0 0 24 24" width="12" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M12 11.293l10.293-10.293.707.707-10.293 10.293 10.293 10.293-.707.707-10.293-10.293-10.293 10.293-.707-.707 10.293-10.293-10.293-10.293.707-.707 10.293 10.293z"/></svg></a>\
            </div>'
        );

        return iii + 1;
    }

    /**
     * Renders Review Popup
     * @param {object} item 
     */
    function renderReview( item, iii ){
        item = item.popup;

        if( ! item.user.first_name || item.user.first_name === '' ){
            item.user.first_name = item.user.username;
        }

        if( ! item.user.last_name || item.user.last_name === '' ){
            item.user.last_name = '';
        }

        var content_lines = woomotivObj.template_review;
        content_lines = content_lines.replace( /{date}/gi, '<span class="wmt-date">' + item.date_completed + '</span>');
        content_lines = content_lines.replace( /{new_line}/gi, '<br>');
        content_lines = content_lines.replace( /{buyer}/gi, '<strong class="wmt-buyer">' + item.user.first_name + ' ' + item.user.last_name.charAt( 0 ) + '</strong>');
        content_lines = content_lines.replace( /{product}/gi, '<strong class="wmt-product">' + item.product.name + '</strong>');
        content_lines = content_lines.replace( /{by_woomotiv}/gi, '<span class="wmt-by">' + 'by <span>woomotiv</span>' + '</strong>');
        content_lines = content_lines.replace( /{buyer_first_name}/gi, '<strong class="wmt-buyer-first-name">' + item.user.first_name + '</strong>');
        content_lines = content_lines.replace( /{buyer_last_name}/gi, '<strong class="wmt-buyer-last-name">' + item.user.last_name + '</strong>');
        content_lines = content_lines.replace( /{buyer_username}/gi, '<strong class="wmt-buyer-username">' + item.user.username + '</strong>');
        
        body.append(
            '<div data-index="' + iii + '" data-type="review" data-product="' + item.product.id + '" class="woomotiv-popup wmt-index-' + iii + '" data-size="'+woomotivObj.size+'" data-shape="'+woomotivObj.shape+'" data-animation="'+woomotivObj.animation+'" data-position="'+woomotivObj.position+'" data-hideonmobile="'+woomotivObj.hide_mobile+'">\
                <div class="woomotiv-container">\
                    <div class="woomotiv-image" >\
                        ' + ( woomotivObj.user_avatar == 0 ? item.product.thumbnail_img : item.user.avatar_img ) + '\
                    </div>\
                    <div class="woomotiv-content"><p>' + content_lines + '<br><span class="wmt-stars"><span style="width:' + (item.stars / 5) * 100 + '%"></span></span></p></div>\
                </div>\
                <a class="woomotiv-link"'+( woomotivObj.disable_link == 1 ? '' : ' href="' + item.product.url + '"' )+'></a>\
                <a class="woomotiv-close ' + ( woomotivObj.hide_close_button == 1 ? '__hidden__' : '' ) + '"><svg viewBox="0 0 24 24" width="12" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M12 11.293l10.293-10.293.707.707-10.293 10.293 10.293 10.293-.707.707-10.293-10.293-10.293 10.293-.707-.707 10.293-10.293-10.293-10.293.707-.707 10.293 10.293z"/></svg></a>\
            </div>'
        );

        return iii + 1;
    }


    /**
     * Renders Custom Popup
     * @param {object} item 
     */
    function renderCustomPopup( item, iii ){
        item = item.popup;

        var content = item.content;
        content = content.replace( /\{/gi, '<strong>' );
        content = content.replace( /\}/gi, '</strong>' );

        body.append(
            '<div data-index="' + iii + '" data-type="custom" data-id="' + item.id + '" class="woomotiv-popup wmt-index-' + iii + '" data-size="'+woomotivObj.size+'" data-shape="'+woomotivObj.shape+'" data-animation="'+woomotivObj.animation+'" data-position="'+woomotivObj.position+'" data-hideonmobile="'+woomotivObj.hide_mobile+'">\
                <div class="woomotiv-container">\
                    <div class="woomotiv-image" >\
                        ' + item.image + '\
                    </div>\
                    <div class="woomotiv-content"><p>' + content + '</p></div>\
                </div>\
                <a class="woomotiv-link"'+( woomotivObj.disable_link == 1 ? '' : ' href="' + item.link + '"' )+'></a>\
                <a class="woomotiv-close ' + ( woomotivObj.hide_close_button == 1 ? '__hidden__' : '' ) + '"><svg viewBox="0 0 24 24" width="12" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M12 11.293l10.293-10.293.707.707-10.293 10.293 10.293 10.293-.707.707-10.293-10.293-10.293 10.293-.707-.707 10.293-10.293-10.293-10.293.707-.707 10.293 10.293z"/></svg></a>\
            </div>'
        );

        return iii + 1;
    }
    

    /**
     * Looper
     */
    function start( cindex ){
        
        cindex = parseInt( cindex );

        if( ! items.eq( cindex ).length ) cindex = 0;

        var item = items.eq( cindex );
        var cevent = transitionAnimations.indexOf( item.data('animation') ) != -1 ? transitionEvent : animationEvent;

        item.addClass('wmt-current').one( cevent , function(){

            secTimer = setTimeout(function(){

                item.removeClass('wmt-current');

                timer = setTimeout( function(){

                    start( cindex + 1 );
                    
                }, intervalTime );

            }, hideTime );

        });

    }

    // Get items and create html nodes 
    ajax( 'get_items' ).done( function( response ){

        if( ! response.hasOwnProperty( 'data' ) ) return;
        if( response.data.length === 0 ) return;

        if( localStorage.getItem('woomotiv_pause_date') ){
            var pause_date = new Date( localStorage.getItem('woomotiv_pause_date') );

            if( pause_date > new Date() ){
                return;
            }
        }
        
        var iii = 0;
        
        Object.keys( response.data ).map( function( key, index ){

            var item = response.data[ key ];

            if( item.type === 'order' ){
                iii = renderOrder( item, iii );
            }
            else if( item.type === 'review' ){
                iii = renderReview( item, iii );
            }
            else{
                iii = renderCustomPopup( item, iii );
            }

        });

        // show time
        items = $('.woomotiv-popup');
        
        setTimeout(function(){
            
            requestAnimID = requestAnimationFrame( function(){
                start( 0 );
            });

        }, parseInt( intervalTime / 2 ) );

        items.on( 'mouseenter', function( event ){
            clearTimeout( timer );
            clearTimeout( secTimer );
            cancelAnimationFrame(requestAnimID);
        });

        items.on( 'mouseleave', function( event ){

            var item = items.filter('.wmt-current');
            var index = item.data('index');
            var halftime = parseInt( hideTime / 2 );

            if( index === undefined ) return;
            
            setTimeout(function(){

                item.removeClass('wmt-current');
                
            }, halftime );

            setTimeout( function (){
                
                requestAnimID = requestAnimationFrame( function(){
                    start( item.data('index') + 1 );
                });

            }, ( hideTime + hideTime ) );

        });

        items.find('.woomotiv-close').on( 'click', function( event ){
            items.remove();
            localStorage.setItem('woomotiv_pause_date', dateAdd( new Date(), 'hour', 1 ) );
        });

        // Stats Update
        items.on( 'click', function( event ){

            event.preventDefault();

            var self = $(this);
            var data = {};

            if( self.data('type') === 'order' ){
                data.type = 'order';
                data.product_id = self.data('product');
            }
            else if( self.data('type') === 'review' ){
                data.type = 'review';
                data.product_id = self.data('product');
            }
            else{
                data.type = 'custom';
                data.id = self.data('id');
            }

            ajax( 'update_stats', data ).done(function( response ){
                
                if( woomotivObj.disable_link != 1 ){
                    location.href = self.find('.woomotiv-link').attr('href');
                }

            });

        });
        
        // Mobile Devices        
        if( woomotivObj.size !== 'small' ){

            win.resize(function( event ){
    
                clearInterval( resizeTimer );
    
                resizeTimer = setTimeout(function(){
                                        
                    if( win.width() < 440 ){
                        items.attr('data-size', 'small');
                    }
                    else{
                        items.attr('data-size', 'default');
                    }
    
                }, 20 );
    
            }).resize();
        }

    });

})( jQuery );