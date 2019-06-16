<?php if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?php echo $bot->getUrl() ?>/retire">
	 <input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
		  <h4 class="alert-heading">Warning!</h4>
			Are you sure you want to retire this bot?  This is permanent, but it will keep all job information and will remain on your bots screen<br/><br/>
			<button type="submit" class="btn btn-primary">Retire Bot</button>
		</div>
	</form>
<?php endif ?>