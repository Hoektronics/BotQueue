<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="/api/v1/revoke?token=<?=$token->get('token')?>">
		<input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
			<h4 class="alert-heading">Warning!</h4>
				Are you sure you want to revoke access to the <?=$app->getLink()?> app?  Any apps currently using these credentials to print will be broken.
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Revoke App Permissions</button>
		</div>
	</form>
<? endif ?>

