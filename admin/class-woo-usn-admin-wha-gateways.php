<?php

namespace Homescriptone\USN;

class Woo_USN_WHA_GW extends Gateways {
    public $tab_slug = 'wha-api';

    public $mode_type = 'wha';

    public function __construct() {
        parent::__construct();
        $this->set_gateway_fields( array(
            'Twilio WhatsApp' => array(
                'account-sid'         => array(
                    'Account SID' => 'text',
                ),
                'auth-token'          => array(
                    'Auth Token' => 'text',
                ),
                'twilio-phone-number' => array(
                    'Phone Number' => 'text',
                ),
                'message-service-id'  => array(
                    'Message Service Id' => 'text',
                ),
            ),
            'GreenAPI'        => array(
                'customer-id' => array(
                    'Customer ID' => 'text',
                ),
                'api-key'     => array(
                    'API Key' => 'text',
                ),
            ),
            'UltraMSG'        => array(
                'instance-id' => array(
                    'Instance ID' => 'text',
                ),
                'token'       => array(
                    'Token' => 'text',
                ),
            ),
        ) );
    }

    public function get_configuration() {
        $saved_settings = get_option( 'woo_usn_options' );
        $fields = array();
        $fields['use-only-whatsapp'] = array(
            'label'       => esc_html__( "Use WhatsApp instead of SMS for sending messages  :", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'content'     => formulus_input_fields( 'woo_usn_options[use-only-whatsapp]', array(
                'type'    => 'radio',
                'options' => array(
                    'no'  => "No",
                    'yes' => "Yes",
                ),
            ), ( isset( $saved_settings['use-only-whatsapp'] ) ? $saved_settings['use-only-whatsapp'] : 'no' ) ),
        );
        formulus_input_table( 'testing-fields', $fields );
    }

    public function display_setting_fields( $tab_name ) {
        if ( $tab_name == $this->tab_slug ) {
            $this->get_configuration();
            $this->get_setting_fields( $tab_name );
            // $this->get_testing_message_fields();
        }
    }

    public function add_settings( $tab_name ) {
        $tab_name['wha-api'] = array(
            'url'   => '?page=ultimate-sms-notifications-channels&tab=wha-api',
            'title' => __( 'WhatsApp Gateways Credentials', 'ultimate-sms-notifications' ),
        );
        return $tab_name;
    }

}
