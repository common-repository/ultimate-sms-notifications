<?php
if ( ! class_exists("Woo_Usn_Globals")) {
    class Woo_Usn_Globals
    {

        /**
         * This function loads the globals variable of the plugin.
         *
         * @return void
         */
        public static function init()
        {
            global $usn_utility;
            global $usn_sms_loader;
            global $usn_api_is_defined;
            global $usn_sms_queued_scheduled_version;
            $usn_utility = new Woo_Usn_Utility();
            $usn_sms_loader = new Woo_Usn_SMS();
            $usn_api_is_defined = false;
            global $woo_usn_db_version;
            $woo_usn_db_version = '1.1';
            global $woo_usn_db_subscribers_version;
            global $woo_usn_alter_db_version;
            $woo_usn_alter_db_version = '1.0';
            $woo_usn_db_subscribers_version = '1.1';
            $usn_sms_queued_scheduled_version = '1.9';
            Woo_Usn_Activator::activate();
        }

    }
}
