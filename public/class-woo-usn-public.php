<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://homescriptone.com
 * @since      1.0.0
 *
 * @package    Woo_Usn
 * @subpackage Woo_Usn/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_Usn
 * @subpackage Woo_Usn/public
 */
if ( !class_exists( "Woo_Usn_Public" ) ) {
    class Woo_Usn_Public {
        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @var      string $plugin_name The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @var      string $version The current version of this plugin.
         */
        private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @param string $plugin_name The name of the plugin.
         * @param string $version The version of this plugin.
         *
         * @since    1.0.0
         */
        public function __construct( $plugin_name, $version ) {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Register the stylesheets for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {
            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in Woo_Usn_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The Woo_Usn_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */
            if ( function_exists( 'is_checkout' ) && is_checkout() ) {
                wp_enqueue_style(
                    $this->plugin_name . '-phone-validator',
                    plugin_dir_url( __FILE__ ) . 'css/jquery-phone-validator.css',
                    array(),
                    $this->version,
                    'all'
                );
                wp_enqueue_style(
                    $this->plugin_name,
                    plugin_dir_url( __FILE__ ) . 'css/woo-usn-public.css',
                    array(),
                    $this->version,
                    'all'
                );
            }
        }

        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {
            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in Woo_Usn_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The Woo_Usn_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */
            if ( class_exists( 'WooCommerce' ) && function_exists( 'is_checkout' ) ) {
                if ( is_checkout() ) {
                    $enqueue_list = array('jquery');
                    $localize_object = array();
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
                    $options = get_option( 'woo_usn_options' );
                    $enqueue_list[] = $this->plugin_name . '-phone-validator';
                    $localize_object['woo_usn_phone_utils_path'] = plugin_dir_url( __FILE__ ) . 'js/jquery-phone-validator-utils.js';
                    $localize_object['wrong_phone_number_messages'] = __( 'The phone number provided isn\'t valid, please correct it.', 'ultimate-sms-notifications' );
                    $localize_object['user_country_code'] = strtolower( $options['default_country_selector'] ?? 'IN' );
                    $localize_object['wc_allowed_countries'] = ( in_array( 'all', $options['wc_allowed_countries'] ) ? array() : $options['wc_allowed_countries'] );
                    wp_enqueue_script(
                        $this->plugin_name,
                        plugin_dir_url( __FILE__ ) . 'js/woo-usn-public.js',
                        $enqueue_list,
                        $this->version,
                        false
                    );
                    wp_localize_script( $this->plugin_name, 'woo_usn_ajax_object', $localize_object );
                }
            }
        }

        /**
         * This method send SMS based on the order ID.
         *
         * @param object $order_id WooCommerce Order ID.
         *
         * @return void
         */
        public function sms_from_thank_you( $order_id ) {
            Woo_Usn_Sms_Public::send_orders_messages( $order_id );
            $order = wc_get_order( $order_id );
            $this->store_customer_consent( $order );
        }

        /**
         * Get customer consent.
         */
        public function get_customer_consent() {
            $options = get_option( 'woo_usn_options' );
            $content = __( 'I would receive any kind of SMS on my phone number.', 'ultimate-sms-notifications' );
            if ( !empty( $options['woo_usn_sms_consent_text_to_display'] ) ) {
                $content = $options['woo_usn_sms_consent_text_to_display'];
            }
            ?>
			<p class="form-row validate-required">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="woo_usn_sms_consent" <?php 
            checked( apply_filters( 'woo_usn_must_send_sms', isset( $_POST['woo_usn_sms_consent'] ) ), true );
            ?> id="woo_usn_consent_sms" />
					<span class="woocommerce-terms-and-conditions-checkbox-text"><?php 
            Woo_Usn_UI_Fields::format_html_fields( $content );
            ?></span>&nbsp;
				</label>
			</p>
		<?php 
        }

        /**
         * Store customer consent.
         */
        public function store_customer_consent( $customer ) {
            $sent_consent = WC()->session->get( 'woo_usn_sms_consent' );
            $sent_consent = ( isset( $sent_consent ) ? $sent_consent : 'off' );
            $customer_id = $customer->get_customer_id();
            update_user_meta( $customer_id, 'woo_usn_allow_sms_sending', $sent_consent );
            global $wpdb;
            $table_name = $wpdb->prefix . '_woousn_subscribers_list';
            $timezone_format = _x( 'Y-m-d  H:i:s', 'timezone date format' );
            //phpcs:disable
            $wpdb->insert( $table_name, array(
                'customer_id'              => $customer_id,
                'customer_consent'         => $sent_consent,
                'customer_registered_page' => 'checkout',
                'date'                     => date_i18n( $timezone_format, false, true ),
                'customer_order_id'        => $customer->get_id(),
            ) );
            //phpcs:enable
        }

        public function store_consent() {
            // store the current consent for sms notifications
            WC()->session->set( 'woo_usn_sms_consent', filter_input( INPUT_POST, 'woo_usn_sms_consent' ) );
        }

        public function save_ccode() {
            // store the country code selected after validating.
            WC()->session->set( 'woousn_pn_billing_country', filter_input( INPUT_POST, 'woousn_pn_billing_country' ) );
        }

        public function validate_pn() {
            $pn_is_valid = filter_input( INPUT_POST, 'woo_usn_pn_is_valid' );
            if ( $pn_is_valid == 'no' ) {
                $options = get_option( 'woo_usn_options' );
                $title = '<strong>' . __( 'Billing Phone', 'woocommerce' ) . '</strong>';
                $message = $title . ': ' . $options['woo_usn_pn_is_not_valid'];
                wc_add_notice( $message, 'error' );
                return;
            }
        }

        public function add_ccode() {
            $options = get_option( 'woo_usn_options' );
            ?>
			<input type="hidden" id="woousn_pn_billing_country" name="woousn_pn_billing_country" value="<?php 
            echo strtolower( $options['default_country_selector'] ?? 'IN' );
            ?>"/>
		<?php 
        }

        public function get_validation_block_html() {
            ?>
		<input type="hidden" id="woousn_pn_valid" name="woo_usn_pn_is_valid" value="no"/>
		<?php 
        }

    }

}