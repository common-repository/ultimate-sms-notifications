(function ($) {
  "use strict";

$(document).ready(function (){

     var dd = JSON.parse(woo_usn_options_db);

    $('select.hs_wp_auto_message_input_text').on('change', function(  ){
        var elemt = $(this);
        var name = elemt.attr('name');
        var val = elemt.val();


        $('div[data-template-name="'+name+'"]').hide();
        $('div[data-template-name="'+name+'"][data-template-message-id="'+val+'"').show();
    });

    $('select.woo-usn-select-variables').each(function(){

        if ( undefined != $(this).data('headerFieldName') ) {
            var header_field_name = $(this).data('headerFieldName');
             header_field_name = header_field_name.replace(/\[/g, "['").replace(/\]/g, "']");
             var idx = $(this).data('varIndex');
             try{
                 var chcs = eval(`dd${header_field_name}`);
                 $('select[name="woo_usn_options' + $(this).data('headerFieldName') + '[' + idx + ']"]').val(chcs[idx]);
             } catch (e) {
                 console.log(e)
             }
        }

        if ( undefined != $(this).data('fieldName') ) {
              var field_name = $(this).data('fieldName');
             field_name = field_name.replace(/\[/g, "['").replace(/\]/g, "']");
             var idx = $(this).data('varIndex');
             try{
                 var chcs = eval(`dd${field_name}`);
                 $('select[name="woo_usn_options' + $(this).data('fieldName') + '[' + idx + ']"]').val(chcs[idx]);
             } catch (e) {
                 console.log(e)
             }
        }

        for( var msg in dd.cloudapi.messages ) {
            var msg_name = ""
            switch ( msg ) {
                case 'admin':
                    msg_name = "woo_usn_options[cloudapi][messages][admin]";
                    break;

                case 'cancelled_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][cancelled_orders]";
                    break;

                case 'completed_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][completed_orders]";
                    break;

                case 'failed_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][failed_orders]";
                    break;

                 case 'on_hold_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][on_hold_orders]";
                    break;

                    case 'pending_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][pending_orders]";
                    break;

                 case 'processing_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][processing_orders]";
                    break;


                  case 'refunded_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][refunded_orders]";
                    break;

              case 'signup_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][signup_orders]";
                    break;

             case 'successful_orders':
                    msg_name = "woo_usn_options[cloudapi][messages][successful_orders]";
                    break;


                case 'vendors':
                    for ( var vdrs in dd.cloudapi.messages[msg] ) {
                        msg_name = "woo_usn_options[cloudapi][messages][vendors]["+vdrs+"]";
                        $('select[name="'+msg_name+'"]').val(dd.cloudapi.messages[msg][vdrs] );
                    }
                    return;
            }

        $('select[name="'+msg_name+'"]').val(dd.cloudapi.messages[msg] );

            $('select.hs_wp_auto_message_input_text').trigger('change');


        }

    });

    // $("select").select2({
    //     "width" : "80%"
    // });

});
})(jQuery);
