<div class="row">
	<div class="span6">
		<h2>
			Available Jobs
			<? if ($available_count): ?>
				:: 1-<?=min(10, $available_count)?> of <?=$available_count?> :: <a href="/jobs/available">see all</a>
			<? endif ?>
		</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $available)); ?>
	</div>
	<div class="span6">
		<h2>
			Working Jobs
			<? if ($taken_count): ?>
				:: 1-<?=min(10, $taken_count)?> of <?=$taken_count?> :: <a href="/jobs/taken">see all</a>
			<? endif ?>
		</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $taken)); ?>
	</div>
</div>
<div class="row">
	<div class="span6">
		<h2>
			Completed Jobs
			<? if ($complete_count): ?>
				:: 1-<?=min(10, $complete_count)?> of <?=$complete_count?> :: <a href="/jobs/complete">see all</a>
			<? endif ?>
		</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete)); ?>
	</div>
	<div class="span6">
		<h2>
			Failed Jobs
			<? if ($failure_count): ?>
				:: 1-<?=min(10, $failure_count)?> of <?=$failure_count?> :: <a href="/jobs/failure">see all</a>
			<? endif ?>
		</h2>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure)); ?>
	</div>
</div>