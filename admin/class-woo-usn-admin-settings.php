<?php

/**
 * This class is responsible to display the settings page for the plugin.
 */
if ( !class_exists( 'Woo_Usn_Admin_Settings' ) ) {
    class Woo_Usn_Admin_Settings {
        public static function display_message_templates() {
            $options = get_option( 'woo_usn_options', true );
            $whatsapp_creds = $options['api_keys']['wha']['twilio-whatsapp'];
            $account_sid = $whatsapp_creds['account-sid'];
            $auth_token = $whatsapp_creds['auth-token'];
            $url = "https://{$account_sid}:{$auth_token}@content.twilio.com/v1/Content?PageSize=500&cached=" . random_int( 0, 3000 );
            $message_template_req = wp_remote_get( $url, array(
                'timeout' => 60,
            ) );
            $message_templates = wp_remote_retrieve_body( $message_template_req );
            $message_templates = json_decode( $message_templates, true );
            $message_templates = $message_templates['contents'];
            $message_ls = array();
            foreach ( $message_templates as $message_tmp ) {
                $message_ls[$message_tmp['sid']] = $message_tmp['friendly_name'];
            }
            ?>
            <div class="wrap">
                <?php 
            $fields = array();
            $fields['gateways'] = array(
                'label'       => 'Message Templates : ',
                'label_class' => 'woo_usn-table-label',
                'content'     => formulus_input_fields( "woo_usn_options[messags-templates]", array(
                    'type'        => 'select',
                    'options'     => $message_ls,
                    'input_class' => array('woo_usn_select_default_gw'),
                ) ),
            );
            formulus_input_table( 'gateways', $fields );
            ?>
            </div>
        <?php 
            foreach ( $message_templates as $message_tmp ) {
                $types_details = $message_tmp['types'];
                $variables = $message_tmp['variables'];
                ?><div class="usn-message-contents" data-sid="<?php 
                echo $message_tmp['sid'];
                ?>"> <textarea class="usn-code-mirror"> Message Content : <?php 
                echo woo_usn_json_pretty_print( $types_details );
                ?>
                Message Variables : <?php 
                echo woo_usn_json_pretty_print( $variables );
                ?></textarea>
            <br>
                <div class="wrap usn-variable">
                    <form method="post" class="usn-form-field" data-sid="<?php 
                echo $message_tmp['sid'];
                ?>">
                        <input type="hidden" name="content_sid" value="<?php 
                echo $message_tmp['sid'];
                ?>" />
                    <?php 
                foreach ( $variables as $var_id => $variable ) {
                    echo "Variable " . $var_id . " : ";
                    echo "<input type='text' class='woo-usn-vars' name='var[" . $var_id . "]' value='" . $variable . "' />";
                    echo "<br/>";
                }
                ?>
                    </form>


                </div>
            </div>
            <?php 
            }
            ?>
        <p class="wrap" style="font-weight:500;">
            Use this text as message for Twilio WhatsApp messaging :
        </p>
            <p class="woo-usn-message-to-send" >
            </p>
	    <?php 
        }

        /**
         * Function for configure settings.
         */
        public static function configure_woo_usn_settings() {
            do_action( 'woo_usn_before_adding_settings_tab' );
            ?>
		<?php 
            if ( isset( $_GET ) && isset( $_GET['tab'] ) && !wp_verify_nonce( '_wpnonce' ) ) {
                $active_tab = filter_input( INPUT_GET, 'tab' );
            } else {
                $url = $_SERVER['REQUEST_URI'] . "&tab=sms-api";
                header( "Location: {$url}" );
            }
            $settings_names = apply_filters( 'woo_usn_settings_names', array(), $active_tab );
            $settings_names['coming-soon'] = array(
                'url'   => 'https://ultimatesmsnotifications.com/coming-soon?utm_source=' . get_site_url(),
                'title' => __( 'Coming soon', 'ultimate-sms-notifications' ),
            );
            ?>
		<div class="wrap">
			<?php 
            settings_errors();
            ?>

			<h2 class="woousn nav-tab-wrapper">
				<?php 
            foreach ( $settings_names as $keyname => $keyvalues ) {
                $class_name = ( $active_tab === $keyname ? 'woousn-tab-active nav-tab-active' : '' );
                ?>
					<a href="<?php 
                echo wp_kses_post( $keyvalues['url'] );
                ?>"
					   class="woousn-tab nav-tab <?php 
                echo esc_attr( $class_name );
                ?>"> <?php 
                echo wp_kses_post( $keyvalues['title'] );
                ?></a>
					<?php 
            }
            ?>
			</h2>


			<form method="post" action="<?php 
            echo esc_url( admin_url( 'admin-post.php' ) );
            ?>">
				<div class="hs-p-wrapper">
                    <?php 
            do_action( 'woo_usn_add_settings_tabs', $active_tab );
            ?>
				</div>
			</form>
		</div>
		<?php 
        }

        /**
         * This allows to send SMS from the dashboard.
         *
         * @return void
         */
        public static function send_sms() {
            $settings_names = array();
            $settings_names['quick'] = array(
                'url'   => '?page=ultimate-sms-notifications&mode=quick',
                'title' => __( 'Quick Notifications', 'ultimate-sms-notifications' ),
            );
            $settings_names['bulk'] = array(
                'url'   => admin_url( 'admin.php?page=ultimate-sms-notifications-pricing' ),
                'title' => __( 'Bulk Notifications', 'ultimate-sms-notifications' ),
            );
            $active_tab = "quick";
            if ( isset( $_GET ) && isset( $_GET['mode'] ) && !wp_verify_nonce( '_wpnonce' ) ) {
                $active_tab = filter_input( INPUT_GET, 'mode' );
            }
            ?>
            <div class="wrap">
			<?php 
            settings_errors();
            ?>

			<h2 class="woousn nav-tab-wrapper">
				<?php 
            foreach ( $settings_names as $keyname => $keyvalues ) {
                $class_name = ( $active_tab === $keyname ? 'woousn-tab-active nav-tab-active' : '' );
                ?>
					<a href="<?php 
                echo wp_kses_post( $keyvalues['url'] );
                ?>"
					   class="woousn-tab nav-tab <?php 
                echo esc_attr( $class_name );
                ?>"> <?php 
                echo wp_kses_post( $keyvalues['title'] );
                ?></a>
					<?php 
            }
            ?>
			</h2>


            <div>
                <h3><?php 
            formulus_format_fields( 'Send a ' . $settings_names[$active_tab]['title'] );
            ?></h3>
            </div>

		</div>



		<div class="hs-p-wrapper">

		<div id="sms-block">

			<?php 
            if ( $active_tab == "quick" ) {
                ?>
                    <div class="woo-usn-use-phone-number woo-usn-use-contact-list-premium woo-usn-qs-class" >
                <?php 
                homescript_input_fields( 'woo_usn_testing_numbers', array(
                    'required'    => true,
                    'label'       => '<strong>' . __( 'Enter Phone Number : ', 'ultimate-sms-notifications' ) . '</strong>',
                    'input_class' => array('woo-usn-testing-numbers', 'woousn-text-customs'),
                ) );
                ?>
                </div>
                <?php 
            } elseif ( $active_tab == "bulk" ) {
                ?>
                <div class="woo-usn-use-contact-list  woo-usn-qs-class">
					<?php 
                ?>
				</div>
                <?php 
            }
            formulus_format_fields( "<br/>" );
            do_action( 'woo_usn_send_notifications_from', $active_tab );
            homescript_input_fields( 'woo_usn_testing_messages', apply_filters( 'woo_usn_testing_messages_options', array(
                'type'        => 'textarea',
                'required'    => true,
                'label'       => '<strong>' . __( 'Message to send: ', 'ultimate-sms-notifications' ) . '</strong>',
                'input_class' => array('woousn-textarea', 'woo-usn-testing-messages'),
                'placeholder' => __( 'Type your message here.', 'ultimate-sms-notifications' ),
            ) ) );
            homescript_input_fields( 'woo_usn_testing_status', array(
                'type'        => 'textarea',
                'required'    => true,
                'id'          => 'woo-usn-response-status',
                'input_class' => array('woousn-textarea', 'woo-usn-response-status'),
                'placeholder' => __( 'Type your message here.', 'ultimate-sms-notifications' ),
            ) );
            $sms_message_text = __( 'Send Message', 'ultimate-sms-notifications' );
            submit_button(
                $sms_message_text,
                'primary',
                '',
                false,
                array(
                    'id' => 'woo_usn_testing',
                )
            );
            ?>
		</div>
            <br/>
            <div class="woousn-cl-status" style="display: none;"></div>

            <div class="woousn-body-cl-status"></div>
            <br/>
            </div>
		</div>
		<?php 
        }

        public static function schedule_notifications() {
            ?>
    </form>
<?php 
        }

        public static function get_un_reasons( $reasons ) {
            $module_type = usn_fs()->get_module_type();
            $internal_message_template_var = array(
                'id' => WOO_USN_PLUGIN_ID,
            );
            $plan = usn_fs()->get_plan();
            if ( usn_fs()->is_registered() && is_object( $plan ) && $plan->has_technical_support() ) {
                $contact_support_template = fs_get_template( 'forms/deactivation/contact.php', $internal_message_template_var );
            } else {
                $contact_support_template = '';
            }
            $reason_found_better_plugin = array(
                'id'                => usn_fs()::REASON_FOUND_A_BETTER_PLUGIN,
                'text'              => sprintf( usn_fs()->get_text_inline( 'I found a better %s', 'reason-found-a-better-plugin' ), $module_type ),
                'input_type'        => 'textfield',
                'input_placeholder' => sprintf( usn_fs()->get_text_inline( "What's the %s's name?", 'placeholder-plugin-name' ), $module_type ),
            );
            $long_term_user_reasons = array(
                array(
                    'id'               => usn_fs()::REASON_OTHER,
                    'text'             => "I am not a developer and require assistance with setting up my website",
                    'input_type'       => 'textfield',
                    'internal_message' => $contact_support_template,
                ),
                // $reason_found_better_plugin,
                array(
                    'id'                => usn_fs()::REASON_DIDNT_WORK_AS_EXPECTED,
                    'text'              => "I am currently evaluating plugins for my website.",
                    'input_type'        => 'select',
                    'input_placeholder' => sprintf( usn_fs()->get_text_inline( "Please share the plugins/features you plan to try/to look for, so we can improve ours", 'placeholder-plugin-name' ), $module_type ),
                    'input_placeholder' => "What are the name of other plugins you're considerating now ?",
                ),
                array(
                    'id'   => usn_fs()::REASON_COULDNT_MAKE_IT_WORK,
                    'text' => "I anticipated different functionality and seek clarification. Leave us a message here at <a target='_blank' href='https://homescriptone.freshdesk.com'>https://homescriptone.freshdesk.com</a>",
                ),
                array(
                    'id'                => usn_fs()::REASON_GREAT_BUT_NEED_SPECIFIC_FEATURE,
                    'text'              => "If your website encounters issues, support is accessible via our Freshdesk at <a target='_blank' href='https://homescriptone.freshdesk.com'>https://homescriptone.freshdesk.com</a>",
                    'input_type'        => '',
                    'input_placeholder' => '',
                    'internal_message'  => $contact_support_template,
                ),
                array(
                    'id'                => usn_fs()::REASON_SUDDENLY_STOPPED_WORKING,
                    'text'              => sprintf( usn_fs()->get_text_inline( 'The %s suddenly stopped working', 'reason-suddenly-stopped-working' ), $module_type ),
                    'input_type'        => '',
                    'input_placeholder' => '',
                    'internal_message'  => $contact_support_template,
                ),
            );
            // Woo_Usn_Utility::log_errors( print_r( $reasons, true ));
            @($reasons = array(
                'long-term'  => $long_term_user_reasons,
                'short-term' => array($reason_found_better_plugin),
            ));
            return $reasons;
        }

    }

}