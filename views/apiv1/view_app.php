<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	Application Name: <?= $app->getName() ?><Br/>
	API Key: <?=$app->get('consumer_key') ?><br/>
	API Secret: <?=$app->get('consumer_secret') ?><br/>
	Active: <?= ($app->get('active') == 1) ? 'yes' : 'no'?><br/>
	<br/>
	<a href="<?=$app->getUrl()?>/edit">Edit App</a> or <a href="<?=$app->getUrl()?>/delete">Delete App</a>
<? endif ?>