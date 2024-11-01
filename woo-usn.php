<?php
/**
 *
 * Plugin Name:       Ultimate SMS Notifications for WooCommerce
 * Plugin URI:        https://ultimatesmsnotifications.com?utm_source=wpdotorg&utm_campaign=free
 * Description:       Send any kind of notifications by SMS and WhatsApp from your WooCommerce store in few clicks.
 * Version:           1.12
 * Author:            UltiWP
 * Author URI:        https://ultimatesmsnotifications.com?utm_source=customer_websites&utm_medium=plugin_page
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       ultimate-sms-notifications
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0.0
 * Requires Plugins: woocommerce
 *
  */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) && ! defined( 'ABSPATH' ) ) {
	die;
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );


require plugin_dir_path( __FILE__ ) . '/abstract/constants.php';
require plugin_dir_path( __FILE__ ) . '/require.php';

/**
 * This function the core of the plugin.
 */
if ( ! function_exists('run_woo_usn') ) {
    function run_woo_usn() {
        $plugin = new Woo_Usn();
        $plugin->run();
    }
    run_woo_usn();
}



