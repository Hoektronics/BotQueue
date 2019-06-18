<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?php echo $job->getUrl() ?>/delete">
	 <input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
		  <h4 class="alert-heading">Warning!</h4>
			Are you sure you want to delete this job?  This is permanent and will remove all information about this job.<br/><br/>
			<button type="submit" class="btn btn-primary">Delete Job</button>
		</div>
	</form>
<?php endif ?>