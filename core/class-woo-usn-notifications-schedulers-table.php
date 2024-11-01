<?php
// phpcs:ignorefile
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    require_once ABSPATH . 'wp-admin/includes/screen.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
    require_once ABSPATH . 'wp-admin/includes/template.php';
}

if (!class_exists('Woo_Usn_Notif_Scheduler_Table')) {
    class Woo_Usn_Notif_Scheduler_Table extends WP_List_Table
    {

        /** Class constructor */
        public function __construct()
        {

            parent::__construct(
                array(
                    'singular' => __('Notif Schedule', 'ultimate-sms-notifications'), // singular name of the listed records
                    'plural' => __('Notif Schedules', 'ultimate-sms-notifications'), // plural name of the listed records
                    'ajax' => false, // does this table support ajax?
                )
            );

            add_action( 'admin_init', array($this, 'process_bulk_action' ) );
          
        }

        public static function delete_all_db_content()
        {
            $getted = filter_input_array(INPUT_GET);
            if (isset($getted['page'], $getted['delete-logs']) && ($getted['page'] === 'ultimate-sms-notifications-schedulers') && ($getted['delete-logs'] == 1)) {
                global $wpdb;
                $table_name = $wpdb->prefix . '__woo_usn_queued_notifications';
                $sql = "TRUNCATE $table_name";
                $wpdb->query($sql);
                wp_redirect(wp_get_referer());
                exit();
            }

        }


        public static function get_customers()
        {

            global $wpdb;

            $sql = "SELECT * FROM {$wpdb->prefix}__woo_usn_queued_notifications";


            return $wpdb->get_results($sql, 'ARRAY_A');
        }


        public static function delete_customer($id)
        {
            global $wpdb;

            $notification = current( Woo_USN_Notifications_Schedulers::get_scheduled_notifications( $id ) );

            // unschedule all notifications programmed related to this id.
            as_unschedule_action( 'woo_usn_start_sending_notifications', array( 'notification' => $notification, 'notification_index' => $notification->schedule_index ), 'woo_usn' );


            $wpdb->delete(
                "{$wpdb->prefix}__woo_usn_queued_notifications",
                array('ID' => $id),
                array('%d')
            );
        }


        public static function record_count()
        {
            global $wpdb;

            $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}__woo_usn_queued_notifications";

            return $wpdb->get_var($sql);
        }


        function column_cb($item)
        {
            return sprintf(
                '<input type="checkbox" name="bulk-delete[]" value="%s" />',
                $item['id']
            );
        }



        // function column_name($item)
        // {

        //     $delete_nonce = wp_create_nonce('woo_usn_sms_logs_delete_nonce');

        //     $title = '<strong>' . $item['name'] . '</strong>';

        //     $actions = array(
        //         'delete' => sprintf('<a href="?page=%s&action=%s&_woousn_sms_logs=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
        //     );

        //     return $title . $this->row_actions($actions);
        // }



        function get_columns(  )
        {
            $columns = array(
                'cb' => '<input type="checkbox" />',
                'message_to_send' => __('Message to send', 'ultimate-sms-notifications'),
                'phone_number' => __('Phone Number', 'ultimate-sms-notifications'),
                'contact_list_id' => __('Contact List Related', 'ultimate-sms-notifications'),
                'msg_scheduled_run' => __('Schedule Message already Sent', 'ultimate-sms-notifications'),
                'schedule_start_date' => __('Schedule Start Date ', 'ultimate-sms-notifications'),
                'schedule_end_date' => __('Schedule End Date', 'ultimate-sms-notifications'),
                'media_link' => __('Media Link', 'ultimate-sms-notifications'),
                'schedule_occurence' => __('Reccurence', 'ultimate-sms-notifications'),
                'status' => __('Status', 'ultimate-sms-notifications'),
            );

            return $columns;
        }


        public function get_bulk_actions()
        {
            return array(
                'bulk-sending' => 'Start Sending',
                'bulk-stop' => 'Stop sending',
                'bulk-delete' => 'Delete',
            );
        }

        	public function single_row( $item ) {
                echo '<tr>';
                $this->single_row_columns( $item );
                echo '</tr>';
            }


        public function prepare_items()
        {

             $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);


            $per_page = $this->get_items_per_page('sms_logs_per_page', 10 );
            $current_page = $this->get_pagenum();
            $total_items = self::record_count();
            $this->set_pagination_args(
                array(
                    'total_items' => $total_items, // WE have to calculate the total number of items
                    'per_page' => $per_page, // WE have to determine how many items to show on a page
                )
            );
            $this->items = self::get_customers($per_page, $current_page, true);
    
        }

        public function process_bulk_action()
        {
            // // Detect when a bulk action is being triggered...
            // if ('delete' === $this->current_action()) {

            //     // In our file that handles the request, verify the nonce.
            //     $nonce = esc_attr($_REQUEST['_wpnonce']);

            //     if (!wp_verify_nonce($nonce, 'woo_usn_sms_logs_delete_nonce')) {
            //         die('Go get a life script kiddies');
            //     } else {
            //         self::delete_customer(absint($_GET['customer']));

            //         // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            //         // add_query_arg() return the current url
            //         wp_redirect(esc_url_raw(add_query_arg()));
            //         exit;
            //     }
            // }

      

            // ids of elements.
            if ( isset( $_POST['bulk-delete'] ) ) {
                $delete_ids = esc_sql($_POST['bulk-delete']);
            }
            
            // If the delete bulk action is triggered
            if (
                (isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
                || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
            ) {

                // loop over the array of record IDs and delete them
                foreach ($delete_ids as $id) {
                    self::delete_customer($id);

                }
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }


            // if the action is start sending
            if ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-sending' ) {
                // loop over the array of record IDs and force resending by setting to false.
                foreach ($delete_ids as $id) {
                    Woo_USN_Notifications_Schedulers::update_db_schedule_info( $id, array( 'msg_scheduled_run' => 0 ) );
                }
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }


             // if the action is stop sending
             if ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-stop' ) {
                // loop over the array of record IDs and force resending by setting to false.
                foreach ($delete_ids as $id) {
                    Woo_USN_Notifications_Schedulers::update_db_schedule_info( $id, array( 'msg_scheduled_run' => 1 ) );
                }
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }

        }

        function column_name($item)
    {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Edit', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&element=%s">' . __('Delete', 'supporthost-admin-table') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );

        return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
    }





    	public function column_default( $item, $column_name ) {
            switch ( $column_name ) {
                case 'message_to_send':
                case 'phone_number':
                case 'schedule_start_date':
                case 'schedule_end_date':
                    if ( $item[$column_name] != "" ) {
                        return $item[$column_name];
                    }
                    return "N/A";

                case 'schedule_occurence':
                     if ( $item[$column_name] == "now" ) {
                         return "Once";
                     }
                     return $item[$column_name];

                case 'msg_scheduled_run':
                    if ( $item[$column_name] == 1 ) {
                        return "Yes";
                    }
                    return "False";

                case 'media_link':
                    if ( $item[$column_name] != "" ) {
                        return "<a href='$item[$column_name]' target='_blank'>Click to view</a>";
                    }
                    return "N/A";

                case 'contact_list_id':
                    return "<a href='".admin_url("post.php?post=".$item[$column_name]."&action=edit")."'>".get_the_title($item[$column_name])."</a>";

                case 'status':
                    return "<a class='woo-usn-status-sent woo-usn-status' href='#sent' data-schedule-status='sent' data-schedule-id='".$item['id']."'>Sent</a> | <a class='woo-usn-status-failed woo-usn-status' data-schedule-status='failed' data-schedule-id='".$item['id']."' href='#failed'>Failed</a>";


                default:
                    return print_r( $item, true ); // Show the whole array for troubleshooting purposes
            }
        }


        public function decode_schedule_messages(){
            $posted_data = filter_input_array( INPUT_POST );
            $status = $posted_data['data']['status'];
            $schedule_id = $posted_data['data']['schedule_id'];
            $schedule = current( Woo_USN_Notifications_Schedulers::get_scheduled_notifications( $schedule_id ) );

            $root_path = WP_CONTENT_DIR . '/uploads/homescriptone/woo-usn/schedulers/logs';
            $message = $schedule->message_to_send;
            $schedule_index =  $schedule->schedule_index;
            $cl_id = $schedule->contact_list_id;

            $filepaths = $root_path . '/'.$cl_id.'/'.$schedule_index;

            $search_results = glob("$filepaths/*");

            $results = array();
            foreach( $search_results as $result ){
                $nresult = str_replace( "\n", '', $result, $nb_replaced );
                $is_copied = copy( $result, $nresult );
                if ( $is_copied ) {
                    $results = @array_merge_recursive( $results, wp_json_file_decode( $nresult, array( 'associative' => true ) )  );
                    sleep(5 );
                    unlink( $nresult );
                }
            }
            if ( $status == "sent" ) {
                echo wp_json_encode( $results['success'] );
            } else {
                echo wp_json_encode( $results['failed'] );
            }
            wp_die();
        }


    }



}



