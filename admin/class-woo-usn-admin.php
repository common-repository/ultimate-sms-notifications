<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Usn
 * @subpackage Woo_Usn/admin
 * @link       https://homescriptone.com
 * @since      1.0.0
 */
if ( !class_exists( 'Woo_Usn_Admin' ) ) {
    class Woo_Usn_Admin {
        private $plugin_name;

        private $version;

        private $api_choosed = 'Twilio';

        private $usn_api;

        public function __construct( $plugin_name, $version ) {
            global $usn_sms_loader;
            $this->plugin_name = $plugin_name;
            $this->version = $version;
            $this->usn_api = $usn_sms_loader;
            $options = get_option( 'woo_usn_api_choosed' );
            if ( !$options ) {
                $this->api_choosed = get_option( 'woo_usn_api_choosed' );
            }
        }

        public function enqueue_styles() {
            global $usn_utility;
            if ( !$usn_utility::is_admin_required_assets() ) {
                return;
            }
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/woo-usn-admin.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-toast',
                plugin_dir_url( __FILE__ ) . 'css/woo-usn-snackbar.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-datatables-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery-datatables.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-select2-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery-select2.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'woo-usn-jquery-datepicker-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery-datepicker.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-phone-validator',
                plugin_dir_url( __FILE__ ) . 'css/jquery-phone-validator.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_editor();
            wp_enqueue_style( 'jquery-ui-style' );
            wp_enqueue_style(
                $this->plugin_name . '-bss-modal-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery-modal.css',
                array(),
                $this->version,
                'all'
            );
            $cm_settings['codeEditor'] = wp_enqueue_code_editor( array(
                'type'       => 'text/css',
                'codemirror' => array(
                    'lineWrapping' => true,
                ),
            ) );
            wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
            wp_enqueue_script( 'wp-theme-plugin-editor' );
            wp_enqueue_style( 'wp-codemirror' );
        }

        public function enqueue_scripts() {
            global $usn_utility;
            if ( !$usn_utility::is_admin_required_assets() ) {
                return;
            }
            $woo_usn_ajax_variables = array(
                'woo_usn_ajax_url'        => admin_url( 'admin-ajax.php' ),
                'woo_usn_ajax_security'   => wp_create_nonce( 'woo-usn-ajax-nonce' ),
                'woo_usn_sms_api_used'    => ( get_option( 'woo_usn_api_choosed' ) == '' ? 'Twilio' : get_option( 'woo_usn_api_choosed' ) ),
                'no_contact_list_defined' => wp_sprintf( esc_html__( 'No contact list is configured on your store, please define it %s to be able to send messages', 'ultimate-sms-notifications' ), '<a href="' . admin_url( 'edit.php?post_type=woo_usn-list' ) . '">here</a>' ),
                'reload_message'          => esc_html__( 'We are going to refresh this settings page. Have you saved your settings? If yes, enter "saved."', 'ultimate-sms-notifications' ),
            );
            wp_enqueue_script(
                $this->plugin_name . '-snackbar',
                plugin_dir_url( __FILE__ ) . 'js/woo-usn-snackbar.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/woo-usn-admin.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-datatables-js',
                plugin_dir_url( __FILE__ ) . 'js/jquery-datatables.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-blockUI',
                plugin_dir_url( __FILE__ ) . 'js/jquery-blockui.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-select2',
                plugin_dir_url( __FILE__ ) . 'js/jquery-select2.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-cl',
                plugin_dir_url( __FILE__ ) . 'js/woo-usn-admin-cl.js',
                array(
                    'jquery',
                    $this->plugin_name . '-blockUI',
                    'jquery-ui-core',
                    'jquery-ui-datepicker'
                ),
                $this->version,
                false
            );
            $woo_usn_cl_variables = array(
                'woo_usn_cl_rules_names'            => Woo_Usn_UI_Fields::get_cl_rules_names(),
                'woo_usn_cl_operators_names'        => Woo_Usn_UI_Fields::get_cl_operators_names(),
                'woo_usn_get_payment_methods'       => $usn_utility::get_wc_payment_gateways(),
                'woo_usn_get_shipping_methods'      => $usn_utility::get_wc_shipping_methods(),
                'woo_usn_country'                   => $usn_utility::get_wc_country(),
                'woo_usn_customer_roles'            => $usn_utility::get_wp_roles(),
                'woo_usn_customer_order_status'     => ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() ),
                'woo_usn_input_number_placeholders' => __( 'enter the amount', 'ultimate-sms-notifications' ),
                'woo_usn_text_field'                => __( 'separate domain name by commas', 'ultimate-sms-notifications' ),
                'loader_message'                    => __( 'Loading ...', 'ultimate-sms-notifications' ),
                'woo_usn_cl_table_list'             => __( 'Customer List Details ', 'ultimate-sms-notifications' ),
                'woo_usn_cl_customer_name'          => __( 'Customers Names ', 'ultimate-sms-notifications' ),
                'woo_usn_cl_customer_phonenumber'   => __( 'Customers Phone Numbers ', 'ultimate-sms-notifications' ),
                'woo_usn_cl_security'               => wp_create_nonce( "search-products" ),
            );
            $woo_usn_ajax_variables = array_merge( $woo_usn_ajax_variables, $woo_usn_cl_variables );
            wp_localize_script( $this->plugin_name, 'woo_usn_ajax_object', $woo_usn_ajax_variables );
            // settings page.
            wp_enqueue_script(
                $this->plugin_name . '-phone-validator',
                plugin_dir_url( __FILE__ ) . 'js/jquery-phone-validator.js',
                array('jquery', 'jquery-ui-tooltip'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-phone-validator-utils',
                plugin_dir_url( __FILE__ ) . 'js/jquery-phone-validator-utils.js',
                array('jquery', 'jquery-ui-tooltip'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-select2',
                plugin_dir_url( __FILE__ ) . 'js/jquery-select2.js',
                array('jquery'),
                $this->version,
                false
            );
            $woo_usn_ajax_variables['woo_usn_phone_utils_path'] = plugin_dir_url( __FILE__ ) . 'js/jquery-phone-validator-utils.js';
            $s_ls = array(
                'jquery',
                'jquery-ui-tooltip',
                'wp-hooks',
                $this->plugin_name . '-select2',
                $this->plugin_name,
                $this->plugin_name . '-phone-validator'
            );
            if ( class_exists( 'WooCommerce' ) ) {
                $s_ls[] = 'serializejson';
            } else {
                wp_enqueue_script(
                    $this->plugin_name . '-serializejson',
                    plugin_dir_url( __FILE__ ) . 'js/jquery-serializeJSON.js',
                    $s_ls,
                    $this->version,
                    false
                );
            }
            wp_enqueue_media();
            wp_enqueue_script(
                $this->plugin_name . '-settings',
                plugin_dir_url( __FILE__ ) . 'js/woo-usn-admin-settings.js',
                $s_ls,
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-bs',
                plugin_dir_url( __FILE__ ) . 'js/woo-usn-admin-bs.js',
                $s_ls,
                $this->version,
                false
            );
            wp_localize_script( $this->plugin_name . '-settings', 'woo_usn_ajax_object', $woo_usn_ajax_variables );
        }

        /**
         * Give feedback or reviews on the website or WP.org.
         */
        public function review_answers() {
            $successfull = 0;
            if ( isset( $_POST['type'] ) ) {
                $successfull = 1;
                if ( $_POST['type'] === 'already_give' && wp_verify_nonce( $_POST['security'], 'woo-usn-ajax-nonce' ) ) {
                    update_option( 'woousn_have_already_give_reviews', true );
                } elseif ( $_POST['type'] === 'dismiss' && wp_verify_nonce( $_POST['security'], 'woo-usn-ajax-nonce' ) ) {
                    update_option( 'woousn_dismiss_banner', true );
                }
                if ( 1 === $successfull ) {
                    update_option( 'woo_usn_display_banner', $successfull );
                }
            }
            echo wp_json_encode( array(
                'status' => $successfull,
            ) );
            wp_die();
        }

        public static function usn_settings_link( $links, $file ) {
            if ( preg_match( '/woo-usn\\.php/', $file ) && current_user_can( 'manage_options' ) ) {
                $settings = array(
                    'settings' => '<a href="admin.php?page=ultimate-sms-notifications&tab=sms-api">' . __( 'Settings', 'ultimate-sms-notifications' ) . '</a>',
                );
                $links = array_merge( $settings, $links );
            }
            return $links;
        }

        public function send_sms_from_orders_by_ajax() {
            if ( is_admin() ) {
                $posted_data = filter_input_array( INPUT_POST );
                $security = $posted_data['security'];
                if ( wp_verify_nonce( $security, 'woo-usn-ajax-nonce' ) ) {
                    global $usn_utility;
                    $ajax_data = $posted_data['data'];
                    $order_id = sanitize_text_field( $ajax_data['order-id'] );
                    $message = sanitize_text_field( $ajax_data['messages-to-send'] );
                    $order = wc_get_order( $order_id );
                    $country = $order->get_billing_country();
                    $country_indicator = $usn_utility::get_country_town_code( $country );
                    $_phone_number = $order->get_billing_phone();
                    $phone_number = "+" . $country_indicator . $usn_utility::get_right_phone_numbers( $country_indicator, $_phone_number );
                    if ( !empty( $phone_number ) && !empty( $message ) ) {
                        $message = $usn_utility::decode_message_to_send( $order_id, $message );
                        $api_resp = $this->usn_api->send_sms( $_phone_number, $message, $country );
                        if ( isset( $api_resp['sms_status_message'] ) ) {
                            $orders_messages = '<br/><strong>' . __( 'Phone Numbers : ', 'ultimate-sms-notifications' ) . '</strong>' . $phone_number . '<br/><strong>' . __( 'Message sent : ', 'ultimate-sms-notifications' ) . '</strong>' . $message . '<br/><strong>' . __( 'Delivery Messages Status : ', 'ultimate-sms-notifications' ) . '</strong>' . $api_resp['sms_status_message'] . '<br/>' . 'Sent from <strong>Ultimate SMS & WhatsApp Notifications</strong>';
                            $order->add_order_note( $orders_messages );
                        }
                        if ( isset( $api_resp['wha_status_message'] ) ) {
                            $orders_messages = '<br/><strong>' . __( 'Phone Numbers : ', 'ultimate-sms-notifications' ) . '</strong>' . $phone_number . '<br/><strong>' . __( 'Message : ', 'ultimate-sms-notifications' ) . '</strong>' . $message . '<br/><strong>' . __( 'Delivery Messages Status : ', 'ultimate-sms-notifications' ) . '</strong>' . $api_resp['wha_status_message'] . '<br/>' . 'Sent from <strong>Ultimate SMS & WhatsApp Notifications</strong>';
                            $order->add_order_note( $orders_messages );
                        }
                        if ( $api_resp['sms_status'] == 400 || $api_resp['wha_status'] == 400 ) {
                            formulus_format_fields( "The message isn't sent, refresh the page to see more details." );
                        } else {
                            formulus_format_fields( "The message is sent, refresh the page to see more details." );
                        }
                    } else {
                        esc_html_e( 'Please fill messages and phone numbers fields before press Send.', 'ultimate-sms-notifications' );
                    }
                }
                wp_die();
            }
        }

        public function get_api_response_code() {
            $posted_data = filter_input_array( INPUT_POST );
            $security = $posted_data['security'];
            if ( wp_verify_nonce( $security, 'woo-usn-ajax-nonce' ) ) {
                $ajax_data = $posted_data['data'];
                $testing_numbers = sanitize_text_field( $ajax_data['testing-numbers'] );
                $testing_message = sanitize_text_field( $ajax_data['testing-messages'] );
                $country_code = sanitize_text_field( $ajax_data['country_code'] );
                $status_code = "";
                $testing_message = Woo_Usn_Utility::decode_message_to_send( null, $testing_message );
                if ( !$testing_numbers ) {
                    $status_code = __( 'Please provide an phone number before to press Send SMS.', 'ultimate-sms-notifications' );
                } else {
                    try {
                        $testing_numbers = Woo_Usn_Utility::get_right_phone_numbers( $country_code, $testing_numbers );
                        $testing_numbers = $country_code . $testing_numbers;
                        $media_url = false;
                        if ( isset( $ajax_data['media_url'] ) ) {
                            $media_url = sanitize_url( $ajax_data['media_url'] );
                        }
                        if ( $this->usn_api instanceof Woo_Usn_SMS ) {
                            $return = $this->usn_api->send_sms(
                                $testing_numbers,
                                $testing_message,
                                $country_code,
                                $media_url,
                                array(
                                    'return' => true,
                                )
                            );
                            if ( isset( $return['sms_status_message'] ) ) {
                                $sms_status_code = $return['sms_status_message'];
                                $status_code .= "SMS : {$sms_status_code} ";
                            }
                            if ( isset( $return['wha_status_message'] ) ) {
                                $wha_status_code = $return['wha_status_message'];
                                $status_code .= "WhatsApp : {$wha_status_code} ";
                            }
                            $status_code = apply_filters(
                                'woo_usn_edit_status_code_single_notifications',
                                $status_code,
                                $testing_numbers,
                                $testing_message,
                                $country_code,
                                $media_url
                            );
                        } else {
                            throw Exception( 'Unable to send message, the core module isnot found' );
                        }
                    } catch ( Exception $errors ) {
                        $status_code = Woo_Usn_Utility::log_errors( $errors );
                    }
                }
            }
            Woo_Usn_UI_Fields::format_html_fields( $status_code );
            wp_die();
        }

        public function check_requirements() {
            // Check if WC is installed.
            $woo_usn_display_banner = get_option( 'woo_usn_display_banner' );
            $display_banner = 'display : block ;';
            if ( $woo_usn_display_banner == 1 ) {
                $reviews_already_give = get_option( 'woousn_have_already_give_reviews' );
                $dismiss_banner = get_option( 'woousn_dismiss_banner' );
                if ( $dismiss_banner || $reviews_already_give ) {
                    $display_banner = 'display : none ;';
                }
            }
            // display newsletters banner.
            ?>
		<div id="woo_usn_banner" class="notice notice-info" style="<?php 
            Woo_Usn_UI_Fields::format_html_fields( $display_banner );
            ?>">
			<p>
				<img id="woorci-logo" src="<?php 
            echo WOO_USN_URL . '../admin/img/usn.svg';
            ?>"
					 style="width : 50px; float :left;">
			<div id="woousn-thank-you" style="display : inline;">
				<p id="woorci-banner-content"><strong
							style="font-size : 15px;"><?php 
            esc_html_e( 'Enjoying Ultimate SMS & WhatsApp Notifications for WooCommerce?', 'ultimate-sms-notifications' );
            ?></strong>
					<br/> <?php 
            esc_html_e( ' Hope that you had a neat and snappy experience with the plugin. Would you please show us a little love by rating us in the WordPress.org?', 'ultimate-sms-notifications' );
            ?>
				</p>
				<p style="position: relative; left: 1px; top: -8px;">
					<a href="https://wordpress.org/support/plugin/ultimate-sms-notifications/reviews/#postform"
					   id="usn-review" target="_blank"><span class="dashicons dashicons-external"></span>Sure! I'd love
						to!</a>
					&nbsp
					<a href="#" id="usn-already-give-review"><span class="dashicons dashicons-smiley"></span>I've
						already
						left a review</a> &nbsp
					<a href="#" id="usn-never-show-again"><span class="dashicons dashicons-dismiss"></span>Never show
						again</a>
				</p>
			</div>
		</div>
		<?php 
            do_action( 'woo_usn_admin_notices' );
        }

        public function send_sms_to_cl() {
            $posted_data = filter_input_array( INPUT_POST );
            $security = $posted_data['security'];
            if ( wp_verify_nonce( $security, 'woo-usn-ajax-nonce' ) ) {
                $cl = $posted_data['data']['contact-list'];
                $msg = $posted_data['data']['testing-messages'];
                $media_url = false;
                if ( isset( $posted_data['data']['media_url'] ) ) {
                    $media_url = $posted_data['data']['media_url'];
                }
                $countries = array_keys( Woo_Usn_Utility::get_worldwide_country_code() );
                $order_lists = Woo_Usn_Customer_List::get_customer_details_from_id( $cl );
                $offset = apply_filters( "woo_usn_bulk_sending_offset", $posted_data['data']['offset'] );
                $limit = apply_filters( "woo_usn_bulk_sending_limit", 50 );
                foreach ( $order_lists as $idx => $order_id ) {
                    if ( is_array( $order_id ) ) {
                        $order_id = $order_id['order_id'];
                    }
                    $order = Woo_Usn_Customer_List::retrieve_orders( $order_id );
                    $pnumber = $order["phone_number"];
                    $country = strtoupper( $order["country"] );
                    if ( !in_array( $country, $countries, true ) || !$pnumber || "" == $pnumber ) {
                        unset($order_lists[$idx]);
                    }
                }
                $total = count( $order_lists );
                if ( $offset + $limit > $total ) {
                    $limit = $total - $offset;
                }
                $order_lists = array_slice( $order_lists, $offset, $limit );
                $offset += $limit;
                $l_orders = array();
                $pnl = array();
                $country_lo = array();
                foreach ( $order_lists as $order_id ) {
                    if ( is_array( $order_id ) ) {
                        $order_id = $order_id['order_id'];
                    }
                    $order = wc_get_order( $order_id );
                    $adress = $order->get_address();
                    $country = strtoupper( $adress["country"] );
                    $pnumber = $adress["phone"];
                    $tmp_msg = Woo_Usn_Utility::decode_message_to_send( $order_id, $msg );
                    $pnumber = Woo_Usn_Utility::split_space_in_numbers( $pnumber );
                    $pnl[$pnumber] = $order_id;
                    $l_orders[$order_id] = $tmp_msg;
                    $country_lo[$order_id] = $country;
                }
                $pnl = array_unique( $pnl );
                $failed = 0;
                $success = 0;
                foreach ( $pnl as $pn => $oid ) {
                    if ( !is_null( $pn ) ) {
                        $sms_obj = new Woo_Usn_SMS();
                        $statu = $sms_obj->send_sms(
                            $pn,
                            $l_orders[$oid],
                            $country_lo[$oid],
                            $media_url,
                            array(
                                'return' => true,
                            )
                        );
                        if ( 200 == $statu['sms_status'] || 200 == $statu['wha_status'] ) {
                            $success += 1;
                        } else {
                            $failed += 1;
                        }
                    }
                }
                echo wp_json_encode( array(
                    'total'   => $total,
                    'offset'  => $offset,
                    'success' => $success,
                    'failed'  => $failed,
                    'message' => __( 'Message details : ', 'ultimate-sms-notifications' ),
                ) );
            }
            wp_die();
        }

        public function get_product_names() {
            $posted_data = filter_input_array( INPUT_POST );
            $products = $posted_data['products'];
            $html = array();
            foreach ( $products as $pid ) {
                $html[$pid] = get_the_title( $pid );
            }
            echo wp_json_encode( array(
                'values' => $html,
            ) );
            wp_die();
        }

        public function save_scheduler_settings() {
            // handle various mode.
            //  $referrer = wp_get_referer();
            //  $referrer = str_replace( admin_url("admin.php?"), '', $referrer );
            //  parse_str($referrer, $query);
            // process scheduled data.
            $uri_req = filter_input_array( INPUT_GET );
            if ( isset( $uri_req['page'] ) && !isset( $uri_req['mode'] ) && isset( $_POST ) && count( $_POST ) > 0 && ($uri_req['page'] === "ultimate-sms-notifications-schedulers" && !isset( $uri_req['mode'] )) ) {
                $posted_data = filter_input_array( INPUT_POST );
                $cl_id = $posted_data['woo_usn_qs_cl'];
                $message = $posted_data['woo_usn_testing_messages'];
                $star_date = $posted_data['woo_usn_schedule_start_date'];
                $end_date = $posted_data['woo_usn_schedule_end_date'];
                $recurrence = $posted_data['woo_usn_schedule_recurrence'];
                $media_link = $posted_data['woo_usn_media_link'];
                $uid = date( 'Ymd' ) . '-' . Woo_Usn_Utility::generate_random_string( 8 );
                Woo_USN_Notifications_Schedulers::add_schedule_notifications(
                    $message,
                    false,
                    $cl_id,
                    false,
                    $recurrence,
                    $star_date,
                    $end_date,
                    $uid,
                    $media_link
                );
                ?>
				<div class="notice notice-success is-dismissible">
					Your message is scheduled.
				</div>
			  <?php 
            }
        }

        public function save_settings() {
            $posted_data = filter_input_array( INPUT_POST );
            $data = $posted_data['data'];
            $old_data = get_option( 'woo_usn_options' );
            if ( !$old_data ) {
                $old_data = array();
            }
            // ensure addon can save their settings.
            $sdata = $data['woo_usn_options'];
            if ( is_null( $data['woo_usn_options'] ) ) {
                $sdata = array();
            }
            $new_data = array_merge_recursive_distinct( $old_data, $sdata );
            update_option( 'woo_usn_options', $new_data );
            $result = array(
                'data' => get_option( 'woo_usn_options' ),
            );
            echo wp_json_encode( $result );
            wp_die();
        }

        public function create_tables() {
            // create default tables.
            Woo_Usn_Activator::activate();
            // rename sms_gateways columns into gateways in woo_usn_logs
            Woo_Usn_Activator::alter_log_table();
        }

        /**
         * Query WC by ajax.
         */
        public function query_wc() {
            $query = filter_input( INPUT_GET, 'term' );
            $operation_type = 'products';
            $data_found = array();
            if ( 'products' === $operation_type ) {
                $data = hs_usn_get_wc_products( $query );
            } elseif ( 'categories' === $operation_type ) {
                $data = hs_usn_get_wc_categories( $query );
            }
            if ( !empty( $data ) ) {
                foreach ( $data as $data_id => $data_name ) {
                    $data_found[] = array($data_id, $data_name);
                }
            }
            echo wp_json_encode( $data_found );
            wp_die();
        }

    }

}