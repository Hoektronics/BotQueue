<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	<form method="post" action="/api/v1/revoke?token=<?=$token->get('token')?>">
		Are you sure you want to revoke access to the <?=$app->getLink()?> app?
		<input type="submit" name="submit" value="Revoke App Permissions">
	</form>
<? endif ?>