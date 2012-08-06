<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	The application <?=$app->getLink()?> is requesting access to your BotQueue account.<br/>
	<br/>
	If you would like to approve this access, please enter the following PIN into the application: <b><?=$token->get('verifier')?></b>.
	<br/><br/>
	If you would like to deny this access, please <a href="/api/v1/revoke?token=<?=$token->get('token')?>">click here</a>.
<? endif ?>

