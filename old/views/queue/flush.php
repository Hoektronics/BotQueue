<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?=$queue->getUrl()?>/empty">
	 <input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
		  <h4 class="alert-heading">Warning!</h4>
			Are you sure you want to empty this queue?  This will permanently delete any pending jobs.<br/><br/>
			<button type="submit" class="btn btn-primary">Empty Queue</button>
		</div>
	</form>
<? endif ?>