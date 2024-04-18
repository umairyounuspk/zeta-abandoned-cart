( function ( $ ) {

	const input_selectors = '#zac-user-consent, input[name^="billing_"], textarea[name^="billing_"], select[name^="billing_"], input[name^="shipping_"], textarea[name^="shipping_"], select[name^="shipping_"], textarea[name="order_comments"]';
	const EmailRegex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	var debounce = '';
	$( document ).on('keyup change', input_selectors, function(event){
		
		var inputs = $( '#customer_details' ).find(input_selectors);

		var data = {
			action: 'save_zac_data',
			nonce: zac_vars.nonce,
			checkout_id: zac_vars.checkout_id,
			ship_to_different_address: $( '#ship-to-different-address-checkbox' ).is( ":checked" )
		};
		inputs.each(function(indx, elem){
			data[$(elem).attr('name')] = $(elem).val();
		});

		if(!(EmailRegex.test(data.billing_email) && $("#zac-user-consent").is(':checked'))) // FIXME delete from database as well.
			return;
		
		clearTimeout( debounce ); // reset the timeout function to manage denoucing
		
		debounce = setTimeout( function () {
			jQuery.post(
				zac_vars.admin_ajax,
				data, // ajax_url coming from localized script
				function (resp) {
					// console.log(resp);
					// debug response
				}
			);
		}, 800 );

	});

} )( jQuery );

