<?php

/**
 *
 * This class is responsible to send SMS using API.
 */
if ( !class_exists( 'Woo_Usn_SMS' ) ) {
    class Woo_Usn_SMS {
        public function schedule_sms_sending( $time_to_send, $args ) {
        }

        /**
         * This functions send SMS to phone numbers using the SMS API defined.
         *
         * @param string $phone_number Customer phone number.
         * @param string $message_to_send Message to send to customer.
         *
         * @return array
         */
        public final function send_sms(
            $phone_number,
            $message_to_send,
            $country_code = false,
            $media_url = false,
            $misc = false
        ) {
            global $usn_utility;
            $mresponse = array();
            $options = get_option( 'woo_usn_options' );
            $real_phone_number = $phone_number;
            if ( isset( $options['use-only-whatsapp'] ) && 'yes' == $options['use-only-whatsapp'] ) {
                $use_whatsapp_only = $options['use-only-whatsapp'] == 'yes';
                $use_sms_only = false;
            } else {
                $use_whatsapp_only = false;
                $use_sms_only = true;
            }
            $country_code = $usn_utility::get_country_town_code( $country_code );
            $phone_number = $usn_utility::get_right_phone_numbers( $country_code, $phone_number );
            $phone_number = '+' . $country_code . $phone_number;
            $phone_number = apply_filters(
                'woo_usn_validate_phone_number',
                $phone_number,
                $real_phone_number,
                $country_code
            );
            if ( $use_sms_only && isset( $options['sms'] ) ) {
                $api_used = $options['sms'];
                $obj = new Woo_Usn_SMS_Notifications();
                $api_response = $obj->send(
                    $api_used,
                    $phone_number,
                    $message_to_send,
                    $media_url
                );
                $decoded = $obj->decode_response( $api_used, $api_response );
                $mresponse['sms_status_message'] = print_r( $decoded['status_message'], true );
                $mresponse['sms_status'] = $decoded['status_code'];
            }
            if ( $use_whatsapp_only && isset( $options['wha'] ) ) {
                $api_used = $options['wha'];
                $obj = new Woo_Usn_WHA_Notifications();
                $api_response = $obj->send(
                    $api_used,
                    $phone_number,
                    $message_to_send,
                    $media_url
                );
                $decoded = $obj->decode_response( $api_used, $api_response );
                $mresponse['wha_status_message'] = print_r( $decoded['status_message'], true );
                $mresponse['wha_status'] = $decoded['status_code'];
            }
            do_action(
                'woo_usn_send_notifications_from_gateway',
                $phone_number,
                $message_to_send,
                $country_code,
                $media_url,
                $misc
            );
            $mresponse = apply_filters(
                'woo_usn_send_sms_to_customer',
                $mresponse,
                $phone_number,
                $message_to_send,
                $misc
            );
            do_action(
                'woo_usn_store_logs_from_notifications',
                $options,
                $mresponse,
                $message_to_send,
                $phone_number
            );
            if ( isset( $misc['return'] ) && $misc['return'] ) {
                return $mresponse;
            }
        }

    }

}
/**
add_filter( 'woo_usn_validate_phone_number', function( $phone_number, $real_phone_number, $country_code ) {
	if ( str_contains( $country_code , $real_phone_number ) ) {
		return $real_phone_number;
	}
    return $phone_number;
}, 10, 3 ); */