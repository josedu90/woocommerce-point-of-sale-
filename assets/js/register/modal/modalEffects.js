/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
var ModalEffects = (function() {

	function init() {
		var overlay = document.querySelector( '.md-overlay' );

		[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

			var modal = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
				close = modal.querySelector( '.md-close' );

			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );
                if(modal.hasAttribute('data-child')){
                    modal.removeAttribute("data-child");
                    classie.remove(overlay, 'child-is-active')
                }
				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( classie.has( el, 'md-setperspective' ) );
			}

			el.addEventListener( 'click', function( ev ) {
				classie.add( modal, 'md-show' );

				if( classie.has( el, 'md-setperspective' ) ) {
					setTimeout( function() {
						classie.add( document.documentElement, 'md-perspective' );
					}, 25 );
				}
			});
			if(close != null){
				close.addEventListener( 'click', function( ev ) {
					ev.stopPropagation();
					removeModalHandler();
				});
			}

		} );

		[].slice.call( document.querySelectorAll( '.md-openmodal' ) ).forEach( function( modal, i ) {

			var close = modal.querySelector( '.md-close' );

			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );
                if(modal.hasAttribute('data-child')){
                    modal.removeAttribute("data-child");
                    classie.remove(overlay, 'child-is-active')
                }
				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( false );
			}

			classie.add( modal, 'md-show' );

			if( close != null ){
				close.addEventListener( 'click', function( ev ) {
					ev.stopPropagation();
					removeModalHandler();
				});
			}

		} );

		[].slice.call( document.querySelectorAll( '.md-dynamicmodal' ) ).forEach( function( modal, i ) {

			//var close = modal.querySelector( '.md-close' );
			var closes = modal.querySelectorAll( '.md-close' );


			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );
                if(modal.hasAttribute("data-child")){
                    modal.removeAttribute("data-child");
                    classie.remove(overlay, 'child-is-active')
                }
				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( false );
			}
			if( closes != null ){
				[].forEach.call(closes, function(close) {
					close.addEventListener( 'click', function( ev ) {
						window.openwin = false;
						ev.stopPropagation();
						removeModalHandler();
						var missing = modal.querySelector( '#missing-attributes-select' );
						if(missing != null){
							missing.innerHTML = '';
						}
						var missing = modal.querySelector( '#product-addons-attributes' );
						if(missing != null){
							missing.innerHTML = '';
						}
					});
				});
			}

		} );

	}

	init();

})();
function openModal(modalid, openwin, child) {
	if(!child) child = false;
	var modal = document.querySelector( '#'+modalid );
	if( modal != null ){
		classie.add( modal, 'md-show' );
		if(child){
            modal.setAttribute("data-child", "true");
            classie.add(document.querySelector('.md-overlay'), 'child-is-active');
		}
		if(openwin === true ){
			window.openwin = true;
		}
	}
	if( typeof wp != 'undefined' && typeof wp.hooks != 'undefined'){
		wp.hooks.doAction( 'openModal_' + modalid);
	}
}
function closeModal(modalid) {
	var modal;
	if( typeof modalid != 'undefined')
		modal = document.querySelector( '#'+modalid );
	else
		modal = document.querySelector( '.md-modal.md-show' );

	if( modal != null ){
		classie.remove( modal, 'md-show' );
		var missing = modal.querySelector( '#missing-attributes-select' );
		if(missing != null){
			missing.innerHTML = '';
		}
		var missing = modal.querySelector( '#product-addons-attributes' );
		if(missing != null){
			missing.innerHTML = '';
		}
		if(modal.hasAttribute("data-child")){
            modal.removeAttribute("data-child");
		}
	}
}
function openConfirm(args) {
	var modal = document.querySelector( '#modal-confirm-box' );
	if( modal != null ){
		var sign_check = true;
		var source        = document.getElementById("tmpl-confirm-box-content").innerHTML;
		var template      = Handlebars.compile(source);
		var html          = template(args);
	    document.getElementById("modal-confirm-box-content").innerHTML = html;
	    document.getElementById("cancel-button").addEventListener("click", function(){
            if(typeof args.cancel != 'undefined'){
                args.cancel();
            }
	    	closeModal('modal-confirm-box');
	    });
	    if (wc_pos_params.signature_panel == true && !args.notSign) {
            sign_check = false;
            jQuery("#modal-signature-box-content").jSignature();
            jQuery("#modal-signature-box-content").bind('change', function(e){
                sign_check = true;
			})
        }
	    document.getElementById("confirm-button").addEventListener("click", function(){
	    	if(typeof args.confirm != 'undefined'){


                if (wc_pos_params.signature_panel == true && !args.notSign) {
                  	APP.signature = jQuery("#modal-signature-box-content").jSignature("getData", "image");

                  	if (wc_pos_params.signature_required == true) {
						if (sign_check == true) {
                            args.confirm();
						} else {
                            APP.showNotice(pos_i18n[56], 'error');
						}
					} else {
                        args.confirm();
					}

                } else {
                    args.confirm();
				}

	    	}
            if (wc_pos_params.signature_required == true && sign_check == false) {

			} else {
                closeModal('modal-confirm-box');
            }
	    });

		openModal('modal-confirm-box');
	}
}
function openPromt(args) {
	var modal = document.querySelector( '#modal-confirm-box' );
	if( modal != null ){
		var content = '<input type="text" id="promt_input" autocomplete="off">';
		if( typeof args.content != 'undefined' ){
			args.content += content;
		}else{
			args.content = content;
		}

		var source        = document.getElementById("tmpl-confirm-box-content").innerHTML;
		var template      = Handlebars.compile(source);
		var html          = template(args);
	    document.getElementById("modal-confirm-box-content").innerHTML = html;
	    document.getElementById("cancel-button").addEventListener("click", function(){
	    	classie.remove( modal, 'md-show' );
	    	if(typeof args.cancel != 'undefined'){
	    		args.cancel(false);
	    	}
	    });
	    document.getElementById("confirm-button").addEventListener("click", function(){
	    	classie.remove( modal, 'md-show' );
	    	if(typeof args.confirm != 'undefined'){
	    		var answer = document.getElementById("promt_input").value;
	    		args.confirm(answer);
	    		document.getElementById("promt_input").value = '';
	    	}
	    });
		classie.add( modal, 'md-show' );
		document.getElementById("promt_input").focus();
	}
}
jQuery(document).ready(function($) {
	$('.md-modal .media-menu a').click( function() {
		$parent = $(this).closest('.md-modal');
		$parent.find('.media-menu a').removeClass('active')
		$(this).addClass('active');
		var id  = $(this).attr('href');
		$parent.find('.popup_section').hide();
		$(id).show();
		$('#coupon_tab div.messages').html('');

		if($(this).hasClass('payment_methods')){
			var txt = $(this).text();
			$('h1 span.txt').text(txt);
			var selected_payment_method = $(this).data('bind');
			$('#payment_method_' + selected_payment_method).attr('checked', 'checked');
		}
	return false;
	});
	$('.md-overlay').click(function(event) {
		var $active = $('.md-modal.md-show');
        if($active.length > 1){
        	$.each($active, function(i, el){
        		if($(el).is('[data-child=true]')){
                    $active = $(el);
				}
			});
		}
		if( $active.hasClass('md-close-by-overlay')){
            $active.find('.md-close').click();
		}
});
});