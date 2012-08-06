<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	<form method="post" action="<?=$app->getUrl()?>/delete">
		Are you sure you want to delete this app?  This is permanent and will disable all access to users of your app.
		<input type="submit" name="submit" value="Delete App">
	</form>
<? endif ?>