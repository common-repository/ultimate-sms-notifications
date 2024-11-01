<?php

class Woo_Usn_SMS_Notifications_Pro{

    public function __construct()
    {
        add_filter( 'woo_usn_get_notifications_attributes', array( $this, 'get_api_response' ), 20, 5 );
        add_action( 'woo_usn_sms_gateways_decode_status', array( $this, 'decode_api_response' ) );

    }


    public function get_api_response(  $messaging_options, $gateway_type, $to_number, $message , $media_url ){
        return $messaging_options;
    }

    public function decode_api_response( $api_response ){

    }
}

new Woo_Usn_SMS_Notifications_Pro();
