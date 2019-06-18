<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 * @var OAuthToken $token
 * @var Form $approve_form
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<div class="alert alert-block">
	  <h4 class="alert-heading">Warning!</h4>
		<p>
			The application <?php echo $app->getLink() ?> is requesting access to your BotQueue account. If you approve this, it will be able to modify your queues, bots, jobs, and other account information.
		</p>
		<p>
			The website for this app is: <a href="<?php echo $app->get('app_url') ?>"><?php echo $app->get('app_url') ?></a>.  Please verify that this is where you downloaded the app from.  This request was made from <strong><?php echo $token->get('ip_address') ?></strong>.
		</p>
	</div>
	<div class="row">
		<div class="span6">
			<div class="alert alert-block alert-success approve-app">
	  		<h4 class="alert-heading">Approve it:</h4>
        <?php echo $approve_form->render() ?>
			</div>
		</div>
		<div class="span6">
			<div class="alert alert-block alert-error deny-app">
			  <h4 class="alert-heading">Deny it:</h4>
				You can safely ignore this page and no access will be granted. Or you can explicitly deny access below.
				<form class="form-horizontal" method="POST" action="<?php echo $token->getUrl() ?>/revoke">
        	<fieldset>
          	<div class="form-actions">
        			<button type="submit" class="btn btn-primary">Deny App</button>
        		</div>
        	</fieldset>
        </form>
			</div>
		</div>
	</div>
<?php endif ?>

