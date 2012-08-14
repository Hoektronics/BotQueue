$(document).ready(function() {
		$(".joblist").sortable({
    	handle: 'td:first',
			update: function(event, ui) {
        jobdata = {'jobs' : $(this).sortable('toArray').toString() };
        //console.log(jobdata);
        
        $.ajax({
          type: 'POST',
          url: '/ajax/queue/update_sort',
          data: jobdata,
          success: function(data, status, xhr){
            //console.log(data);
          }
        });
			}
		});
		$(".joblist").disableSelection();
		$(".jobtable").disableSelection();
});
