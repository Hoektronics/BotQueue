<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="alert alert-block">
	  <h4 class="alert-heading">Warning!</h4>
		<p>
			The application <?=$app->getLink()?> is requesting access to your BotQueue account. If you approve this, it will be able to modify your queues, bots, jobs, and other account information.
		</p>
		<p>
			The website for this app is: <a href="<?=$app->get('app_url')?>"><?=$app->get('app_url')?></a>.  Please verify that this is where you downloaded the app from.
		</p>
	</div>
	<div class="row">
		<div class="span6">
			<div class="alert alert-block alert-success">
	  		<h4 class="alert-heading">Approve it:</h4>
				Please enter the following PIN into the application: <strong><?=$token->get('verifier')?></strong>
			</div>
		</div>
		<div class="span6">
			<div class="alert alert-block alert-error">
			  <h4 class="alert-heading">Deny it:</h4>
				<a href="/app/revoke/<?=$token->get('token')?>?submit=1">Click here</a>, or just ignore this page and no access will be granted.
			</div>
		</div>
	</div>
<? endif ?>

