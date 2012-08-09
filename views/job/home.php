<div class="row">
	<div class="span6">
		<h2>Available Jobs</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $available)); ?>
	</div>
	<div class="span6">
		<h2>Working Jobs</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $taken)); ?>
	</div>
</div>
<div class="row">
	<div class="span6">
		<h2>Completed Jobs</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete)); ?>
	</div>
	<div class="span6">
		<h2>Failed Jobs</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure)); ?>
	</div>
</div>