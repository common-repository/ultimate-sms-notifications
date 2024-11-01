<?php

use Homescriptone\USN\Send;

class Woo_Usn_SMS_Notifications extends Send{

    public function send( $gateway_type, $to_number, $message, $media_url ) {
        $db_options = get_option('woo_usn_options');
        $messaging_options = array();
        $method = "POST";

        switch( $gateway_type ) {

            case 'twilio':
                // add a status callback to retrieve reply from twilio
                $account_sid = $db_options['api_keys']['sms']['twilio']['account-sid'];
                $auth_token  = $db_options['api_keys']['sms']['twilio']['auth-token'];
                $from_number = $db_options['api_keys']['sms']['twilio']['twilio-phone-number'];
                $headers = array(
                    'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
                );
                $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
                $data = array(
                    'From' => $from_number,
                    'To' => $to_number,
                    'Body' => $message,
                );
                if ( isset($media_url) && ("" != $media_url)) {
                    $data['MediaUrl'] = $media_url;
                }

                $messaging_options = array(
                    'url'  => $url,
                    'body' => $data,
                    'headers' => $headers
                );
                break;

            case 'kivalo':
                $from_number = $db_options['api_keys']['sms']['kivalo']['from-number'];
                $api_key = $db_options['api_keys']['sms']['kivalo']['api-key'];

                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                // callback url to get api reply or response for gateway
                $url = "http://sms.kivalosolutions.com/sms/api?action=send-sms&api_key=$api_key&to=$to_number&from=$from_number&sms=$message";
                $messaging_options = array(
                    'url'  => $url
                );
                $method = 'GET';
                break;


            case 'avlytext':
                $sender_name = $db_options['api_keys']['sms']['avlytext']['sender-name'];
                $api_key = $db_options['api_keys']['sms']['avlytext']['api-key'];

                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                $url = 'https://api.avlytext.com/v1/sms?api_key=' . $api_key .'&sender='.$sender_name . '&recipient='.$to_number.'&text='.$message;
                $method = 'GET';
                $messaging_options = array(
                    'url'  => $url
                );
                break;


            case 'tyntecsms':
                $sender_name = $db_options['api_keys']['sms']['tyntecsms']['sender-name'];
                $api_key = $db_options['api_keys']['sms']['tyntecsms']['api-key'];
                $headers = array(
                    'Content-Type' => 'application/json',
                    'apikey' => $api_key,
                );
                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                $data = wp_json_encode(
                    array(
                        'from' => $sender_name,
                        'to' => $to_number,
                        'message' => $message,
                    )
                );
                $url = 'https://api.tyntec.com/messaging/v1/sms';
                $messaging_options = array(
                    'url'  => $url,
                    'data' => $data,
                    'headers' => $headers
                );
                break;


            case 'fast2sms':
                $api_key = $db_options['api_keys']['sms']['fast2sms']['api-key'];
                $method = "GET";
                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                $url = "https://www.fast2sms.com/dev/bulkV2?authorization=" . $api_key . "&message=".$message."&language=english&route=q&numbers=". $to_number;
                $messaging_options = array(
                    'url'  => $url
                );
                break;


            case 'fortytwo':
                // 4d21e574-9157-4d60-ac7e-7e63bb60f8b1
                $url =  'https://rest.fortytwo.com/1/im';
                $to  = $to_number;
                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                $key = $db_options['api_keys']['sms']['fortytwo']['token'];
                $sid = $db_options['api_keys']['sms']['fortytwo']['sender-id'];

                $headers =  array(
                    'Authorization' => 'Token ' . $key,
                    'Content-Type'  => 'application/json; charset=utf-8',
                );

                $body = json_encode(array(
                    'destinations' => array(
                        array( 'number' => $to )
                    ),
                    'sms_content'  => array(
                        'sender_id' => $sid,
                        'message'   =>  urlencode(htmlspecialchars($message)),
                    )
                ));

                $messaging_options = array(
                    'url'  => $url,
                    'body' => $body,
                    'headers' => $headers
                );


                break;


            case '1s2u':
                $username = $db_options['api_keys']['sms']['1s2u']['username'];
                $password = $db_options['api_keys']['sms']['1s2u']['password'];
                $sid = $db_options['api_keys']['sms']['1s2u']['sid'];
                $method = "GET";
                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }
                $url = "https://api.1s2u.io/bulksms?username=$username&password=$password&mt=1&sid=$sid&mno=$to_number&msg=$message";
                $messaging_options = array(
                    'url'  => $url
                );
                break;


	        case 'telesign' :
				$customer_id = $db_options['api_keys']['sms']['telesign']['customer-id'];
				$api_key     = $db_options['api_keys']['sms']['telesign']['api-key'];
		        $method = "POST";
		        $headers = array(
			        'Authorization' => 'Basic ' . base64_encode( $customer_id . ':' . $api_key ),
		        );
		        $url = 'https://rest-api.telesign.com/v1/messaging';
		        if ( isset($media_url) && ("" != $media_url)) {
			        $message .= " ". $media_url;
		        }
		        $data = array(
			        'phone_number' => $to_number,
			        'message'      => $message,
			        'message_type' => 'ARN',
		        );
		        $messaging_options = array(
			        'url'  => $url,
			        'body' => $data,
			        'headers' => $headers
		        );
		        break;

	        case 'octopush' :
		        $login = $db_options['api_keys']['sms']['octopush']['login'];
		        $api_key     = $db_options['api_keys']['sms']['octopush']['api-key'];
		        $method = "POST";

		        $headers = array(
			        'Content-Type' => 'application/json',
			        'api-login'    => $login,
			        'api-key'      => $api_key,
		        );
		        if ( isset($media_url) && ("" != $media_url)) {
			        $message .= " ". $media_url;
		        }
		        $url = 'https://api.octopush.com/v1/public/sms-campaign/send';
		        $data = wp_json_encode( array(
			        'purpose'    => 'wholesale',
			        'type'       => 'sms_premium',
			        'text'       => $message,
			        'sender'     => apply_filters( 'woo_usn_octopush_sender_name', get_bloginfo( 'name' ) ),
			        'recipients' => array( 
                        array(
				        'phone_number' => $to_number,
			             ) 
                    ),
		        ) );

		        $messaging_options = array(
			        'url'  => $url,
			        'body' => $data,
			        'headers' => $headers
		        );
		        break;


            case 'messente':
                $username = $db_options['api_keys']['sms']['messente']['username'];
                $password = $db_options['api_keys']['sms']['messente']['password'];
                $sid = $db_options['api_keys']['sms']['messente']['sender-name'];
                $method = "POST";
                if ( isset($media_url) && ("" != $media_url)) {
                    $message .= " ". $media_url;
                }

                $request_body = json_encode(
                    array(
                        'to' => $to_number,
                        'messages' => array(
                            array(
                                'channel' => 'sms', // change this for viber.
                                'sender' => $sid,
                                'text' => $message,
                            ),
                        ),
                    )
                );

                // Set the request headers
                $headers = array(
                    'Content-Type' => 'application/json',
                );

                // Set the authorization header
                $authorization = base64_encode($username . ':' . $password);
                $headers['Authorization'] = 'Basic ' . $authorization;

                $url = "https://api.messente.com/v1/omnimessage";
                $messaging_options = array(
                    'url'  => $url,
                    'body' => $request_body,
                    'headers' => $headers
                );
                $method = "POST";

                break;

            default:
                $messaging_options = apply_filters( 'woo_usn_get_notifications_attributes', $messaging_options, $gateway_type, $to_number, $message , $media_url );
                break;

        }


        if ( $method == "GET" )  {
            $result = wp_remote_get(
                $messaging_options['url']
            );
        } else {
            $data_result = array(
                'timeout' => 65,
                'method' => $method
            );

            if ( isset( $messaging_options['data'] ) ) {
                $data_result['data'] =  $messaging_options['data']  ;
            }

            if ( isset( $messaging_options['body'] ) ) {
                $data_result['body'] =  $messaging_options['body']  ;
            }

            if ( isset( $messaging_options['headers'] ) ) {
                $data_result['headers'] =  $messaging_options['headers']  ;
            }

            $result = wp_remote_post(
                $messaging_options['url'],
                $data_result
            );
        }
        $body_result = wp_remote_retrieve_body($result);
        return apply_filters( 'woo_usn_get_notifications_request_response', $body_result, $gateway_type, $result );
    }


    public function decode_response( $gateway_type, $response_to_decode ){
        // twilio error code 20003 means the api keys credentials are not correct pleae check them again
        // decode api response here wwith the message sid : https://console.twilio.com/us3/monitor/logs/sms?pageSize=50&sid=SMab977b7a3205c0cf04ceb193a7586b70
        $wc_log = wc_get_logger();
        $wc_log->alert( "$gateway_type Log  : " . print_r( $response_to_decode, true  ), array( 'source' => 'ultimate-sms-notifications' ) );

       
        switch( $gateway_type ) {
            case 'twilio':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( isset( $api_resp_decoded['status' ] ) && isset( $api_resp_decoded['sid'] ) ) {
                    return array(
                        'status_message' => 'The message is '. $api_resp_decoded['status'] . ' you can access it <a href="https://console.twilio.com/us3/monitor/logs/sms?pageSize=50&sid='.$api_resp_decoded['sid'].'">here</a>',
                        'status_code' => $api_resp_decoded['status'] == "queued" ? 200 : 400
                    );
                } else {
                    if ( isset(  $api_resp_decoded['code' ] ) &&  ($api_resp_decoded['code' ] == 20003 ) ) {
                        return array(
                            'status_message' => 'Twilio credentials is not correct, please update them.',
                            'status_code'    => 400
                        );
                    } else {
                        return array(
                            'status_message' => 'We are unable to send your message, please have a look <a href="https://console.twilio.com/us3/monitor/logs/sms?pageSize=50">here</a> for more details. ',
                            'status_code'    => 400
                        );
                    }

                }
                break;

            case 'kivalo':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                return array(
                    'status_message' => 'Kivalo response : ' .  $api_resp_decoded['message'] ,
                    'status_code' => $api_resp_decoded['code'] == "ok" ? 200 : 400
                );
                break;

            case 'avlytext':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( isset( $api_resp_decoded['id'] ) ) {
                    return array(
                        'status_message' => 'The message is sent' . print_r( $api_resp_decoded , true ) ,
                        'status_code' => 200
                    );
                } else {
                    return array(
                        'status_message' => 'Errors message : ' . print_r( $api_resp_decoded['errors'] , true ) ,
                        'status_code' => 400
                    );
                }
                break;

            case 'tyntecsms':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( isset( $api_resp_decoded['errorCode'] ) && "string" != $api_resp_decoded['errorCode']  ) {
                    return array(
                        'status_message' => 'Error message : '. print_r( $api_resp_decoded , true ) ,
                        'status_code' => 400
                    );
                } else {
                    return array(
                        'status_message' => 'The message is sent. '. print_r( $api_resp_decoded , true ),
                        'status_code' => 200
                    );
                }
                break;


            case 'fast2sms':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( isset( $api_resp_decoded['return'] ) &&  $api_resp_decoded['return'] ) {
                    return array(
                        'status_message' => 'The message is sent '. print_r( $response_to_decode , true ) ,
                        'status_code' => 200
                    );
                } else {
                    return array(
                        'status_message' => 'The message is not sent, an error occured.'. print_r( $response_to_decode , true ),
                        'status_code' => 400
                    );
                }
                break;


            case '1s2u':
                if ( strpos($response_to_decode, 'OK') !== false ) {
                    return array(
                        'status_message' => 'The message is sent '. print_r( $response_to_decode , true ) ,
                        'status_code' => 200
                    );
                } else {
                    return array(
                        'status_message' => 'The message is not sent, an error occured : '. print_r( $response_to_decode , true ),
                        'status_code' => 400
                    );
                }
                break;


            case 'messente':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( ! isset( $api_resp_decoded['errors'] ) ) {
                    return array(
                        'status_message' => 'The message is sent'. print_r( $response_to_decode , true ) ,
                        'status_code' => 200
                    );
                } else {
                    return array(
                        'status_message' => 'The message is not sent, an error occured : '. print_r( $response_to_decode , true ),
                        'status_code' => 400
                    );
                }
                break;


            case 'fortytwo':
                $api_resp_decoded = json_decode( $response_to_decode , true );
                if ( isset( $api_resp_decoded['result_info']['status_code'] ) && $api_resp_decoded['result_info']['status_code']  == 200 ) {
                    return array(
                        'status_message' => 'The message is sent'. print_r( $response_to_decode , true ) ,
                        'status_code' => 200
                    );

                } else {
                    return array(
                        'status_message' => 'The message is not sent, an error occured : '. print_r( $response_to_decode , true ),
                        'status_code' => 400
                    );
                }
                break;


	        case 'octopush' :
		        $api_resp_decoded = json_decode( $response_to_decode , true );
				if ( isset( $api_resp_decoded['code'] ) && preg_match( '/^2([0-9]{1})([0-9]{1})$/', $api_resp_decoded['code'] )  ) {
					return array(
						'status_message' => 'The message is sent'. print_r( $response_to_decode , true ) ,
						'status_code' => 200
					);
				} else {
					return array(
						'status_message' => 'The message is not sent, an error occured : '. print_r( $response_to_decode , true ),
						'status_code' => 400
					);
				}
				break;


	        case 'telesign' :
		        $api_resp_decoded = json_decode( $response_to_decode , true );
		        if ( isset( $api_resp_decoded['code'] ) && preg_match( '/^2([0-9]{1})([0-9]{1})$/', $api_resp_decoded['code'] )  ) {
			        return array(
				        'status_message' => 'The message is sent'. print_r( $response_to_decode , true ) ,
				        'status_code' => 200
			        );
		        } else {
			        return array(
				        'status_message' => 'The message is not sent, an error occured : '. print_r( $response_to_decode , true ),
				        'status_code' => 400
			        );
		        }
		        break;


            default:
                do_action( 'woo_usn_sms_gateways_decode_status', json_decode( $response_to_decode , true ) );
	            \Woo_Usn_Utility::write_log(  ' Error code :' . print_r( json_decode( $response_to_decode , true )  , true ) );
                break;
        }
    }

}
