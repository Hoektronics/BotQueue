<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <script>
	  function setActiveForm() {
		  $('#editTab').children('li').each(function() {
			  var tab_id = $(this).attr('id');
			  if(tab_id == 'bot_<?=$active_form?>') {
				  $(this).attr("class", "active");
			  }
			  <? if(!$setup_mode): ?>
			  $(this).children('a').each(function() {
				  $(this).attr('href', '#'.concat(tab_id).concat('_content'));
				  $(this).attr('data-toggle', 'tab');
			  });
			  <? endif ?>
		  });
		  var content = $('#editTabContent');
	  	  content.children('div').each(function() {
			  var content_id = $(this).attr('id');
			  if(content_id == 'bot_<?=$active_form?>_content') {
				  $(this).attr('class', 'tab-pane fade active in');
			  } else {
				  $(this).attr('class', 'tab-pane fade');
			  }
		  });
		  content.attr('style', '');
	  }

	  window.onload = setActiveForm;
  </script>
  <div class="row">
    <div class="span3">
      <ul class="nav nav-list" id="editTab">
        <li id="bot_info">
			<a>Information / Details</a>
		</li>
        <li id="bot_slicing">
			<a>Slicing Setup</a>
		</li>
        <li id="bot_driver">
			<a>Driver Configuration</a>
		</li>
      </ul>
    </div>

    <div class="span9 tab-content" id="editTabContent" style="display:none">
      <div id="bot_info_content">
  	    <?= $info_form->render() ?>
  	  </div>
    
      <div id="bot_slicing_content">
  	    <?= $slicing_form->render() ?>
      </div>

      <div id="bot_driver_content">
        <? if ($bot->get('status') == 'idle' || $bot->get('status') == 'offline' || $bot->get('status') == 'error' || $bot->get('status') == 'waiting'): ?>
  	      <?= $driver_form->render() ?>
  	    <? else: ?>
  	      <div class="row-fluid">
        		<div class="alert alert-error">
        			<a class="close">&times;</a>
        			<strong>Error</strong> The bot must be in an idle, offline, or waiting state in order to edit the driver config.
          	</div>
          </div>
  	    <? endif ?>
      </div>
    </div>
  </div>
  <script>
    function update_driver_form()
    {
	  $(':submit').attr("disabled","disabled");
      var token_id = $('#oauth_token_dropdown').find(":selected").val();
      var driver = $('#driver_name_dropdown').find(":selected").val();

	  var edit_area = $('#driver_edit_area');
      edit_area.html("<span class='muted'>Loading...</span>");
      edit_area.load('/bot:<?=$bot->id?>/driverform:' + driver + '/token:' + token_id,
		  function(){
			  $(':submit').removeAttr("disabled");
		  });
    }
    
    $(update_driver_form);
  </script>
<? endif ?>