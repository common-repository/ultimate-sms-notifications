<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
if ( !class_exists( 'Woo_Usn_Sms_Admin' ) ) {
    class Woo_Usn_Sms_Admin {
        public static function send_sms_on_status_change( $order_id, $old_status, $new_status ) {
            global $usn_utility;
            global $usn_sms_loader;
            $_order = new WC_Order($order_id);
            $country = $_order->get_billing_country();
            $country_indicator = $usn_utility::get_country_town_code( $country );
            $_phone_number = $_order->get_billing_phone();
            $phone_number = $usn_utility::get_right_phone_numbers( $country_indicator, $_phone_number );
            $phone_number = "+" . $country_indicator . $phone_number;
            $order_statuses = wc_get_order_statuses();
            $options = get_option( 'woo_usn_options' );
            foreach ( $order_statuses as $os_key => $os_value ) {
                $order_status = str_replace( 'wc-', '', $os_key );
                if ( $new_status != $order_status ) {
                    continue;
                }
                if ( $options['woo_usn_message_after_order_changed_to_' . $order_status] == "yes" ) {
                    $message = $options['woo_usn_' . $order_status . '_messages'];
                    $message = $usn_utility::decode_message_to_send( $order_id, $message );
                    $media_url = "";
                    $status_code = $usn_sms_loader->send_sms(
                        $_phone_number,
                        $message,
                        $country_indicator,
                        $media_url
                    );
                    $orders_messages = '<br/><strong>' . __( 'Phone Numbers : ', 'ultimate-sms-notifications' ) . '</strong>' . $phone_number . '<br/><strong>' . __( 'Message sent : ', 'ultimate-sms-notifications' ) . '</strong>' . $message . '<br/><strong>' . __( 'Delivery Messages Status : ', 'ultimate-sms-notifications' ) . '</strong>' . $status_code . '<br/>' . 'Sent from <strong>Ultimate SMS & WhatsApp Notifications</strong>';
                    $_order->add_order_note( $orders_messages );
                }
            }
        }

        /**
         * Display metabox for sending message from the orders.
         */
        public static function message_from_orders_metabox() {
            $options = get_option( 'woo_usn_options' );
            if ( isset( $options['woo_usn_messages_from_orders'] ) && "yes" == $options['woo_usn_messages_from_orders'] && function_exists( 'wc_get_container' ) ) {
                $obj = new Woo_Usn_Sms_Admin();
                $screen = ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order' );
                add_meta_box(
                    'woo_usn_send_messages',
                    __( 'Send SMS', 'ultimate-sms-notifications' ),
                    array($obj, 'message_box_for_orders'),
                    $screen,
                    'side',
                    'high'
                );
            }
        }

        /**
         * Display a message box who allows shop owner/manager to send SMS
         * directly from customer orders.
         *
         * @param object $order_id WooCommerce Order ID.
         */
        public function message_box_for_orders( $order_id ) {
            global $usn_utility;
            $order = new WC_Order($order_id);
            $id = $order->get_id();
            $order_status = $order->get_status();
            formulus_input_fields( 'woo_usn_messages_to_send', array(
                'type'        => 'textarea',
                'required'    => true,
                'input_class' => array('woo_usn_messages_to_send', 'woousn-textarea'),
                'placeholder' => __( 'Type your message here.', 'ultimate-sms-notifications' ),
                'maxlength'   => 160,
                'return'      => false,
            ) );
            $country_indicator = $usn_utility::get_country_town_code( $order->get_billing_country() );
            $_phone_number = $order->get_billing_phone();
            $phone_number = "+" . $country_indicator . $usn_utility::get_right_phone_numbers( $country_indicator, $_phone_number );
            ?>
            <input type="submit" name="woo_usn_sms_submit" id="woo_usn_sms_submit" class="button button-primary" value="<?php 
            esc_html_e( 'Send', 'ultimate-sms-notifications' );
            ?>" style="width:80px; word-wrap: break-word;">
            <input type="hidden" name="woo_usn_phone_numbers" value="<?php 
            echo $phone_number;
            ?>" style="width:80px; word-wrap: break-word;">

            <br/>
            <br/>
            <textarea id="phone_number" class="woousn-textarea" maxlength='160' order_id='<?php 
            echo esc_attr( $id );
            ?>' order_status='<?php 
            echo esc_attr( $order_status );
            ?>' rows="5" style="display : none; height:83px; width : 254px;" readonly></textarea>
            <br/>
            <div class="woousn-cl-loader" style="display: none;"></div>
            <?php 
        }

    }

}