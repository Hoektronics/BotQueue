<? if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?php echo $job->getUrl() ?>/cancel">
	 <input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
		  <h4 class="alert-heading">Warning!</h4>
			Are you sure you want to cancel this job?  You will still have a record of the job, but it won't be run.<br/><br/>
			<button type="submit" class="btn btn-primary">Cancel Job</button>
		</div>
	</form>
<? endif ?>