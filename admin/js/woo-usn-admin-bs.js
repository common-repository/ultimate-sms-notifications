(function ($) {
    'use strict';

    $(document).on('click', '.woo-usn-status', function(e){
        e.preventDefault();
        var status =$(this).data('scheduleStatus');
        var scheduleId = $(this).data('scheduleId');
        $.blockUI({'message' : 'Loading ... '});
        $.post(
            ajaxurl,
            {
                action : 'woo_usn_get_schedule_status',
                data : {
                    'status' : status,
                    'schedule_id' : scheduleId
                }
            },
            function(resp){
                var decoded = JSON.parse(resp);
                $.unblockUI();
                var html = "<table class='wp-list-table widefat fixed striped table-view-list pages'><thead><tr><th>Message</th><th>Phone Number</th><th>Status</th><th>Sending Mode Type</th></tr></thead><tbody>";
                for( var i in decoded ) {
                    var value = decoded[i];
                    html +="<tr><td>" + value.message +"</td><td>"+value.phone_number+"</td><td>"+status+"</td><td>"+value.mode_type+"</td></tr>";
                }
                html += "</tbody></table>";
                $('div.woo_usn_result').html(html );
                $('#woo_usn_bs_status').modal();
            }
        );
    });

})(jQuery);
