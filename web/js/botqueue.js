prepare_job_sort = function () {
    var jobList = $(".joblist");
    jobList.sortable({
        handle: 'td:first',
        update: function (event, ui) {
            var table = this;
            $.ajax({
                type: 'POST',
                url: '/ajax/queue/update_sort',
                data: {'jobs': $(table).sortable('toArray').toString()},
                success: function (data, status, xhr) {
                }
            });
        }
    });
    jobList.disableSelection();
    $(".jobtable").disableSelection();
};

$(document).ready(prepare_job_sort);

update_notification_count = function () {
    $.ajax({
        url: '/notification/count',
        success: function (count) {
            var icon = $("#notification-icon");
            icon.html(count);
            if (count == 0) {
                icon.removeClass('active');
            } else {
                icon.addClass('active');
            }
        }
    });
};

setInterval(update_notification_count, 60000);

ajax_click = function (event) {
    event.preventDefault();
    var href = $(this).attr("href");
    $.ajax({
        url: href,
        success: function(result) {
            App.fetch();
        }
    });
};

$(".btn-ajax-click").click(ajax_click);