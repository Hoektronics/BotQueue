<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?=$job->getUrl()?>/qa">
	 <input type="hidden" name="submit" value="1">
	 <div class="controls">
  	 <label for="accepted">
  	   <input type="checkbox" id="accepted" name="accepted" value="1" checked="true">
  	   Do you accept the output of the <?=$job->getLink()?> from the <?=$bot->getLink()?> bot?
  	 </label>
   </div>
	 <div id="reject_options">
	   <div class="controls">
    	 <label for="accepted">
      	 <input type="checkbox" id="bot_failed" name="bot_failed" value="1">
    	   Has the bot failed and be taken offline?
    	 </label>
	   </div>
	   <div class="controls">
    	 <label for="accepted">
      	 <input type="checkbox" id="bot_failed" name="cancel_job" value="1">
    	   Should this job be canceled and removed?
    	 </label>
	   </div>
	 </div>
   <div class="controls">
	  <input type="submit" value="Finish Job">
	 </div>
	</form>
<? endif ?>