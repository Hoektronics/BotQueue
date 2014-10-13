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
}

$(document).ready(prepare_job_sort);

function update_slice_config_dropdown(ele) {
    var engine_id = $("select#slice_engine_dropdown option:selected").val();
    var dropDown = $("select#slice_config_dropdown");
    var submit = $(":submit");
    dropDown.attr("disabled", "disabled");
    submit.attr("disabled", "disabled");
    dropDown.load("/ajax/bot/slice_config_select", {"id": engine_id}, function () {
        dropDown.removeAttr("disabled");
        submit.removeAttr("disabled");
    });
}