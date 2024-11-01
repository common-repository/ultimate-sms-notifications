<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Usn
 * @subpackage Woo_Usn/includes
 * @link       https://homescriptone.com
 */
if ( !class_exists( 'Woo_Usn' ) ) {
    class Woo_Usn {
        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    1.0.0
         * @var      Woo_Usn_Loader $loader Maintains and registers all hooks for the plugin.
         */
        protected $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         * @var      string $plugin_name The string used to uniquely identify this plugin.
         */
        protected $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    1.0.0
         * @var      string $version The current version of the plugin.
         */
        protected $version;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct() {
            if ( defined( 'WOO_USN_VERSION' ) ) {
                $this->version = WOO_USN_VERSION;
            } else {
                $this->version = '1.0.0';
            }
            $this->plugin_name = 'woo-usn';
            $this->load_dependencies();
            $this->set_locale();
            if ( is_admin() ) {
                $this->define_admin_hooks();
            } else {
                $this->define_public_hooks();
            }
            $this->define_background_hooks();
        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    1.0.0
         */
        private function load_dependencies() {
            require_once WOO_USN_PATH . '../core/class-woo-usn-loader.php';
            require_once WOO_USN_PATH . '../i18n/class-woo-usn-i18n.php';
            require_once WOO_USN_PATH . '../admin/class-woo-usn-admin.php';
            require_once WOO_USN_PATH . '../public/class-woo-usn-public.php';
            $this->loader = new Woo_Usn_Loader();
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Woo_Usn_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    1.0.0
         */
        private function set_locale() {
            $plugin_i18n = new Woo_Usn_i18n();
            $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        }

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    1.0.0
         */
        private function define_admin_hooks() {
            // this is loaded after all wp is loaded.
            add_action( 'wp_loaded', function () {
                new Woo_Usn_Notif_SettingScreen();
                new Settings();
                if ( class_exists( 'Homescriptone\\USN\\Woo_USN_SMS_GW' ) ) {
                    new Homescriptone\USN\Woo_USN_SMS_GW();
                }
                if ( class_exists( 'Homescriptone\\USN\\Woo_USN_WHA_GW' ) ) {
                    new Homescriptone\USN\Woo_USN_WHA_GW();
                }
                if ( class_exists( 'Homescriptone\\USN\\Woo_USN_Viber_GW' ) ) {
                    new Homescriptone\USN\Woo_USN_Viber_GW();
                }
            } );
            $plugin_admin = new Woo_Usn_Admin($this->get_plugin_name(), $this->get_version());
            /**
             * Admin part.
             */
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
            $this->loader->add_action( 'admin_notices', $plugin_admin, 'check_requirements' );
            $this->loader->add_action(
                'admin_menu',
                'Woo_Usn_Admin_Menu',
                'add_menus',
                10,
                1
            );
            /**
             * Send message if order statuses changed.
             */
            $this->loader->add_action(
                'woocommerce_order_status_changed',
                'Woo_Usn_Sms_Admin',
                'send_sms_on_status_change',
                30,
                3
            );
            /**
             * Add metabox into the woocommerce order details.
             */
            $this->loader->add_action( 'add_meta_boxes', 'Woo_Usn_Sms_Admin', 'message_from_orders_metabox' );
            /**
             * AJAX functions.
             */
            $this->loader->add_action( 'wp_ajax_woo_usn-review-answers', $plugin_admin, 'review_answers' );
            // TODO : rewrite
            $this->loader->add_action( 'wp_ajax_woo_usn_send-messages-manually-from-orders', $plugin_admin, 'send_sms_from_orders_by_ajax' );
            // TODO : rewrite
            $this->loader->add_action( 'wp_ajax_woo_usn-get-api-response-code', $plugin_admin, 'get_api_response_code' );
            // TODO : rewrite
            $this->loader->add_action( 'wp_ajax_woo_usn-send-sms-to-contacts', $plugin_admin, 'send_sms_to_cl' );
            // TODO : rewrite
            $this->loader->add_action( 'wp_ajax_woo_usn-get-wc-products-name', $plugin_admin, 'get_product_names' );
            // TODO : rewrite
            $this->loader->add_action( 'wp_ajax_woo-usn-save-settings', $plugin_admin, 'save_settings' );
            if ( class_exists( 'Woo_Usn_Notif_Scheduler_Table' ) ) {
                $woo_usn_table = new Woo_Usn_Notif_Scheduler_Table();
                $this->loader->add_action( 'wp_ajax_woo_usn_get_schedule_status', $woo_usn_table, 'decode_schedule_messages' );
            }
            /**
             * CPT part.
             */
            $this->loader->add_filter(
                'plugin_action_links',
                $plugin_admin,
                'usn_settings_link',
                11,
                2
            );
            $this->loader->add_action(
                'admin_init',
                $plugin_admin,
                'save_scheduler_settings',
                10,
                1
            );
            $this->loader->add_action( 'wp_ajax_hs_usn_query_wc', $plugin_admin, 'query_wc' );
        }

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    1.0.0
         */
        private function define_public_hooks() {
            $plugin_public = new Woo_Usn_Public($this->get_plugin_name(), $this->get_version());
            $plugin_sms_public = new Woo_Usn_Sms_Public();
            $options = get_option( 'woo_usn_options' );
            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
            $this->loader->add_action(
                'woocommerce_thankyou',
                $plugin_public,
                'sms_from_thank_you',
                99
            );
            if ( isset( $options['woo_usn_sms_consent'] ) && $options['woo_usn_sms_consent'] == "yes" ) {
                $this->loader->add_action( 'woocommerce_checkout_after_terms_and_conditions', $plugin_public, 'get_customer_consent' );
                // $this->loader->add_action('woocommerce_checkout_order_created', $plugin_public, 'store_customer_consent', 15);
            }
            $this->loader->add_action(
                'woocommerce_created_customer',
                $plugin_sms_public,
                'send_messagge_to_customer_signin_up',
                12,
                2
            );
            if ( isset( $options['woo_usn_checkout_phone_number_validation'] ) && $options['woo_usn_checkout_phone_number_validation'] == "yes" ) {
                $this->loader->add_action(
                    'woocommerce_before_checkout_process',
                    $plugin_public,
                    'validate_pn',
                    15
                );
                $this->loader->add_action( 'woocommerce_checkout_billing', $plugin_public, 'get_validation_block_html' );
            }
            $this->loader->add_action(
                'woocommerce_before_checkout_process',
                $plugin_public,
                'save_ccode',
                15
            );
            $this->loader->add_action(
                'woocommerce_checkout_billing',
                $plugin_public,
                'add_ccode',
                99
            );
            $this->loader->add_action( 'wpcf7_submit', $plugin_sms_public, 'send_cf7_notifications' );
            // store consent
            $this->loader->add_action(
                'woocommerce_before_checkout_process',
                $plugin_public,
                'store_consent',
                99
            );
        }

        private function define_background_hooks() {
            $plugin_admin = new Woo_Usn_Admin($this->get_plugin_name(), $this->get_version());
            $this->loader->add_action( 'plugins_loaded', $plugin_admin, 'create_tables' );
            // if (usn_fs()->is__premium_only()) {
            //     // $obj = new Woo_Usn_WP_Job_Manager();
            //     // $this->loader->add_action('job_manager_send_notification', $obj, 'execute_background_process', 99, 2);
            // }
        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->loader->run();
        }

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @return    string    The name of the plugin.
         * @since     1.0.0
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @return    Woo_Usn_Loader    Orchestrates the hooks of the plugin.
         * @since     1.0.0
         */
        public function get_loader() {
            return $this->loader;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @return    string    The version number of the plugin.
         * @since     1.0.0
         */
        public function get_version() {
            return $this->version;
        }

    }

}