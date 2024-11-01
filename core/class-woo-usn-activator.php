<?php

if ( !class_exists( 'Woo_Usn_Activator' ) ) {
    class Woo_Usn_Activator {
        /**
         * Activate function.
         */
        public static function activate() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            global $wpdb;
            $table_name = $wpdb->prefix . '_woousn_subscribers_list';
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
            if ( !$wpdb->get_var( $query ) == $table_name ) {
                $sql = "CREATE TABLE {$table_name} \n                                (\n                                    id mediumint(20) NOT NULL AUTO_INCREMENT,\n                                    customer_id mediumint(255) NOT NULL,\n                                    customer_consent text NOT NULL,\n                                    customer_registered_page text NOT NULL,\n                                    customer_order_id VARCHAR(255),\n                                    date datetime DEFAULT '2022-12-12 00:00:00' NOT NULL,\n                                    PRIMARY KEY  (id)\n                                ) {$charset_collate};";
                dbDelta( $sql );
            }
        }

        public static function alter_log_table() {
        }

    }

}