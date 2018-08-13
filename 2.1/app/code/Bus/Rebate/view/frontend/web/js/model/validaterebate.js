define(
    ['jquery', 'Magento_Ui/js/modal/modal'],
    function ($, modal) {
        'use strict';
        return {

	    validationErrors: [],
            /**
             * Validate something
             *
             * @returns {boolean}
             */
	    
            validate: function() {
		var fail = 0;
		var alertMessage = "";	
		$("html,body").css("cursor", "progress");
		$.ajax({
			url: "/checkout/cart/validatePost", 
			data: {email: $("#customer-email").val()}, 
			success: function(response) {
				if (!response.success) {
					fail = 1;
					alertMessage = response['error_message'];
				}
				else {
					fail = 0;
				}
			},
			error: function(result, statusMessage) {
				fail = 1;
				alertMessage = statusMessage;
			},
			async: false
		});
		$("html,body").css("cursor", "default");
		if (fail) { 
			if ($("#rebate-error-modal").length) {
				$("#rebate-error-modal").remove();
			}
			$("body").append( "<div id='rebate-error-modal'></p>" );
			$("#rebate-error-modal").modal({
			    autoOpen:true,
			    clickableOverlay:true,
			    type:'popup',
			    title: alertMessage 
			});	
			return false; 
		}
		else {
			return true;
		}
            }
        }
    }
);
