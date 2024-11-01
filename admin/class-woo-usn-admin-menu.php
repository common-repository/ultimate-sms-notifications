<?php

if ( !class_exists( 'Woo_Usn_Admin_Menu' ) ) {
    class Woo_Usn_Admin_Menu {
        /**
         * This function add menus to WP Dashboard.
         *
         * @return void
         */
        public static function add_menus() {
            global $menu, $submenu;
            global $admin_page_hooks, $_registered_pages, $_parent_pages;
            add_menu_page(
                __( 'Ultimate SMS Notifications', 'ultimate-sms-notifications' ),
                __( 'Notifications', 'ultimate-sms-notifications' ),
                'manage_options',
                'ultimate-sms-notifications',
                array('Woo_Usn_Admin_Settings', 'send_sms'),
                plugins_url( 'img/usn.svg', __FILE__ ),
                57
            );
            add_submenu_page(
                'ultimate-sms-notifications',
                __( 'Notifications Channels', 'ultimate-sms-notifications' ),
                __( 'Notifications Channels', 'ultimate-sms-notifications' ),
                'manage_options',
                'ultimate-sms-notifications-channels',
                array('Woo_Usn_Admin_Settings', 'configure_woo_usn_settings')
            );
            add_submenu_page(
                'ultimate-sms-notifications',
                __( 'Logs', 'ultimate-sms-notifications' ),
                __( 'Logs', 'ultimate-sms-notifications' ),
                'manage_options',
                admin_url( 'admin.php?page=ultimate-sms-notifications-pricing' ),
                null
            );
            add_submenu_page(
                'ultimate-sms-notifications',
                __( 'Subscribers', 'ultimate-sms-notifications' ),
                __( 'Subscribers', 'ultimate-sms-notifications' ),
                'manage_options',
                admin_url( 'admin.php?page=ultimate-sms-notifications-pricing' ),
                null
            );
            add_submenu_page(
                'ultimate-sms-notifications',
                __( 'Schedule Notifications', 'ultimate-sms-notifications' ),
                __( 'Schedule Notifications', 'ultimate-sms-notifications' ),
                'manage_options',
                admin_url( 'admin.php?page=ultimate-sms-notifications-pricing' ),
                null
            );
            add_submenu_page(
                'ultimate-sms-notifications',
                __( 'Contact Lists', 'ultimate-sms-notifications' ),
                __( 'Contact Lists', 'ultimate-sms-notifications' ),
                'manage_options',
                admin_url( 'admin.php?page=ultimate-sms-notifications-pricing' ),
                null
            );
            $submenu['ultimate-sms-notifications'][0][0] = __( 'Quick & Bulk Notifications', 'ultimate-sms-notifications' );
        }

    }

}