(function ($) {
    "use strict";
    $(document).ready(function () {

        $('select[data-toggle]').on('click', function(){
            var toggled = $(this).data('toggle');
            for( var value in toggled.split(',') ) {
                var elm = toggled.split(',')[value];
                elm = elm.replace(/\s+/g, '');
                if ( $(this).val() == "yes" ) {
                    $('.'+elm).show()
                } else {
                    $('.'+elm).hide()
                }
            }
        });
        $('select[data-toggle]').trigger('click');

        var selector = [
            'select.woo_usn_allowed_countries_for_phone_selector',
            'select.woo-usn-select-vendors',
            'select.default_country_selector',
            'select.woo_usn_select_default_gw',
            'select.woousn_allcountries',
            'select.wc_allowed_countries'
        ]

        for( var elm in selector ) {
            $(selector[elm]).select2({'width': '100%'});
        }

        // we check if the abstract mode type is defined.
        if ( "undefined" != typeof(woo_usn_mode)) {
            $('select[name="woo_usn_options['+woo_usn_mode+']"]').on('change', function(){
                var selected_gw = $('select[name="woo_usn_options['+woo_usn_mode+']"]').val();
                $('tr.woousngw-selected').hide();
                $('tr.'+selected_gw).show();
            });
            $('select[name="woo_usn_options['+woo_usn_mode+']"]').trigger('change');

        }


        // add media to messages.
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
                       $(e.currentTarget).parent().parent().find('span.woo-usn-media-preview').empty().append('<img width="300px" src="' + media_attachment_url + '" class="woo-usn-img-preview" />');
                    } else {
                       $(e.currentTarget).parent().parent().find('span.woo-usn-media-preview').empty().append('The media has been added to the message, you can have a preview <a href="'+media_attachment_url+'" >here </a><br/><br/>' );
                    }

                    var elemy = $(e.currentTarget).parent().parent().find('.woo-usn-media-link-message');
                    $(elemy).val( media_attachment_url );

                   frame.close();

              });

              // Finally, open the modal on click
              frame.open();
            });

        // delete media from messages.
        $('button.woo-usn-delete').on('click dbclick submit', function(e){
            e.preventDefault();
             var elemy = $(this).parent().parent().find('.woo-usn-media-link-message');
             elemy.val('');
             console.log(elemy)
             $(e.currentTarget).parent().parent().find('span.woo-usn-media-preview').empty();

        })


    });


})(jQuery);
