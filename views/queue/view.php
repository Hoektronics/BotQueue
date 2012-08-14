<? if ($megaerror): ?>
	<div class="megaerror"><?=$megaerror?></div>
<? else: ?>
	<div class="row">
		<div class="span12">
			<h3>Active Jobs</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $active))?>
		</div>
	</div>
	<div class="row">
		<div class="span6">
			<h3>Completed Jobs</h3>
			<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete))?>
		</div>
		<div class="span6">
			<h3>Failed Jobs</h3>
			<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure))?>
		</div>
	</div>
<? endif ?>