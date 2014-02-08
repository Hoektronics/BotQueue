<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <script>
  
  </script>
  <div class="row">
    <div class="span3">
      <ul class="nav nav-list" id="editTab">
        <li<?=($active_form == 'info' ? " class=\"active\"" : '')?>>
			<a<?=($active_form === null ? " href=\"#bot_details\" data-toggle=\"tab\"" : '')?>>Information / Details</a>
		</li>
        <li<?=($active_form == 'slicing' ? " class=\"active\"" : '')?>>
			<a<?=($active_form === null ? " href=\"#bot_details\" data-toggle=\"tab\"" : '')?>>Slicing Setup</a>
		</li>
        <li<?=($active_form == 'driver' ? " class=\"active\"" : '')?>>
			<a<?=($active_form === null ? " href=\"#bot_details\" data-toggle=\"tab\"" : '')?>>Driver Configuration</a>
		</li>
      </ul>
    </div>

    <div class="span9 tab-content" id="editTabContent">
      <div id="bot_details" class="tab-pane fade<?=($active_form == 'info' ? ' active in' : '')?>">
  	    <?= $info_form->render() ?>
  	  </div>
    
      <div id="bot_slicing" class="tab-pane fade<?=($active_form == 'slicing' ? ' active in' : '')?>">
  	    <?= $slicing_form->render() ?>
      </div>

      <div id="bot_configuration" class="tab-pane fade<?=($active_form == 'driver' ? ' active in' : '')?>">
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
      $('#driver_edit_area').html("<span class='muted'>Loading...</span>");
      $('#driver_edit_area').load('/bot:<?=$bot->id?>/driverform:' + driver + '/token:' + token_id, function(){$(':submit').removeAttr("disabled");});
    }
    
    $(update_driver_form);
  </script>
<? endif ?>