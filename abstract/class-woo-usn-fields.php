<?php

namespace Homescriptone\USN;

abstract class Fields {
    public $screen_slug;

    public $screen_name;

    public $screen_title;

    public $screen_fields;

    public $db_value_name;

    protected $orders_statuses;

    public $notices;

    protected $vendors_list;

    public function __construct() {
        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $this->orders_statuses = wc_get_order_statuses();
        }
        $this->vendors_list = apply_filters( 'woo_usn_vendors_list_compatible', array(
            'dokan'        => __( 'Dokan' ),
            'multivendorx' => __( 'MultivendorX' ),
            'wcfm'         => __( 'WCFM' ),
        ) );
        add_action( 'admin_menu', array($this, 'add_menu'), 20 );
        add_action( 'admin_post', array($this, 'save_settings') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
    }

    protected function get_tag_name() {
        return filter_input( INPUT_GET, 'page' );
    }

    protected function is_assets_page() {
        $page = filter_input( INPUT_GET, 'page' );
        if ( $page == $this->screen_slug ) {
            return true;
        }
        return false;
    }

    public function get_data() {
        return get_option( $this->db_value_name );
    }

    public function save_settings() {
        $posted_data = filter_input_array( INPUT_POST );
        if ( isset( $posted_data[$this->db_value_name] ) ) {
            $woo_usn_options = $posted_data[$this->db_value_name];
            $old_value = get_option( $this->db_value_name, true );
            $data = array_merge_recursive_distinct( $old_value, $woo_usn_options );
            update_option( $this->db_value_name, $data );
        }
        if ( wp_get_referer() ) {
            wp_safe_redirect( wp_get_referer() );
        } else {
            wp_safe_redirect( admin_url( $this->screen_slug, 'admin' ) );
        }
    }

    protected function get_media_message_buttons( $field_name, $default_value ) {
        ob_start();
        return ob_get_clean();
    }

    public abstract function enqueue_scripts();

    public function add_header_menu() {
    }

    public function get_screen_fields() {
        return $this->screen_fields;
    }

    public function set_screen_fields( $fields ) {
        $this->screen_fields = apply_filters( 'woo_usn_fields', $fields, $this->screen_slug );
    }

    public function add_menu() {
        add_submenu_page(
            'ultimate-sms-notifications',
            $this->screen_name,
            $this->screen_title,
            'manage_options',
            $this->screen_slug,
            array($this, 'get_fields')
        );
    }

    public function get_fields() {
        ?>
            <form method="post" action="<?php 
        echo esc_url( admin_url( 'admin-post.php' ) );
        ?>">
            <div class="wrap">
                <?php 
        formulus_input_table(
            $this->screen_slug . '-screen',
            $this->get_screen_fields(),
            false,
            'wp-list-table widefat fixed striped table-view-list'
        );
        ?>
                    <input type="hidden" name="hs_screen_type" value="<?php 
        echo $this->db_value_name;
        ?>" />
                    <input type="hidden" name="woo_usn_screen_type" value="<?php 
        echo $this->screen_slug;
        ?>" />
            </div>
            <?php 
        submit_button( 'Save changes' );
        ?>
            </form>
        <?php 
    }

}
