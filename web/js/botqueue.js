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