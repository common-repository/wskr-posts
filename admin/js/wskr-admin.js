(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 /*
		Plugin Setting Tab
	 */
	$(document).ready(function(){
	
		$('.WSKRFormInside ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('.WSKRFormInside ul.tabs li').removeClass('current');
			$('.WSKRFormInside .tab-content').removeClass('current');

			$(this).addClass('current');
			$(".WSKRFormInside #"+tab_id).addClass('current');
		});
		
	})
	

})( jQuery );

/* Authorise Account */
function authorise_business_account(e){	
	jQuery(e).addClass('disabled_btn');	
	jQuery('.Loderimg').show();
	var error = 0;
	jQuery("#wskr_setting_from .input_fields").each(function() {
	    if(jQuery(this).val() == ''){
	    	jQuery(this).addClass('empty_fields');
	    	error++;
	    }
	});
	if(error > 0){
		jQuery(e).removeClass('disabled_btn');	
		jQuery('.Loderimg').hide();
		jQuery('.response_msg').html('<p>There is an error. Please check all the fields and try again.</p>');
		jQuery('.response_msg').addClass('error_msg');
		jQuery('.response_msg').removeClass('success_msg');
		setTimeout(function() { 
            jQuery('.response_msg').html('<p>Response Message</p>');
            jQuery('.response_msg').removeClass('error_msg success_msg'); 
        }, 2000); return false;
	}

  jQuery('.response_msg').removeClass('error_msg success_msg');

  jQuery.ajax({
    url: wskrSettingAjax.ajax_url,
    type: 'post',
    data: {
      action 	: 'wskr_authorise_business_account',
      formdata: jQuery("#wskr_setting_from").serialize()
    },
    success: function(res) {
      console.log(res);
      jQuery(e).removeClass('disabled_btn');
      jQuery('.Loderimg').hide();
      var obj = JSON.parse(res);
      if(obj.status==0){
        jQuery('.response_msg').addClass('error_msg');
        jQuery('.response_msg').html('<p class="m-0">Your WSKR Business account authorisation has failed.</p>' +
            '<p class="m-0">Please ensure that you have entered the correct username and password and try again.</p>' +
            '<p class="m-0">You will not be able to receive payments without authorising your account.</p>');
      }else{
        jQuery('#wskr_business_id').val(obj.data);
        jQuery('.response_msg').addClass('success_msg');
        jQuery('.response_msg').html('<p>You have successfully authorised your WSKR Business account.</p>');
		setTimeout(function() { 
            jQuery('.response_msg').html('Response Message');
            jQuery('.response_msg').removeClass('error_msg success_msg'); 
        }, 2000); return false;
      }
    }
  });
}
/**
* WSKR Save setting script 
*/
function wskr_save_settings_ajax_script(e){
	jQuery('.LoderSaveimg').show();
	jQuery(e).addClass('disabled_btn');
	var error = 0;
	jQuery("#wskr_setting_from .input_fields").each(function() {
	    if(jQuery(this).val() == ''){
	    	jQuery(this).addClass('empty_fields');
	    	error++;
	    }
	});
	if(error > 0){
		jQuery(e).removeClass('disabled_btn');	
		jQuery('.Loderimg').hide();
		jQuery('.response_msg').html('*There is an error. Please check all the fields and try again.');
		jQuery('.response_msg').addClass('error_msg');
		jQuery('.response_msg').removeClass('success_msg');
		setTimeout(function() { 
            jQuery('.response_msg').html('Response Message');
            jQuery('.response_msg').removeClass('error_msg success_msg'); 
        }, 2000); return false;
	}
	jQuery.ajax({
        url: wskrSettingAjax.ajax_url,
        type: 'post',
        data: {
        	action 	: 'wskr_save_settings_ajax',
        	formdata: jQuery("#wskr_setting_from").serialize()
        },
        success: function(res) {
        	var obj = JSON.parse(res);
            jQuery(e).removeClass('disabled_btn');
            jQuery('.LoderSaveimg').hide();
            if(obj.status == 1){
            	jQuery('.response_msg').html('Settings successfully saved.');
            	jQuery('.response_msg').addClass('success_msg');
	            jQuery('.response_msg').removeClass('error_msg');
	            setTimeout(function() { 
	                jQuery('.response_msg').html('Response Message');
	                jQuery('.response_msg').removeClass('error_msg success_msg'); 
	            }, 1500);
            }
        }
    });
}
