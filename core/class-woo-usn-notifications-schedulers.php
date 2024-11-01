<?php
require_once WOO_USN_PATH . '../vendor/as/action-scheduler.php';



if ( ! class_exists( 'Woo_USN_Notifications_Schedulers' ) ) {
	class Woo_USN_Notifications_Schedulers {

		private $hooks;

		private function set_hooks( $hooks ) {
			$this->hooks = $hooks;
		}

		private function get_hooks() {
			return $this->hooks;
		}

        public function __construct() {
            $this->schedule_notifications();
        }

		public function send_notifications( ) {
			$all_notifications = $this->retrieve_notifications_to_schedule();
            if ( count( $all_notifications ) < 1 ) {
                as_unschedule_all_actions( 'woo_usn_schedule_notifications' );
                return;
            }
			foreach ( $all_notifications as $notification ) {
				$notification_is_allowed = $this->schedule_is_allowed( $notification );

				if ( $notification_is_allowed ) {
					if ( ! as_has_scheduled_action( 'woo_usn_start_sending_notifications', array(
						'notifications' => $notification,
						'notifications_index' =>  $notification['schedule_index'],

						), 'woo_usn' )
					) {
						as_schedule_single_action(
							time(),
							'woo_usn_start_sending_notifications',
							array(
								'notifications' => $notification,
								'notifications_index' =>  $notification['schedule_index'],

							),
							'woo_usn'
						);
					}

				}
			}
		}



		public function create_scheduler_log_folder( $path_to_content ) {
			try {
				if ( ! is_dir( $path_to_content ) ) {
					mkdir( $path_to_content, 0777, true );
				}
			} catch ( Exception $errors ) {
				error_log( print_r( $errors, true ) );
			}
		}

        /**
         * Save schedule notifications to db.
         *
         * @param $message Message to send.
         * @param $phone_number Phone Number.
         * @param $contact_list_id CL list id.
         * @param $is_completed Schedule has finished.
         * @param $recurrence recurrence.
         * @param $start_date Start date
         * @param $end_date End date
         * @param $schedule_index Unique Index
         * @param $media_url Media Url
         * @return void
         */
		public static function add_schedule_notifications( $message, $phone_number = false, $contact_list_id = false, $is_completed = false, $recurrence = 'daily', $start_date = false, $end_date = false, $schedule_index = false, $media_url = false ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . '__woo_usn_queued_notifications';
			$timezone_format = _x( 'Y-m-d  H:i:s', 'timezone date format' );
			$wpdb->insert(
				$table_name,
				array(
					'message_to_send'     => $message,
					'phone_number'        => $phone_number,
					'contact_list_id'     => $contact_list_id,
					'date'                => date_i18n( $timezone_format, false, true ),
					'msg_scheduled_run'   => $is_completed,
					'schedule_occurence'  => $recurrence,
					'schedule_start_date' => $start_date,
					'schedule_end_date'   => $end_date,
					'schedule_index'      => $schedule_index,
                    'media_link'           => $media_url
				)
			);
		}

		public function create_txt_file( $filepath, $mode, $data_to_write ) {
			try {
				$myfile         = fopen( $filepath, $mode );
				$data_to_write .= PHP_EOL;
				fwrite( $myfile, $data_to_write );
				fclose( $myfile );
			} catch ( Exception $errors ) {
				error_log( print_r( $errors, true ) );
			}
		}

		public function read_schedule_status( $file_path ) {
			try {
				$myfile = fopen( $file_path, 'r' );
				if ( ! $myfile ) {
					throw new Exception();
				}
				$contents = '';
				while ( ! feof( $myfile ) ) {
					$contents .= fread( $myfile, 8 * KB_IN_BYTES );
				}
				fclose( $myfile );
				return $contents;
			} catch ( Exception $errors ) {
				$this->create_track_position_file( $file_path, '{"failed":{}, "success":{}}' );
				return $this->read_schedule_status( $file_path );
			}
		}

		public function create_final_position( $file_path ) {
			try {
				$this->create_track_position_file( $file_path, '{"failed":{}, "success":{}}' );
			} catch ( Exception $errors ) {
				error_log( print_r( $errors , true ) );
			}
		}

		public function read_schedule_position( $file_path ) {
			try {
				$myfile = fopen( $file_path, 'r' );
				if ( ! $myfile ) {
					throw new Exception();
				}
				$contents = '';
				while ( ! feof( $myfile ) ) {
					$contents .= fread( $myfile, 8 * KB_IN_BYTES );
				}
				fclose( $myfile );
				return $contents;
			} catch ( Exception $errors ) {
				$this->create_track_position_file( $file_path, 0 );
				return $this->read_schedule_position( $file_path );
			}
		}

		public function create_track_position_file( $filepath, $data_to_write ) {
			$this->create_txt_file( $filepath, 'w+', $data_to_write );
		}

		public function store_notifications_status( $filepath, $data_to_write ) {

			$this->create_txt_file( $filepath, 'r+', $data_to_write );
		}


		public static function get_scheduled_notifications( $id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . '__woo_usn_queued_notifications';
			$sql        = "SELECT * from $table_name WHERE id=$id";
			return $wpdb->get_results( $sql );
		}

		/**
		 * Retrieve all saved schedule notifications.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function retrieve_notifications_to_schedule() {
			global $wpdb;
			$table_name = $wpdb->prefix . '__woo_usn_queued_notifications';
			$sql        = "SELECT * from $table_name WHERE msg_scheduled_run = 0";
			return $wpdb->get_results( $sql, ARRAY_A );
		}


		/**
		 * Schedule notifications.
		 *
		 * @return void
		 */
		private function schedule_notifications() {
			$this->set_hooks(
				apply_filters(
					'woo_usn_scheduler_hooks',
					array(
						array(
							'name' => 'woo_usn_start_sending_notifications',
							'callback' => 'start_sending',
							'nb_args' => 2
						),
						array(
							'name' => 'woo_usn_schedule_notifications',
							'callback' => 'send_notifications',
							'nb_args' => 1
						),

					)
				)
			);

			$hooks = $this->get_hooks();
			foreach ( $hooks as  $hook ) {
				add_action( $hook['name'], array( $this, $hook['callback'] ), 10, $hook['nb_args'] );
			}
		}

		public function start_sending( $notification, $notification_index ) {
			$path_to_content = WP_CONTENT_DIR . '/uploads/homescriptone/woo-usn/schedulers/logs';
			$countries         = array_keys( Woo_Usn_Utility::get_worldwide_country_code() );
			$message_to_send        = $notification['message_to_send'];

			$folder_path = $path_to_content . '/' . $notification['contact_list_id'] . '/' . $notification_index;
			$log_file    = $folder_path . '/position.txt';
			$this->create_scheduler_log_folder( $folder_path );
			$current_position = $this->read_schedule_position( $log_file );

			$contact_lists = Woo_Usn_Customer_List::get_customer_details_from_id( $notification['contact_list_id'] );

			// empty customer lists.
			if ( count( $contact_lists ) < 1 ) {
				unlink( $log_file );
				$this->store_notifications_status( $folder_path . "/$current_position.json", '{"failed":{}, "success":{}, "infos":"Contact List empty, unable to send anything."}' );
				self::update_db_schedule_info( $notification['id'], array( 'msg_scheduled_run' => 1 ) );
				as_unschedule_action( 'woo_usn_start_sending_notifications', array( 'notification' => $notification, 'notification_index' => $notification_index ), 'woo_usn' );
				return;
			}

            $cl_list = array();
			foreach( $contact_lists as $oid => $order_id ) {
				$customer     = Woo_Usn_Customer_List::retrieve_orders( $order_id );
				$country_code = $customer['country'];
				$phone_number = $customer['phone_number'];
				$customer_full_name    = $customer['full_name'];
				if ( ! in_array( $country_code, $countries, true ) || ! $phone_number || "" == $phone_number  ) {
					continue;
				}

				$splited_customer_phone_numbers = Woo_Usn_Utility::get_right_phone_numbers( $country_code, $phone_number );

				$customer_fn = array(
					$customer_full_name =>  array(
						'country'      => $country_code,
						'phone_number' => $splited_customer_phone_numbers,
						'customer_id'  => $customer['customer_id'],
						'full_name'    => $customer_full_name
					)
				);

				$cl_list = array_merge_recursive_distinct(
					$cl_list,
					$customer_fn
				);
			}

			$next_position = $current_position + WOO_USN_SCHEDULER_BATCH_SIZE;

			if ( $current_position + WOO_USN_SCHEDULER_BATCH_SIZE > count( $cl_list ) ) {
				$next_position = $current_position + ( count( $cl_list ) - $current_position );
			}

			$cl_list = array_slice( $cl_list, $current_position, $next_position );

			$contents = $this->read_schedule_status( $folder_path .  "/$current_position.json" );
			$contents = json_decode( $contents, ARRAY_A );
            $media_link = $notification['media_link'];
            if ( $media_link == "" || ! $media_link ) {
                $media_link = false;
            }


			foreach ( $cl_list as $idex => $customer ) {
				global $usn_sms_loader;
				$full_name    = $customer['full_name'];
				$customer_id  = $customer['customer_id'];
				$country_code = $customer['country'];
				$phone_number = $customer['phone_number'];

				$notification_reply = $usn_sms_loader->send_sms( $phone_number, $message_to_send, $country_code, $media_link, array( 'return' => true ) );
				$contents = $this->get_status_notifications( $notification_reply,  $contents, $phone_number, $country_code, $message_to_send, $customer_id );
			}
			$contents = json_encode( $contents );
			$this->store_notifications_status( $folder_path ."/$current_position.json", $contents );

			if ( $current_position + WOO_USN_SCHEDULER_BATCH_SIZE > count($cl_list) ) {
				unlink( $log_file );
				self::update_db_schedule_info( $notification['id'], array( 'msg_scheduled_run' => 1 ) );
				as_unschedule_action( 'woo_usn_start_sending_notifications', array( 'notification' => $notification, 'notification_index' => $notification_index ), 'woo_usn' );
			    return;
			}


			$this->create_track_position_file( $log_file, $next_position );
			as_schedule_single_action( time() , 'woo_usn_start_sending_notifications', array(
				'notification' => $notification,
				'notification_index' => $notification_index
			), 'woo_usn', true );

		}

		private function get_status_notifications( $notification_reply, $contents, $phone_number, $country_code, $message_to_send, $customer_id ){


			if ( isset( $notification_reply['sms_status'] ) ) {
				if ( $notification_reply['sms_status'] == 200 ) {
					$contents['success'][] = array(
						'phone_number' => $phone_number,
						'country' => $country_code,
						'message' => $message_to_send,
						'customer_id' => $customer_id,
						'mode_type' => 'sms_status'
					);
				} elseif ( $notification_reply['sms_status'] == 400 ) {
					$contents['failed'][] = array(
						'phone_number' => $phone_number,
						'country' => $country_code,
						'message' => $message_to_send,
						'customer_id' => $customer_id,
						'mode_type' => 'sms_status'
					);
				}
			}

			if ( isset( $notification_reply['wha_status'] ) ) {
				if ( $notification_reply['wha_status'] == 400 ) {
					$contents['failed'][] = array(
						'phone_number' => $phone_number,
						'country' => $country_code,
						'message' => $message_to_send,
						'customer_id' => $customer_id,
						'mode_type' => 'wha_status'
					);
				} elseif ( $notification_reply['wha_status'] == 200  ) {
					$contents['success'][] = array(
						'phone_number' => $phone_number,
						'country'      => $country_code,
						'message'      => $message_to_send,
						'customer_id'  => $customer_id,
						'mode_type'   => 'wha_status'
					);
				}
			}

			return apply_filters( 'woo_usn_bulk_notifications_status', $contents, $phone_number, $message_to_send, $country_code, $customer_id );
		}

		public static function update_db_schedule_info( $schedule_id, $data ) {
			global $wpdb;
			$table_name = $wpdb->prefix . '__woo_usn_queued_notifications';
			$sql        = "UPDATE `$table_name`  ";
			foreach ( $data as $key => $value ) {
				$sql .= "SET `$key` = '$value' ";
			}
			$sql .= "WHERE `$table_name`.`id`=" . $schedule_id;
			$wpdb->query( $sql );
		}

		public function schedule_is_allowed( $schedule_data ) {
			global $usn_utility;
			$date_to_check          = gmdate( 'Y-m-d H:i' );
			$bulk_sms_start_sending = $schedule_data['schedule_start_date'];
			$bulk_sms_end_sending   = $schedule_data['schedule_end_date'];
			if ( $bulk_sms_start_sending && $bulk_sms_end_sending ) {
				return $usn_utility::convert_date_to_timestamp( $bulk_sms_start_sending ) <= $usn_utility::convert_date_to_timestamp( $date_to_check ) && $usn_utility::convert_date_to_timestamp( $date_to_check ) <= $usn_utility::convert_date_to_timestamp( $bulk_sms_end_sending );
			}

			if ( ! $bulk_sms_start_sending && $bulk_sms_end_sending ) {
				return $usn_utility::convert_date_to_timestamp( $date_to_check ) <= $usn_utility::convert_date_to_timestamp( $bulk_sms_end_sending );
			}
			if ( $bulk_sms_start_sending && ! $bulk_sms_end_sending ) {
				return $usn_utility::convert_date_to_timestamp( $bulk_sms_start_sending ) <= $usn_utility::convert_date_to_timestamp( $date_to_check );
			}

			return true;
		}



	}

}


add_action(
	'action_scheduler_init',
	function () {
		$obj = new Woo_USN_Notifications_Schedulers();
        if ( ! as_has_scheduled_action( 'woo_usn_schedule_notifications', array(), 'woo_usn' ) ) {
            as_schedule_single_action(time(),'woo_usn_schedule_notifications', array(), 'woo_usn', true );
        }
	}
);







