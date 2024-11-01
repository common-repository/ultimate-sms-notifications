(function ($) {
  "use strict";

  var woo_usn_cl_data;

  $(document).ready(function () {
    var woo_usn_btn_testing = $("input#woo_usn_testing");
    var woo_usn_messages_fields = $("#woo_usn_messages_to_send");
    var woo_usn_submit_sms = $("#woo_usn_sms_submit");
    var woo_usn_load_defaults_message = $(
      "input#woo_usn_load_default_messages"
    );
    var woo_usn_phone_numbers = $("textarea#phone_number");
    var woo_usn_order_status = woo_usn_phone_numbers.attr("order_status");
    var woo_usn_ajax_loading = $("div.woousn-cl-loader");
    var woo_usn_order_id = woo_usn_phone_numbers.attr("order_id");
    var woo_usn_return_modal_status = $("div.woousn-body-cl-status");
    var woo_usn_modal_status = $("div.woousn-cl-status");
    var woo_usn_api_choosed = $("select#woo_usn_api_to_choose").val();
    $("select#woo_usn_api_to_choose").change(function () {
      woo_usn_api_choosed = this.value;
    });

    var woousn_phone_number_validator = document.querySelector("#woo_usn_testing_numbers");
    try{
      var iti =  window.intlTelInput(woousn_phone_number_validator,{
        initialCountry: "us",
        utilsScript : woo_usn_ajax_object.woo_usn_phone_utils_path
      });
    }  catch(e){
      //console.info(e);
    }

    $('div.woo-usn-qs-class.woo-usn-use-phone-number' ).show();


    // show/hide based on the API
    $("select#woo_usn_api_to_choose").change(function () {
      var api = $(this).children(":selected").val();
      $('div[id^="woo_usn_api"]').hide();
      $('div[id="woo_usn_api_'+api.replace(" ",'').toLowerCase()+'"]').show();
      wp.hooks.doAction( 'woo_usn_settings_api_choosed' , api );
    });


    $('div[id^="woo_usn_api"]').hide();
    $('div[id="woo_usn_api_'+woo_usn_ajax_object.woo_usn_sms_api_used.replace(" ",'').toLowerCase()+'"]').show();
    wp.hooks.doAction( 'woo_usn_settings_api_choosed' , woo_usn_ajax_object.woo_usn_sms_api_used );

    // count numbers of characters
    $(".woousn-textarea").on("keyup", function () {
      $("span.woousn-textcount").empty();
      var limit = $(this).empty().val().length;
      $(
        '<strong><span class="woousn-textcount" style="color : red;">' +
          limit +
          " characters typed </span></strong>"
      ).insertAfter($(this));
    });

    // submit sms from orders
    woo_usn_submit_sms.on("click", function (e) {
      e.preventDefault();
      var woo_usn_messages_to_send = $(
        ".woo_usn_messages_to_send.input-text"
      ).val();
      var woo_usn_phone_numbers = $("input[name='woo_usn_phone_numbers']").val();
      var data = {
        "messages-to-send": woo_usn_messages_to_send,
        "phone-number": woo_usn_phone_numbers,
        "order-id": woo_usn_order_id,
        "order-status": woo_usn_order_status,
      };
      woo_usn_ajax_loading.show();
      $.post(
        woo_usn_ajax_object.woo_usn_ajax_url,
        {
          action: "woo_usn_send-messages-manually-from-orders",
          data: data,
          security: woo_usn_ajax_object.woo_usn_ajax_security,
        },
        function (response) {
          woo_usn_ajax_loading.hide();
          $("textarea#phone_number").show().append(response);
        }
      );
      $("textarea#phone_number").hide().empty();
    });

    // test sms sending from general settings
    woo_usn_btn_testing.on("click submit", function (e) {
      e.preventDefault();

      var woo_usn_testing_messages = $("#woo_usn_testing_messages").val();

      if ( woo_usn_ajax_object.is_bulk_notif_page == "1" ) {
        var data = {
          "contact-list": $('select#woo_usn_qs_cl').val(),
          "testing-messages": woo_usn_testing_messages,
        };
        if ( data['contact-list'] == null ) {
          woo_usn_return_modal_status.show().append("<strong>" + woo_usn_ajax_object.no_contact_list_defined + "</strong>");
          return false;
        }
        data['media_url'] = $('input[name="woo_usn_media_link"]').val();
        woo_usn_modal_status.show();
        woo_usn_return_modal_status.empty();
        data['offset'] = 0;
        woo_usn_cl_data = data;
        woo_usn_cl_data.success = 0;
        woo_usn_cl_data.failed = 0;
        woo_usn_cl_data.message = "";
        var html;
        woo_usn_send_bulk_notif( woo_usn_cl_data ).then((d)=>{
            html  = d.message + "<br>";
            html += "<table class='table table-bordered wp-list-table widefat fixed striped table-view-list posts'>";
            html += "<thead>";
            html += "<tr>";
            html += "<th>Success</th>";
            html += "<th>Failed</th>";
            html += "</tr>";
            html += "</thead>";
            html += "<tbody>";
            html += "<tr>";
            html += "<td>"+woo_usn_cl_data['success']+"</td>";
            html += "<td>"+woo_usn_cl_data['failed']+"</td>";
            html += "</tr>";
            html += "</tbody></table>";
        }).catch((r)=>{
            html = r;
        }).finally((r)=>{
            woo_usn_modal_status.hide();
            woo_usn_return_modal_status.show().html("<strong>" + html + "</strong>");
        })
       } else {
            var isValid = iti.isValidNumber();
        if ( !isValid ){
          woo_usn_return_modal_status
          .show().empty()
          .append("<strong>The phone number is not valid.</strong>");
          return;
        }
        var countryData = iti.getSelectedCountryData();
        countryData = countryData.dialCode;

        var woo_usn_testing_numbers = $(".woo-usn-testing-numbers").val();
        var data = {
          "testing-numbers": woo_usn_testing_numbers,
          "testing-messages": woo_usn_testing_messages,
          'country_code' : countryData,
        };

        data['media_url'] = $('input[name="woo_usn_media_link"]').val();
        woo_usn_modal_status.show();
        woo_usn_return_modal_status.empty();
        $.post(
          woo_usn_ajax_object.woo_usn_ajax_url,
          {
            action: "woo_usn-get-api-response-code",
            data: data,
            security: woo_usn_ajax_object.woo_usn_ajax_security,
          },
          function (response) {
            woo_usn_modal_status.hide();
            woo_usn_return_modal_status
              .show()
              .append("<strong>" + response + "</strong>");
          }
        );
      }


    });

    woo_usn_load_defaults_message.on("click submit", function (e) {
      e.preventDefault();
      woo_usn_ajax_loading.show();
      $.post(
        woo_usn_ajax_object.woo_usn_ajax_url,
        {
          action: "get-orders-defaults-messages",
          security: woo_usn_ajax_object.woo_usn_ajax_security,
        },
        function (response) {
          woo_usn_ajax_loading.hide();
          woo_usn_messages_fields.show().append(response);
        }
      );
      woo_usn_messages_fields.empty().hide();
    });





    // // show/hide fields from the settings page.
    // woo_usn_display_settings();
    // $("body").on("change", function () {
    //   woo_usn_display_settings();
    // });
    $('div#wpfooter').hide();

    $('select#woo_usn_api_to_choose').select2();
    $('select#woo_usn_qs_cl').select2();


    $('input[name="woo_usn_qs_pn"]').on('change', function(){
      var recipient_selection_mode = $(this).val();
      $('div.woo-usn-qs-class').hide();
      $('div.woo-usn-qs-class.woo-usn-'+recipient_selection_mode ).show();
    });

    // whatsapp.
    var whatsapp_id_choosed = $("select#woo_usn_api_to_choose").val();
    $("div#woo_usn_whatsapp_api_"+whatsapp_id_choosed).show();

    $("select#woo_usn_api_to_choose").on('change', function(){
      $("div.woo_usn_wha_api").hide();
      whatsapp_id_choosed = $(this).val();
      $("div#woo_usn_whatsapp_api_"+whatsapp_id_choosed).show();
    });




    $("select[name='woo_usn_options[default_country_selector]']").select2();



    $('select.woousn_allcountries').select2();

     function woo_usn_display_country_settings( country ) {
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_message_after_customer_purchase]"]',
          "tr.woo-usn-defaults-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_message_after_order_changed]"]',
          "tr.woo-usn-completed-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_message_after_order_changed]"]',
          "tr.woo-usn-completed-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_sms_to_admin]"]',
          "tr.woo-usn-admin-completed-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_sms_to_vendors]"]',
          "tr.woo-usn-vendor-completed-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_sms_consent]"]',
          "tr.woo-usn-customer-consent"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_messages_after_customer_signup]"]',
          "tr.woo-usn-signup-defaults-messages"
      );
      hs_toggle_display(
          'input[name="woo_usn_options['+country+'][woo_usn_failed_emails_notifications]"]',
          "tr.woo-usn-admin-failed-emails"
      );
    };

     function init_multicountries_select2() {
       $('select.woousn_allcountries').on('change click', function() {
         var country = $(this).val();
         var choice  = prompt( woo_usn_ajax_object.reload_message );
         if ( choice == "saved" ) {
           $.blockUI({message:"Loading settings ..."});
           $.post(
               ajaxurl,
               {
                 "action" : "woo_usn_get_country_settings_multi",
                 "woo_usn_country" : country
               }, function(response) {
                 $('div.woousn-options-tab').html(response);
                 $('select.woousn_allcountries').val(country);
                 $('select.woousn_allcountries').select2();
                 $.unblockUI();
                 $("body").on("change", function () {
                   woo_usn_display_country_settings( country );
                 });
                 init_multicountries_select2();
                 woo_usn_display_country_settings( country );
               }
           );
         }
         return false;
       });
     }

    init_multicountries_select2();



    var urlParams = new URLSearchParams(window.location.search);

      $('#submit').on('click submit', function(e){

        e.preventDefault();
        $.blockUI({message:"Saving changes"});
        var data = $('form').serializeJSON();

        var woo_usn_options = data;
        $.post(
            ajaxurl,{
              "action" : "woo-usn-save-settings",
              "data"  : woo_usn_options
            }, function ( response ) {
              $.unblockUI();
              var json = JSON.parse( response );
              if ( json.data ) {
                Snackbar.show({pos: 'bottom-right', text:'Settings saved'});
              }
            }
        );
      });


       $('button.woo-usn-delete').on('click dbclick submit', function(e){
            e.preventDefault();
             var elemy = $(e.currentTarget).parent().parent().find('.woo-usn-media-link-message');
             elemy.val('');

             $(e.currentTarget).parent().parent().find('span.woo-usn-media-preview').empty();

             var elem = $('span.woo-usn-media-upload-place');

              $(elem).empty();
              $('span.woo-usn-media-preview').empty();

        })

    // $("select").select2({
    //   "width" : "80%"
    // });


    function woo_usn_send_bulk_notif( data ) {
      return new Promise(function(resolve, reject) {
        $.post(
            woo_usn_ajax_object.woo_usn_ajax_url,
            {
              action: "woo_usn-send-sms-to-contacts",
              data: data,
              security: woo_usn_ajax_object.woo_usn_ajax_security,
            },
            function (response) {
              try {
                var jsonp = JSON.parse(response);

                if (jsonp.offset < jsonp.total) {
                  woo_usn_cl_data['success'] += jsonp.success;
                  woo_usn_cl_data['failed'] += jsonp.failed;
                  woo_usn_cl_data['offset'] = jsonp.offset;
                  woo_usn_cl_data['message'] = jsonp.message;
                  resolve( woo_usn_bs_callback(woo_usn_cl_data) );
                } else {
                  woo_usn_cl_data['success'] = jsonp.success;
                  woo_usn_cl_data['failed'] = jsonp.failed;
                  woo_usn_cl_data['offset'] = jsonp.offset;
                  woo_usn_cl_data['message'] = jsonp.message;
                  resolve(data);
                }
              } catch (e){
                Snackbar.show({pos: 'bottom-right', text:'The plugin cannot deliver bulk messaging at the moment, please look at the logs at WooCommerce > Status > Logs > Ultimate-sms-notifications of today date. Thank you!'});
                reject(response)
              }
            }
        );
      });
    }

    //    $('select.hs_wp_auto_message_input_text').on('change', function(  ){
    //     var elemt = $(this);
    //     var name = elemt.attr('name');
    //     var val = elemt.val();
    //
    //
    //     $('div[data-template-name="'+name+'"]').hide();
    //     $('div[data-template-name="'+name+'"][data-template-message-id="'+val+'"').show();
    // });
    //
    // $('select.hs_wp_auto_message_input_text').trigger('change');
    //
    // $('select.woo-usn-select-variables').each(function(){
    //     var dd = JSON.parse(woo_usn_options_db);
    //
    //     if ( undefined != $(this).data('headerFieldName') ) {
    //         var header_field_name = $(this).data('headerFieldName');
    //          header_field_name = header_field_name.replace(/\[/g, "['").replace(/\]/g, "']");
    //          var idx = $(this).data('varIndex');
    //          try{
    //              var chcs = eval(`dd${header_field_name}`);
    //              $('select[name="woo_usn_options' + $(this).data('headerFieldName') + '[' + idx + ']"]').val(chcs[idx]);
    //          } catch (e) {
    //              console.log(e)
    //          }
    //     }
    //
    //     if ( undefined != $(this).data('fieldName') ) {
    //           var field_name = $(this).data('fieldName');
    //          field_name = field_name.replace(/\[/g, "['").replace(/\]/g, "']");
    //          var idx = $(this).data('varIndex');
    //          try{
    //              var chcs = eval(`dd${field_name}`);
    //              $('select[name="woo_usn_options' + $(this).data('fieldName') + '[' + idx + ']"]').val(chcs[idx]);
    //          } catch (e) {
    //              console.log(e)
    //          }
    //     }
    // });


    function woo_usn_bs_callback(data) {
      return woo_usn_send_bulk_notif(data);
    }

    $('select#woo_usn_testing_messages').on('change', function(){
      $('div.woo_usn_testing_messages').hide();
      $('div.woo_usn_testing_messages[data-template-message-id="'+$(this).val()+'"]').show();
    });

     $('select#woo_usn_testing_messages').trigger('change');



     $('button.woo-usn-upload-msg-media').on('click dbclick submit', function(e){
        e.preventDefault();
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select or Upload File/Media to share by SMS/WhatsApp',
            button: {
                text: 'Use this file/media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {

        // Get media attachment details from the frame state
        var attachment = frame.state().get('selection').first().toJSON();

        //var media_attachment_id = attachment.id;
        var media_attachment_url = attachment.url;
        var media_type = attachment.type;

        if ( "image" == media_type ) {
          $('span.woo-usn-media-preview').empty().append('<img width="300px" src="' + media_attachment_url + '" class="woo-usn-img-preview" />');
        } else {
          $('span.woo-usn-media-preview').empty().append('The media has been added to the message, you can have a preview <a href="'+media_attachment_url+'" >here </a><br/><br/>' );
        }


        //var elem = $('span.woo-usn-media-upload-place');
        var elemy = $(e.currentTarget).parents().find('.woo-usn-media-link-message');

        $(elemy).val( media_attachment_url );


      });

      // Finally, open the modal on click
      frame.open();
    });


  });
})(jQuery);
