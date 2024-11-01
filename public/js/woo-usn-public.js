(function( $ ) {
	'use strict';

	/**
	 * This init the phone validator on the checkout page.
	 *
	 * @param country_code the country code that will be used for the phone number field.
	 */
	function woo_usn_init_pn_validator( country_code ) {
		var iti;
		var woousn_phone_number_validator = document.querySelector( "#billing_phone" );
		var wc_allowed_countries	      = woo_usn_ajax_object.wc_allowed_countries;

		try {
			var options = {
					initialCountry: country_code,
					utilsScript : woo_usn_ajax_object.woo_usn_phone_utils_path,
					separateDialCode : true,
					autoPlaceholder : "off",
					formatOnDisplay : false, // use only international mode
					nationalMode    : false

			};

			if ( wc_allowed_countries.length > 0 ) {
				options['onlyCountries'] = wc_allowed_countries;
			}

			iti = window.intlTelInput(
				woousn_phone_number_validator,
				options
			);


				woousn_phone_number_validator.addEventListener("countrychange", function() {
					$('input#woousn_pn_billing_country').val( iti.getSelectedCountryData().iso2.toUpperCase() );
					$('input[name="woo_usn_billing_country"]').val( iti.getSelectedCountryData().iso2.toUpperCase() );
				});





			$('form').on('change click keydown submit', function() {
				woo_usn_check_pn_validity( iti );
			});
		} catch (e) {
			console.info( e );
		}
		return iti;
	}

	function woo_usn_check_pn_validity( iti ){
		var pphone = iti.getNumber();
		iti.setNumber( pphone );
		var isValid  = iti.isValidNumber();
		var selector = $( 'input#woousn_pn_valid' );
		if ( ! isValid ) {
			$('p#billing_phone_field').removeClass('woocommerce-validated')
			$('p#billing_phone_field').addClass('woocommerce-invalid-phone')
			$('p#billing_phone_field').addClass('woocommerce-invalid');
			selector.val('no')
		} else {
			$('p#billing_phone_field').removeClass('woocommerce-invalid-phone')
			$('p#billing_phone_field').addClass('woocommerce-validated');
			selector.val('yes')
		}
	}
	$( document ).ready(
		function(){
			if ( woo_usn_ajax_object.woo_usn_phone_utils_path ) {

				var country_code = 'us';
				if ( woo_usn_ajax_object.user_country_code ) {
					country_code = woo_usn_ajax_object.user_country_code;
				}
				var iti_pn;

				$( document ).on(
					'keydown',
					'#billing_phone',
					function() {
						woo_usn_check_pn_validity( iti_pn );
					}
				);
				iti_pn = woo_usn_init_pn_validator( country_code );


				if ( $('#billing_country').length < 1 ) {
					// in case the billing country has been removed.
					$('#woousn_pn_billing_country').val(woo_usn_ajax_object.user_country_code);
				}
				$('#woousn_pn_billing_country').val(woo_usn_ajax_object.user_country_code);
			}

			
		}
	);

})( jQuery );
