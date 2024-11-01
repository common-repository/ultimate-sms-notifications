<?php
/**
 * All the constants related to the plugin are defined here.
 */

if ( ! defined("WOO_USN_VERSION") ) {
    define( 'WOO_USN_VERSION', '1.12' );
}
if ( ! defined("WOO_USN_PATH") ) {
    define( 'WOO_USN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined("WOO_USN_URL") ) {
    define( 'WOO_USN_URL', plugins_url( '/', __FILE__ ) );
}
if ( ! defined("WOO_USN_PLUGIN_ID") ) {
    define( 'WOO_USN_PLUGIN_ID', '6855' );
}
if ( ! defined("WOO_USN_PLUGIN_NAME") ) {
    define( 'WOO_USN_PLUGIN_NAME', 'ultimate-sms-notifications' );
}
if ( ! defined("WOO_USN_SCHEDULER_BATCH_SIZE") ) {
    define( 'WOO_USN_SCHEDULER_BATCH_SIZE', apply_filters( 'woo_usn_scheduler_batch_size', 30 ) );

}
