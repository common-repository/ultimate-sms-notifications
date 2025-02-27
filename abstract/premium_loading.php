<?php

/**
 * We load all the necessary for the pro version.
 */
if ( !function_exists( 'usn_fs' ) ) {
    function usn_fs() {
        global $usn_fs;
        if ( !isset( $usn_fs ) ) {
            if ( !defined( 'WP_FS__ENABLE_GARBAGE_COLLECTOR' ) ) {
                define( 'WP_FS__ENABLE_GARBAGE_COLLECTOR', true );
            }
            // Include Freemius SDK.
            require_once WOO_USN_PATH . '../vendor/freemius/start.php';
            $usn_fs = fs_dynamic_init( array(
                'id'             => WOO_USN_PLUGIN_ID,
                'slug'           => WOO_USN_PLUGIN_NAME,
                'type'           => 'plugin',
                'public_key'     => 'pk_f19d3d36fc8f76c04152ca10e914b',
                'is_premium'     => false,
                'premium_suffix' => 'PRO',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'slug'    => WOO_USN_PLUGIN_NAME,
                    'contact' => false,
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $usn_fs;
    }

    usn_fs();
    /**
     * Load Freemius for USN.
     *
     * @since 1.9.0
     */
    do_action( 'usn_fs_loaded' );
    /**
     * Load Freemius for USN.
     *
     * @since 1.9.0
     */
    do_action( '_loaded' );
    usn_fs()->add_filter( 'uninstall_reasons', array('Woo_Usn_Admin_Settings', 'get_un_reasons') );
}
usn_fs()->add_action( 'after_uninstall', 'usn_fs_uninstall_cleanup' );