<?php

use Homescriptone\USN\Send;
class Woo_Usn_WHA_Notifications extends Send {
    public function send(
        $gateway_type,
        $to_number,
        $message,
        $media_url
    ) {
        $db_options = get_option( 'woo_usn_options' );
        $messaging_options = array();
        $method = "POST";
        switch ( $gateway_type ) {
            case 'twilio-whatsapp':
                // add a status callback to retrieve reply from twilio
                $account_sid = $db_options['api_keys']['wha'][$gateway_type]['account-sid'];
                $auth_token = $db_options['api_keys']['wha'][$gateway_type]['auth-token'];
                $from_number = $db_options['api_keys']['wha'][$gateway_type]['twilio-phone-number'];
                $message_id = $db_options['api_keys']['wha'][$gateway_type]['message-service-id'];
                $headers = array(
                    'Authorization' => 'Basic ' . base64_encode( $account_sid . ':' . $auth_token ),
                );
                $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";
                if ( $message_id == "" ) {
                    $data = array(
                        'From' => 'whatsapp:' . $from_number,
                        'To'   => 'whatsapp:' . $to_number,
                        'Body' => $message,
                    );
                    if ( isset( $media_url ) && "" != $media_url ) {
                        $data['MediaUrl'] = $media_url;
                    }
                } else {
                    try {
                        $message = json_decode( $message );
                        $data = array(
                            'MessagingServiceSid' => $message_id,
                            'To'                  => 'whatsapp:' . $to_number,
                            'ContentSid'          => $message->content_sid,
                        );
                        if ( !is_null( $message->var ) ) {
                            $data['ContentVariables'] = json_encode( $message->var );
                        }
                    } catch ( Exception $er ) {
                        echo "The content message template provided is not valid, please update it.";
                        return;
                    }
                }
                $method = "POST";
                $messaging_options = array(
                    'url'     => $url,
                    'body'    => $data,
                    'headers' => $headers,
                );
                break;
            case 'greenapi':
                $customer_id = $db_options['api_keys']['wha'][$gateway_type]['customer-id'];
                $api_key = $db_options['api_keys']['wha'][$gateway_type]['api-key'];
                $url = "https://api.green-api.com/waInstance{$customer_id}/SendMessage/{$api_key}";
                $to_number = str_replace( '+', '', $to_number );
                $post_fields = array(
                    'chatId'  => $to_number . '@c.us',
                    'message' => $message,
                );
                $body = wp_json_encode( $post_fields );
                $method = "POST";
                $headers = array(
                    'cache-control' => 'no-cache',
                );
                $messaging_options = array(
                    'url'     => $url,
                    'body'    => $body,
                    'headers' => $headers,
                );
                break;
            case 'ultramsg':
                $instance_id = $db_options['api_keys']['wha'][$gateway_type]['instance-id'];
                $token = $db_options['api_keys']['wha'][$gateway_type]['token'];
                $params = array(
                    'token' => $token,
                    'to'    => $to_number,
                    'body'  => $message,
                );
                $method = "POST";
                $url = "https://api.ultramsg.com/{$instance_id}/messages/chat";
                $messaging_options = array(
                    'url'  => $url,
                    'body' => $params,
                );
                break;
        }
        if ( $method == "GET" ) {
            $result = wp_remote_get( $messaging_options['url'] );
        } else {
            $data_result = array(
                'timeout' => 65,
                'method'  => $method,
            );
            if ( isset( $messaging_options['data'] ) ) {
                $data_result['data'] = $messaging_options['data'];
            }
            if ( isset( $messaging_options['body'] ) ) {
                $data_result['body'] = $messaging_options['body'];
            }
            if ( isset( $messaging_options['headers'] ) ) {
                $data_result['headers'] = $messaging_options['headers'];
            }
            $result = wp_remote_post( $messaging_options['url'], $data_result );
        }
        $body_result = wp_remote_retrieve_body( $result );
        return apply_filters(
            'woo_usn_get_notifications_request_response',
            $body_result,
            $gateway_type,
            $result
        );
    }

    public function decode_response( $gateway_type, $response_to_decode ) {
        $wc_log = wc_get_logger();
        $wc_log->alert( "{$gateway_type} Log  : " . print_r( $response_to_decode, true ), array(
            'source' => 'ultimate-sms-notifications',
        ) );
        switch ( $gateway_type ) {
            case 'twilio-whatsapp':
                $api_resp_decoded = json_decode( $response_to_decode, true );
                if ( isset( $api_resp_decoded['status'] ) ) {
                    return array(
                        'status_message' => 'The message is ' . $api_resp_decoded['status'] . ' you can access it <a href="https://console.twilio.com/us3/monitor/logs/sms?pageSize=50&sid=' . $api_resp_decoded['sid'] . '">here</a>',
                        'status_code'    => ( $api_resp_decoded['status'] == "queued" || $api_resp_decoded['status'] == "accepted" ? 200 : 400 ),
                    );
                } else {
                    if ( isset( $api_resp_decoded['code'] ) && $api_resp_decoded['code'] == 20003 ) {
                        return array(
                            'status_message' => 'Twilio credentials is not correct, please update them.',
                            'status_code'    => 400,
                        );
                    } else {
                        return array(
                            'status_message' => 'We are unable to send your message, please have a look <a href="https://console.twilio.com/us3/monitor/logs/sms?pageSize=50">here</a> for more details. ',
                            'status_code'    => 400,
                        );
                    }
                }
                break;
            case 'greenapi':
                $api_resp_decoded = json_decode( $response_to_decode, true );
                if ( isset( $api_resp_decoded['idMessage'] ) ) {
                    return array(
                        'status_message' => 'Your WhatsApp message has been delivered successfully.',
                        'status_code'    => 200,
                    );
                } else {
                    return array(
                        'status_message' => $api_resp_decoded['message'],
                        'status_code'    => 400,
                    );
                }
                break;
            case 'ultramsg':
                $api_resp_decoded = json_decode( $response_to_decode, true );
                if ( isset( $api_resp_decoded['sent'] ) && $api_resp_decoded['sent'] ) {
                    return array(
                        'status_message' => 'Your WhatsApp message has been delivered successfully.',
                        'status_code'    => 200,
                    );
                } else {
                    return array(
                        'status_message' => $api_resp_decoded['error'],
                        'status_code'    => 400,
                    );
                }
                break;
            default:
                do_action( 'woo_usn_wha_gateways_decode_status', json_decode( $response_to_decode, true ) );
                break;
        }
    }

}
