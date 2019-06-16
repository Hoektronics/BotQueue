<?php if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php elseif ($status): ?>
	<div class="BaseStatus"><?php echo $status ?></div>
<?php else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="/user:<?php echo $user->id ?>/delete">
		<input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
			<h4 class="alert-heading">Warning!</h4>
			This is permanent.  We will delete all data including files and activities.  Are you sure you want to do this?<br/><br/>
			<button type="submit" class="btn btn-primary">Yes, delete it!</button>
		</div>
	</form>
<?php endif ?>
