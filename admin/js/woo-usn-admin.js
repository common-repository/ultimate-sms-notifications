var $ = jQuery;

/**
 * @return {string}
 */
var HSGenerateRandom = function (length) {
  "use strict";
  var result = "";
  var characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
};

function woousn_render_array(array, display) {
  if (Array.isArray(array)) {
    array.forEach(function (data) {
      if (display == "show") {
        data.show();
      } else if (display == "hide") {
        data.hide();
      }
    });
  } else {
    if (display == "show") {
      array.show();
    } else if (display == "hide") {
      array.hide();
    }
  }
}

var hs_add_more_message = function (html_selector, id) {
  let ids = 0;
  if (id) {
    ids += id;
  }
  let button = "<br/><div data-id='" + ids + "'> <button type='submit' class='button button-primary add-more-message'>"
      + woo_usn_ajax_object.add_message_txt + "</button> </div>";
  $(button).insertAfter(html_selector);
};

function woousn_hide_fields(sibling, to_hide) {
  $(sibling).change(function () {
    if (this.checked) {
      woousn_render_array(to_hide, "show");
    } else {
      woousn_render_array(to_hide, "hide");
    }
  });
}

/**
 * This function hide some elements based on the checkbox checked.
 * @param checkbox_elem jQuery element to tag.
 * @param elem_to_hide  jQuery element to tag.
 */
var hs_toggle_display = function (checkbox_elem, elem_to_hide) {
  var woo_usn_elem = $(checkbox_elem).is(":checked");
  if (woo_usn_elem) {
    $(elem_to_hide).show();
  } else {
    $(elem_to_hide).hide();
  }
};



$(document).ready(function (){
    var dismiss_banner = $('a#usn-never-show-again');

    $(document).on('click dbclick', 'a#usn-already-give-review', function (e){
        e.preventDefault();
       $.post(woo_usn_ajax_object.woo_usn_ajax_url, {
           'action' : 'woo_usn-review-answers',
           'type' : 'already_give',
           'security' : woo_usn_ajax_object.woo_usn_ajax_security
       }, function(){
          $('div#woo_usn_banner').hide();
       });

    });


    $(document).on('click dbclick', 'a#usn-never-show-again', function (e){
        e.preventDefault();
        $.post(woo_usn_ajax_object.woo_usn_ajax_url, {
            'action' : 'woo_usn-review-answers',
            'type' : 'dismiss',
            'security' : woo_usn_ajax_object.woo_usn_ajax_security
        }, function(){
            $('div#woo_usn_banner').hide();
        });

    });

    $('form.woousn-custom-tables table');

    jQuery('button.woo_usn_s_bs_now').on('click dbclick', function(e){
      e.preventDefault();
      var pn_associated = $(this).data('bsPnAssociated');
      var bs_associated = $(this).data('bsId');
      $.post(woo_usn_ajax_object.woo_usn_ajax_url, {
        'action' : 'send-bs',
        'phone_number' : pn_associated,
        'bulk_sms' : bs_associated
      }, function(response){
        if ( response == 1 ){
          window.location.reload();
        }
      })
    });

    // $('div.woo-usn-links').parent().attr('target','_blank');
    $('.usn-message-contents').hide();
    var cm;

    $('select[name="woo_usn_options[messags-templates]"]').on('change', function(){
        $('.usn-message-contents').hide();
        var datasid = $(this).val();
        $('.usn-message-contents[data-sid="'+datasid+'"]').show();
        $('.usn-message-contents[data-sid="'+datasid+'"]').trigger('click', true);
        if ( undefined != cm ) {
            cm.codemirror.setOption("mode", "text/x-csrc");
            cm.codemirror.getWrapperElement().parentNode.removeChild(cm.codemirror.getWrapperElement());
            cm=null;
        }
        cm = wp.codeEditor.initialize($('.usn-message-contents[data-sid="'+datasid+'"]').children('.usn-code-mirror'), cm_settings);



        $('form[data-sid="'+datasid+'"]').on('change click keydown', function(e){
            var elms = $(this).serializeJSON();
            $('p.woo-usn-message-to-send').html(JSON.stringify(elms))
        })

        $('form[data-sid="'+datasid+'"]').trigger('change click keydown')

        var elms = $('form[data-sid="'+datasid+'"]').serializeJSON();

        $('p.woo-usn-message-to-send').html(JSON.stringify(elms))
    });

    $('select[name="woo_usn_options[messags-templates]"]').trigger('change');





});
