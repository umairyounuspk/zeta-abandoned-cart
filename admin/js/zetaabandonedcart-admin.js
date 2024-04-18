(function( $ ) {
	'use strict';
	
	$( document ).on('click', "#zac_test_email_send", function(){
		var $btn = $(this);
		$btn.attr("disabled", "disabled");
		var nonce = $btn.data("nonce");
		var url = $btn.data("url");

		$("#test_email_status").html("Sending Test Email..");
		var data = {
			action: "zac_test_email",
			nonce: nonce,
			email: $("#zac_test_email").val()
		}

		jQuery.post(
			url,
			data, // ajax_url coming from localized script
			function (resp) {
				$("#test_email_status").html(resp.data);
				
				if(resp.success)
					$("#test_email_status").delay(3000).fadeOut(1000);
				
				$btn.removeAttr("disabled");
			}
		);
	});
})( jQuery );
