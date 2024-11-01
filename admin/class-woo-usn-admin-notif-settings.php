<?php

use Homescriptone\USN\Fields;
class Woo_Usn_Notif_SettingScreen extends Fields {
    // Redefining properties from the parent class.
    public $screen_slug = 'ultimate-sms-notifications-woocommerce-notifications';

    public $screen_name = 'WooCommerce Notifications';

    public $screen_title = 'WooCommerce Notifications';

    public function __construct() {
        $this->db_value_name = 'woo_usn_options';
        parent::__construct();
        $saved_data = $this->get_data();
        $fields = array();
        $fields += array(
            'woo-usn-admin-orders-notifications'        => array(
                'label'       => esc_html__( "Send a message from a customer's order details :", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'tr_class'    => 'woo-admin-orders-notifications',
                'description' => esc_html__( 'By enabling it, you will be able to send a customized message from your customer order details.', 'ultimate-sms-notifications' ),
                'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_messages_from_orders]', array(
                    'type'    => 'select',
                    'options' => array(
                        'yes' => 'Enable',
                        'no'  => 'Disable',
                    ),
                ), ( isset( $saved_data['woo_usn_messages_from_orders'] ) ? $saved_data['woo_usn_messages_from_orders'] : 'no' ) ),
            ),
            'woo-usn-user-signup-notifications'         => array(
                'label'       => esc_html__( "Send a message to customer registering new account from the checkout page:", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'description' => esc_html__( 'By enabling it, a message will be sent to this new registered customer.', 'ultimate-sms-notifications' ),
                'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_messages_after_customer_signup]', array(
                    'type'              => 'select',
                    'options'           => array(
                        'yes' => 'Enable',
                        'no'  => 'Disable',
                    ),
                    'custom_attributes' => array(
                        'data-toggle' => 'woo-checkout-user-signup-message',
                    ),
                ), ( isset( $saved_data['woo_usn_messages_after_customer_signup'] ) ? $saved_data['woo_usn_messages_after_customer_signup'] : 'no' ) ),
            ),
            'woo-usn-user-signup-notifications-message' => array(
                'label'       => esc_html__( "Message that the new registered customer will receive", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'tr_class'    => 'woo-checkout-user-signup-message',
                'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_message_to_new_customers]', array(
                    'type'        => 'textarea',
                    'required'    => true,
                    'input_class' => array('woousn-textarea'),
                    'placeholder' => __( 'Please put the default message to send.', 'ultimate-sms-notifications' ),
                ), ( isset( $saved_data['woo_usn_message_to_new_customers'] ) ? $saved_data['woo_usn_message_to_new_customers'] : '' ) ),
            ),
        );
        $fields += array(
            'woo-usn-orders-notifications'     => array(
                'label'       => esc_html__( "Send a message after a customer purchase : ", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'description' => esc_html__( 'By enabling it, an automated message will be sent to the customer along with the WooCommerce email.', 'ultimate-sms-notifications' ),
                'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_message_after_customer_purchase]', array(
                    'type'              => 'select',
                    'options'           => array(
                        'yes' => 'Enable',
                        'no'  => 'Disable',
                    ),
                    'custom_attributes' => array(
                        'data-toggle' => 'woo-orders-notifications-message',
                    ),
                ), ( isset( $saved_data['woo_usn_message_after_customer_purchase'] ) ? $saved_data['woo_usn_message_after_customer_purchase'] : 'no' ) ),
            ),
            'woo-orders-notifications-message' => array(
                'label'       => esc_html__( "Message customer will receive after a successfull purchase", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'tr_class'    => 'woo-orders-notifications-message',
                'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_defaults_messages]', array(
                    'type'        => 'textarea',
                    'required'    => true,
                    'input_class' => array('woousn-textarea'),
                    'placeholder' => __( 'Please put the default message to send.', 'ultimate-sms-notifications' ),
                ), ( isset( $saved_data['woo_usn_defaults_messages'] ) ? $saved_data['woo_usn_defaults_messages'] : '' ) ),
            ),
        );
        if ( isset( $this->orders_statuses ) && !empty( $this->orders_statuses ) ) {
            foreach ( $this->orders_statuses as $status_k => $status ) {
                $key = str_replace( 'wc-', '', $status_k );
                $fields['woo-admin-change-orders-status-to-' . $key] = array(
                    'label'       => esc_html__( "Send a message after changing the order status (WooCommerce) to " . $status . ":", 'ultimate-sms-notifications' ),
                    'label_class' => 'woo_usn-table-label',
                    'tr_class'    => 'woo_usn-description',
                    'description' => esc_html__( 'By activating it, a message will be sent to the customer to inform him about the status of his order. By not filling in some fields below, the message will not be sent in these conditions.', 'ultimate-sms-notifications' ),
                    'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_message_after_order_changed_to_' . $key . ']', array(
                        'type'              => 'select',
                        'options'           => array(
                            'yes' => 'Enable',
                            'no'  => 'Disable',
                        ),
                        'custom_attributes' => array(
                            'data-toggle' => 'woo-admin-change-orders-status-message-to' . $key,
                        ),
                    ), ( isset( $saved_data["woo_usn_message_after_order_changed_to_" . $key] ) ? $saved_data["woo_usn_message_after_order_changed_to_" . $key] : 'no' ) ),
                );
                $fields['woo-admin-change-orders-status-to-' . $key . '-message'] = array(
                    'label'       => esc_html__( "Message that the new registered customer will receive " . $status, 'ultimate-sms-notifications' ),
                    'label_class' => 'woo_usn-table-label',
                    'tr_class'    => 'woo-admin-change-orders-status-message-to' . $key,
                    'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_' . $key . '_messages]', array(
                        'type'        => 'textarea',
                        'required'    => true,
                        'input_class' => array('woousn-textarea'),
                        'placeholder' => __( 'Please put the default message to send.', 'ultimate-sms-notifications' ),
                    ), ( isset( $saved_data['woo_usn_' . $key . '_messages'] ) ? $saved_data['woo_usn_' . $key . '_messages'] : '' ) ),
                );
                $fields['woo-admin-change-orders-status-to-' . $key . '-media'] = array(
                    'label'       => esc_html__( "Media ( Image/Video ) new registered customer will receive ", 'ultimate-sms-notifications' ),
                    'label_class' => 'woo_usn-table-label',
                    'tr_class'    => 'woo-admin-change-orders-status-message-to' . $key,
                    'content'     => $this->get_media_message_buttons( 'woo_usn_options[woo_usn_' . $key . '_messages_media]', ( isset( $saved_data['woo_usn_' . $key . '_messages_media'] ) ? $saved_data['woo_usn_' . $key . '_messages_media'] : false ) ),
                );
            }
        }
        $fields['woo-send-message-to-admin'] = array(
            'label'       => esc_html__( "Send a message to the store owner/manager when a customer has placed an order: ", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'tr_class'    => 'woo_usn-description',
            'description' => esc_html__( 'By enabling it, the shop owner/manager phone number will receive an automated SMS once any purchase is made on his shop.', 'ultimate-sms-notifications' ),
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_sms_to_admin]', array(
                'type'              => 'select',
                'options'           => array(
                    'yes' => 'Enable',
                    'no'  => 'Disable',
                ),
                'custom_attributes' => array(
                    'data-toggle' => 'woo-admin-change-orders-status-message-to-df, woo-admin-change-orders-status-message-to-phone',
                ),
            ), ( isset( $saved_data['woo_usn_sms_to_admin'] ) ? $saved_data['woo_usn_sms_to_admin'] : 'no' ) ),
        );
        $fields['woo-admin-change-orders-status-message-to-df'] = array(
            'label'       => esc_html__( "Message that the new registered customer will receive ", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'tr_class'    => 'woo-admin-change-orders-status-message-to-df',
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_admin_messages_template]', array(
                'type'        => 'textarea',
                'required'    => true,
                'input_class' => array('woousn-textarea'),
                'placeholder' => __( 'Please put the default message to send.', 'ultimate-sms-notifications' ),
            ), ( isset( $saved_data['woo_usn_admin_messages_template'] ) ? $saved_data['woo_usn_admin_messages_template'] : '' ) ),
        );
        $fields['woo-admin-change-orders-status-message-to-phone'] = array(
            'label'       => esc_html__( "Phone Numbers to which the message will be sent", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'tr_class'    => 'woo-admin-change-orders-status-message-to-df',
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_admin_numbers]', array(
                'type'        => 'text',
                'required'    => true,
                'input_class' => array('woousn-textarea'),
            ), ( isset( $saved_data['woo_usn_admin_numbers'] ) ? $saved_data['woo_usn_admin_numbers'] : '' ) ),
        );
        $fields['woo-phone-number-validation'] = array(
            'label'       => esc_html__( "Phone Number Validation : ", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'tr_class'    => 'woo_usn-description',
            'description' => esc_html__( 'By enabling it, the customer\'s telephone number will be validated on the checkout page.', 'ultimate-sms-notifications' ),
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_checkout_phone_number_validation]', array(
                'type'              => 'select',
                'options'           => array(
                    'yes' => 'Enable',
                    'no'  => 'Disable',
                ),
                'custom_attributes' => array(
                    'data-toggle' => 'woo-phone-number-validation-error-mesage',
                ),
            ), ( isset( $saved_data['woo_usn_checkout_phone_number_validation'] ) ? $saved_data['woo_usn_checkout_phone_number_validation'] : 'no' ) ),
        );
        $fields['woo-phone-number-validation-error-mesage'] = array(
            'label'       => esc_html__( "Phone Number Validation error message : ", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'tr_class'    => 'woo-phone-number-validation-error-mesage',
            'description' => esc_html__( 'Define the default message to display if validation failed', 'ultimate-sms-notifications' ),
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_pn_is_not_valid]', array(
                'type'        => 'textarea',
                'required'    => true,
                'input_class' => array('woousn-textarea'),
                'placeholder' => __( 'Please put the default message to send.', 'ultimate-sms-notifications' ),
            ), ( isset( $saved_data['woo_usn_pn_is_not_valid'] ) ? $saved_data['woo_usn_pn_is_not_valid'] : '' ) ),
        );
        if ( class_exists( 'WooCommerce' ) ) {
            $countries = array(
                'all' => esc_html__( 'All Countries', 'ultimate-sms-notifications' ),
            );
            $countries += (include WC()->plugin_path() . '/i18n/countries.php');
            $fields['default_country_selector'] = array(
                'label'       => esc_html__( "Default country selected in the phone field on the checkout page : ", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'content'     => formulus_input_fields( 'woo_usn_options[default_country_selector]', array(
                    'type'        => 'select',
                    'options'     => $countries,
                    'required'    => true,
                    'input_class' => array("default_country_selector"),
                ), ( isset( $saved_data['default_country_selector'] ) ? $saved_data['default_country_selector'] : 'IN' ) ),
            );
            $fields['allowed_countries_for_phone_selector'] = array(
                'label'       => esc_html__( "Countries to make available into the phone field on the checkout page : ", 'ultimate-sms-notifications' ),
                'label_class' => 'woo_usn-table-label',
                'content'     => formulus_input_fields( 'woo_usn_options[wc_allowed_countries][]', array(
                    'type'              => 'select',
                    'options'           => $countries,
                    'custom_attributes' => array(
                        'multiple' => true,
                    ),
                    'input_class'       => array("wc_allowed_countries"),
                    'default'           => ( isset( $saved_data['wc_allowed_countries'] ) ? $saved_data['wc_allowed_countries'] : array('all') ),
                ) ),
            );
        }
        $fields['woo_usn_send_rest_api_messages'] = array(
            'label'       => esc_html__( "Send messages via REST API : ", 'ultimate-sms-notifications' ),
            'label_class' => 'woo_usn-table-label',
            'content'     => formulus_input_fields( 'woo_usn_options[woo_usn_send_rest_api_messages]', array(
                'type'    => 'select',
                'options' => array(
                    'yes' => esc_html__( 'Enable', 'ultimate-sms-notifications' ),
                    'no'  => esc_html__( 'Disable', 'ultimate-sms-notifications' ),
                ),
            ), ( isset( $saved_data['woo_usn_send_rest_api_messages'] ) ? $saved_data['woo_usn_send_rest_api_messages'] : 'no' ) ),
        );
        $this->set_screen_fields( $fields );
    }

    public function enqueue_scripts() {
        if ( $this->is_assets_page() ) {
            wp_enqueue_media();
            wp_enqueue_script(
                $this->screen_slug,
                WOO_USN_URL . '../admin/js/woo-usn-notif-settings.js',
                array('jquery'),
                WOO_USN_VERSION
            );
        }
    }

    public function get_media_message_fields( $name, $value ) {
        echo $this->get_media_message_buttons( $name, $value );
    }

}
