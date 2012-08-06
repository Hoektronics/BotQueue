<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	<form method="post" action="<?=$app->getUrl()?>/edit">
		Application Name: <input type="text" name="name" value="<?=$app->get('name')?>">
		<input type="submit" name="submit" value="Edit App">
	</form>
<? endif ?>

