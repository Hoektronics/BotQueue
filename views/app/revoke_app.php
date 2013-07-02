<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?=$token->getUrl()?>/revoke">
		<input type="hidden" name="submit" value="1">
		<div class="alert alert-block">
			<h4 class="alert-heading">Warning!</h4>
				Are you sure you want to <?= ($token->get('type') == 2) ? 'revoke' : 'deny' ?> access to the <?=$app->getLink()?> app?  <? if ($token->get('type') == 2): ?>Any apps currently using these credentials to print will be broken.<? endif ?>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary"><?= ($token->get('type') == 2) ? 'Revoke' : 'Deny' ?> App Permissions</button>
		</div>
	</form>
<? endif ?>