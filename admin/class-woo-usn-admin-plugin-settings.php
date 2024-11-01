<?php

use Homescriptone\USN\Fields;


class Settings extends Fields {

	// Redefining properties from the parent class.
	public $screen_slug  = 'woo-usn-plugin-settings';
	public $screen_name  = 'WordPress Notifications';
	public $screen_title = 'WordPress Notifications';

	public function __construct() {
		$this->db_value_name = 'woo_usn_options';
		parent::__construct();
		$saved_data = $this->get_data();


        $fields = array(
             'cf7-notifications-label' => array(
                'label'   => esc_html__("Send notifications if Contact Form 7 forms is filled :", 'ultimate-sms-notifications'),
                'label_class' => 'woo_usn-table-label',
                'tr_class' => 'woo-admin-orders-notifications',
                'description' => esc_html__('By enabling it, you will be able to receive a notifications after Contact Form 7 forms is filled.', 'ultimate-sms-notifications'),
                'content'     => formulus_input_fields(
                    'woo_usn_options[cf7-notifications-label]',
                    array(
                        'type'        => 'select',
                        'options'     => array(
                            'yes' => 'Enable',
                            'no' => 'Disable'
                        ),
                        'custom_attributes' => array(
                            'data-toggle' => 'cf7-notifications'
                        )
                    ),
                    isset($saved_data['cf7-notifications-label']) ? $saved_data['cf7-notifications-label'] : 'no'
                )
            ),
             'cf7-notifications-message' => array(
                'label'   => esc_html__("Message you will receive", 'ultimate-sms-notifications'),
                'label_class' => 'woo_usn-table-label',
                'tr_class'    => 'cf7-notifications',
                'content'     => formulus_input_fields(
                    'woo_usn_options[cf7-notifications-message]',
                    array(
                        'type'        => 'textarea',
                        'required'    => true,
                        'input_class' => array('woousn-textarea'),
                        'placeholder' => __('Please put the default message to send.', 'ultimate-sms-notifications'),
                    ),
                    isset($saved_data['cf7-notifications-message']) ? $saved_data['cf7-notifications-message'] : ''
                )
            ),
            'cf7-notifications-numbers' => array(
                'label'   => esc_html__("Phone Numbers to which the message will be sent", 'ultimate-sms-notifications'),
                'label_class' => 'woo_usn-table-label',
                'tr_class'    => 'cf7-notifications',
                'content'     => formulus_input_fields(
                    'woo_usn_options[cf7-notifications-numbers]',
                    array(
                        'type'        => 'text',
                        'required'    => true,
                        // add the media uploader for each notifications type.
                    ),
                    isset($saved_data['cf7-notifications-numbers']) ? $saved_data['cf7-notifications-numbers'] : ''

            )
            )

        );


		$this->set_screen_fields( $fields );
	}
	public function enqueue_scripts() {
		if ( $this->is_assets_page() ) {
			wp_enqueue_script( $this->screen_slug, WOO_USN_URL . '../admin/js/woo-usn-notif-settings.js', array( 'jquery' ), WOO_USN_VERSION );
		}
	}


}
