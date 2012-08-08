<? if ($megaerror): ?>
	<div class="megaerror"><?=$megaerror?></div>
<? else: ?>
	<h2><?=$queue->getName()?></h2>

	<h3>Job Queue</h3>
	<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs))?>
<? endif ?>