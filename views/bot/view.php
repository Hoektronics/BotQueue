<h2><?=$bot->getName()?></h2>

<h3>Status: <?=$bot->get('status') ?></h3>

<? if ($job->isHydrated()): ?>
	<h3>Current Job: <?=$job->getLink()?></h3>
<? endif?>

Queue: <?=$queue->getLink()?><br/>
Maker: <?=$bot->get('manufacturer')?><br/>
Model: <?=$bot->get('model')?><br/>
Electronics: <?=$bot->get('electronics')?><br/>
Firmware: <?=$bot->get('firmware')?><br/>
Extruder: <?=$bot->get('extruder')?><br/>

<h3>Jobs</h3>
<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs)) ?>