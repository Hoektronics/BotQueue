<? if ($megaerror): ?>
	<div class="megaerror"><?=$megaerror?></div>
<? else: ?>
	<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs))?>
<? endif ?>