<?php

namespace Homescriptone\USN;

class Woo_USN_SMS_GW extends Gateways {

	public $tab_slug = 'sms-api';
    public $mode_type = 'sms';

    public function __construct() {
        parent::__construct();
        $this->set_gateway_fields( array(
                'Twilio'   => array(
                    'account-sid'        => array(
                        'Account SID' => 'text',
                    ),
                    'auth-token' => array(
                        'Auth Token' => 'text',
                    ),
                    'twilio-phone-number'           => array(
                        'Phone Number' => 'text',
                    ),
                ),
                'Kivalo' => array(
                    'from-number' => array(
                        'From Number' => 'text',
                    ),
                    'api-key'    => array(
                        'API Key' => 'text',
                    ),
                ),
                'AvlyText' => array(
                     'api-key'    => array(
                        'API Key' => 'text',
                    ),
                     'sender-name'    => array(
                        'Sender Name' => 'text',
                    ),
                ),
                'TyntecSMS' => array(
                     'api-key'    => array(
                        'API Key' => 'text',
                    ),
                     'sender-name'    => array(
                        'Sender Name' => 'text',
                    ),
                ),
                'Fast2SMS' => array(
                     'api-key'    => array(
                        'API Key' => 'text',
                    ),
                ),
                '1s2u' => array(
                    // https://api.1s2u.io/bulksms?username=YourUsername&password=YourPassword&mt=MessageType&sid=SenderName&mno=MobileNumber&msg=Message
                    'username'    => array(
                       'Username' => 'text',
                   ),
                   'password'    => array(
                        'Password' => 'text',
                    ),
                    // 'mt'    => array(
                    //     'Message Type' => 'text',
                    // ), to set to 1
                    'sid'    => array(
                        'Sender Name' => 'text',
                    ),
                ),
                'Messente' => array(
                    // https://api.1s2u.io/bulksms?username=YourUsername&password=YourPassword&mt=MessageType&sid=SenderName&mno=MobileNumber&msg=Message
                    'username'    => array(
                       'Username' => 'text',
                   ),
                   'password'    => array(
                        'Password' => 'text',
                    ),
                    // 'mt'    => array(
                    //     'Message Type' => 'text',
                    // ), to set to 1
                    'sender-name'    => array(
                        'Sender Name' => 'text',
                    ),
                ),
                'FortyTwo' => array(
                    'token'    => array(
                       'Token' => 'text',
                   ),
                   'sender-id'    => array(
                        'Sender Id' => 'text',
                    ),
                ),
                'Telesign' => array(
	                'customer-id'    => array(
		                'Customer ID' => 'text',
	                ),
	                'api-key'    => array(
		                'API Key' => 'text',
	                ),
                ),
                'Octopush' => array(
	                'login'    => array(
		                'Login' => 'text',
	                ),
	                'api-key'    => array(
		                'API Key' => 'text',
	                ),
                ),

            ));
    }

	public function add_settings( $tab_name ) {
		$tab_name['sms-api'] = array(
			'url'   => '?page=ultimate-sms-notifications-channels&tab=sms-api',
			'title' => __( 'SMS Gateways Credentials', 'ultimate-sms-notifications' ),
		);
		return $tab_name;
	}
}
