<?php

namespace Homescriptone\USN;

abstract class Gateways{

    public $tab_slug;
    public $fields;
    public $mode_type;

    public $gateways_fields = array();

    public function __construct(){
        add_filter('woo_usn_settings_names', array( $this, 'add_settings' ) );
        if ( $this->is_assets_page() ) {
            $this->gateways_fields = apply_filters( 'woo_usn_gateways', array() , $this->tab_slug );
            add_action('woo_usn_add_settings_tabs', array( $this, 'display_setting_fields'));
            add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }

       protected function is_assets_page() {
        $page = filter_input( INPUT_GET, 'page' );
        $tab  = filter_input( INPUT_GET, 'tab' );
        if (  $tab == $this->tab_slug ) {
            return true;
        }
        return false;
    }

    protected function get_tag_name() {
        return filter_input( INPUT_GET, 'tab' );
    }

    public function enqueue_scripts(){
        ?>
            <style>
                table{
                    width: 100%;
                }
            </style>
            <script type="text/javascript">
                var woo_usn_mode = "<?php echo $this->mode_type; ?>";
            </script>
        <?php
            if ( $this->is_assets_page() ) {
                wp_enqueue_media();
                wp_enqueue_script($this->tab_slug, WOO_USN_URL . '../admin/js/woo-usn-notif-settings.js', array('jquery' ), WOO_USN_VERSION);
            }

    }

    abstract function add_settings( $tab_name );

    public function set_gateway_fields( $fields ) {
        $this->gateways_fields = $fields;
    }


    public function get_gateway_fields(){
        return $this->gateways_fields;
    }

    public function display_setting_fields($tab_name){
        if ( $tab_name == $this->tab_slug ) {
             $this->get_setting_fields( $tab_name );
            // $this->get_testing_message_fields();
        }
    }


    public function get_testing_message_fields(){
         $fields = array();
         $fields['testing-phone-number'] =  array(
            'label'   => esc_html__("Phone Number : ", 'ultimate-sms-notifications'),
            'label_class' => 'woo_usn-table-label',
            'content'     => formulus_input_fields(
                '',
                array(
                    'type'        => 'text',
                    'input_class'  => array("woo_usn_testing_phone_numbers", "input-text", "woo_usn_testing_message"),
                )
            )
         );

          $fields['testing-message'] =  array(
            'label'   => esc_html__("Message : ", 'ultimate-sms-notifications'),
            'label_class' => 'woo_usn-table-label',
            'content'     => formulus_input_fields(
                '',
                array(
                    'type'        => 'textarea',
                    'input_class'  => array("woo_usn_testing_message"),
                )
            )
          );

           $fields['testing-media-url'] =  array(
            'label'   => esc_html__("Add Media : ", 'ultimate-sms-notifications'),
            'label_class' => 'woo_usn-table-label',
            'content'     => formulus_input_fields(
                '',
                array(
                    'type'        => 'text',
                    'input_class'  => array("woo_usn_media_url"),
                    )
                )
            );


            formulus_input_table( 'testing-fields', $fields );

            submit_button( 'Send Message ');
    }


    public function get_setting_fields( $tab_name ) {

        $saved_data = get_option('woo_usn_options', true);

		$fields              = array();
		 $fields['gateways'] = array(
			 'label'       => 'Pick the default gateway to send message',
			 'label_class' => 'woo_usn-table-label',
			 'content'      => formulus_input_fields(
				 "woo_usn_options[$this->mode_type]",
				 array(
					  'type'        => 'select',
					  'options'     => array_keys( $this->get_gateway_fields() ),
					  'input_class' => array( 'woo_usn_select_default_gw' ),
                 ),
                 isset( $saved_data[$this->mode_type])? $saved_data[$this->mode_type] : current(array_keys( $this->get_gateway_fields() ))
			 )
		 );

         foreach ( $this->get_gateway_fields() as $gw_fields_id => $gw_fields ) {

            foreach ( $gw_fields as $gwf_id => $gwd ) {
                  $fields[$gw_fields_id.$gwf_id] = array(
                      'label' => $gw_fields_id . " ".  current(array_keys( $gwd ) ),
                       'label_class' => 'woo_usn-table-label',
                      'tr_class' => str_replace(' ', '-', strtolower($gw_fields_id) ) . ' woousngw-selected',
                      'content' => formulus_input_fields(
                          "woo_usn_options[api_keys][$this->mode_type][".str_replace(' ', '-', strtolower($gw_fields_id) )."][$gwf_id]",
                          array(
                              'type'        => current(array_values( $gwd ) ) ,
                              'input_class' => array( 'woo_usn_testing_message' ),
                          ),
                          isset( $saved_data['api_keys'][$this->mode_type][str_replace(' ', '-', strtolower($gw_fields_id) )][$gwf_id] ) ?
                              $saved_data['api_keys'][$this->mode_type][str_replace(' ', '-', strtolower($gw_fields_id) )][$gwf_id] :
                              'DEFINE_YOUR_API_KEYS_HERE'
                      )
                  );
            }


         }

		 formulus_input_table( 'gateways', $fields );
         echo wp_kses_post( '<br/>' );
         echo wp_sprintf("Find out, how to retrieve your credentials in order to use the plugin Ultimate SMS Notifications without hassle <a href='%s' target='_blank'>here</a>", 'https://docs.ultimatesmsnotifications.com/message-channels') ;
		 submit_button( 'Save Credentials' );
	}


}
