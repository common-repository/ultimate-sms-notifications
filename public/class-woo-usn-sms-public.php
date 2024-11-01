<?php

if ( !class_exists( 'Woo_Usn_Sms_Public' ) ) {
    class Woo_Usn_Sms_Public {
        public function send_cf7_notifications() {
            $saved_data = get_option( 'woo_usn_options' );
            if ( isset( $saved_data['cf7-notifications-label'] ) && $saved_data['cf7-notifications-label'] == "enable" ) {
                global $usn_sms_loader;
                $phone_numbers = $saved_data['cf7-notifications-numbers'];
                $message = $saved_data['cf7-notifications-message'];
                $phone_numbers = explode( ",", $phone_numbers );
                foreach ( $phone_numbers as $phone_number ) {
                    $usn_sms_loader->send_sms(
                        $phone_number,
                        $message,
                        false,
                        false,
                        false
                    );
                }
            }
        }

        /**
         * Send Message ( SMS | Whatsapp | more ... ) once a new customer made a purchase or notify the shop owner numbers about new purchase.
         */
        public static function send_orders_messages( $order ) {
            global $usn_utility, $usn_sms_loader;
            $_order = new WC_Order($order);
            $country = strtoupper( WC()->session->get( 'woousn_pn_billing_country' ) );
            $_order->set_billing_country( $country );
            $_order->save();
            $_phone_number = $_order->get_billing_phone();
            $options = get_option( 'woo_usn_options' );
            if ( isset( $options['woo_usn_message_after_customer_purchase'] ) || isset( $options['woo_usn_message_after_customer_purchase'] ) && $options['woo_usn_message_after_customer_purchase'] == "yes" ) {
                // change tags with their right values.
                $customer_message = $usn_utility::decode_message_to_send( $order, apply_filters( 'woo_usn_defaults_messages', $options['woo_usn_defaults_messages'], $order ) );
                $media_url = "";
                // send SMS to customer.
                if ( $customer_message && !is_admin() ) {
                    $status = $usn_sms_loader->send_sms(
                        $_phone_number,
                        $customer_message,
                        $country,
                        $media_url
                    );
                    $orders_messages = '<br/><strong>' . __( 'Phone Numbers : ', 'ultimate-sms-notifications' ) . '</strong>' . $_phone_number . '<br/><strong>' . __( 'Messages : ', 'ultimate-sms-notifications' ) . '</strong>' . $customer_message . '<br/><strong>' . __( 'Delivery Messages : ', 'ultimate-sms-notifications' ) . '</strong>' . $status . '<br/>' . 'Sent from <strong>Ultimate SMS & WhatsApp Notifications</strong>';
                    $_order->add_order_note( $orders_messages );
                }
            }
            if ( isset( $options['woo_usn_sms_to_admin'] ) || isset( $options['woo_usn_sms_to_admin'] ) && $options['woo_usn_sms_to_admin'] == "yes" ) {
                $admin_can_receive_messages = $options['woo_usn_sms_to_admin'] == "yes";
                $admin_numbers = esc_attr( $options['woo_usn_admin_numbers'] );
                $admin_message = $usn_utility::decode_message_to_send( $order, apply_filters( 'woo_usn_admin_messages_template', $options['woo_usn_admin_messages_template'], $order ) );
                // send SMS to shop manager.
                if ( isset( $admin_can_receive_messages ) && $admin_can_receive_messages && !is_admin() ) {
                    $explode_admin_phone_numbers = explode( ',', $admin_numbers );
                    $media_url = "";
                    foreach ( $explode_admin_phone_numbers as $admin_pn ) {
                        $status = $usn_sms_loader->send_sms(
                            $admin_pn,
                            $admin_message,
                            $country = false,
                            $media_url
                        );
                        $orders_messages = '<br/><strong>' . __( 'Phone Numbers : ', 'ultimate-sms-notifications' ) . '</strong>' . $admin_pn . '<br/><strong>' . __( 'Messages : ', 'ultimate-sms-notifications' ) . '</strong>' . $admin_message . '<br/><strong>' . __( 'Delivery Messages : ', 'ultimate-sms-notifications' ) . '</strong>' . $status . '<br/>' . 'Sent from <strong>Ultimate SMS & WhatsApp Notifications for WooCommerce</strong>';
                        $_order->add_order_note( $orders_messages );
                    }
                }
            }
            do_action( 'woo_usn_api_messages', $order );
        }

        /**
         * Send message to new customer creating a new account.
         */
        public static function send_messagge_to_customer_signin_up( $customer_obj, $data ) {
            $woo_usn_options = get_option( 'woo_usn_options' );
            if ( isset( $woo_usn_options['woo_usn_messages_after_customer_signup'] ) && $woo_usn_options['woo_usn_messages_after_customer_signup'] == "yes" ) {
                global $usn_utility, $usn_sms_loader;
                $customer = new WC_Customer($customer_obj);
                if ( isset( $_POST['woo_usn_billing_country'] ) ) {
                    $data = filter_input_array( INPUT_POST );
                    $billing_phone = $data['billing_phone'];
                    $billing_country = $data['billing_country'];
                    $billing_fn = $data['billing_first_name'];
                    $billing_ln = $data['billing_last_name'];
                    $customer->set_billing_country( $billing_country );
                    $customer->set_billing_phone( $billing_phone );
                    $template_message = apply_filters( 'woo_usn_message_to_new_customers', $woo_usn_options['woo_usn_message_to_new_customers'], $customer );
                    $customer_message = preg_replace( array('/%store_name%/', '/%customer_name%/', '/%customer_phone_number%/'), array(get_bloginfo( 'name' ), $billing_fn . ' ' . $billing_ln, $usn_utility::get_right_phone_numbers( $billing_country, $billing_phone )), $template_message );
                    $media_url = "";
                    $usn_sms_loader->send_sms(
                        $billing_phone,
                        $customer_message,
                        $billing_country,
                        $media_url
                    );
                }
            }
        }

    }

}