<div class="row">
	<div class="span6">
		<h2>Latest Jobs</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $jobs)); ?>
	</div>
	<div class="span6">
		<h2>Latest Activity</h2>
		<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
	</div>
</div>
<div class="row">
	<div class="span6">
		<h2>My Queues</h2>
		<?= Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues)); ?>
	</div>
	<div class="span6">
		<h2>My Bots</h2>
		<?= Controller::byName('bot')->renderView('draw_bots', array('bots' => $bots)); ?>
	</div>
</div>
