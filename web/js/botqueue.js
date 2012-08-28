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

function toggle_bot_status(bot_id, status)
{
  console.log(status);
  
  if (status == 'idle')
  {
    $('#bot_pause_link_' + bot_id).show();
    $('#bot_play_link_' + bot_id).hide();
  }
  else if (status == 'offline')
  {
    $('#bot_pause_link_' + bot_id).hide();
    $('#bot_play_link_' + bot_id).show();
  }

  return false;
}