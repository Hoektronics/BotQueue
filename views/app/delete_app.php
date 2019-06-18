<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?php echo $app->getUrl() ?>/delete">
	 <input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
		  <h4 class="alert-heading">Warning!</h4>
			Are you sure you want to delete this app?  This is permanent and will disable all access to users of your app.<br/><br/>
			<button type="submit" class="btn btn-primary">Delete App</button>
		</div>
	</form>
<?php endif ?>