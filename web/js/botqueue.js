prepare_jobqueue_drag = function()
{
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
}

$(document).ready(prepare_jobqueue_drag);

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

function update_slice_config_dropdown(ele)
{
  engine_id = $("select#slice_engine_dropdown option:selected").val();
  $("select#slice_config_dropdown").attr("disabled", "disabled");
  $("select#slice_config_dropdown").load("/ajax/bot/slice_config_select", {"id" : engine_id}, function(){$("select#slice_config_dropdown").removeAttr("disabled");});
}