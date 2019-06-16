<?php
/**
 * @package botqueue_jobs
 * @var int $available_count
 * @var array $available
 * @var int $taken_count
 * @var array $taken
 * @var int $complete_count
 * @var array $complete
 * @var int $failure_count
 * @var array $failure
 */
?>
<div class="row">
	<div class="span6">
		<h2>
			Available Jobs
			<?php if ($available_count): ?>
				:: 1-<?php echo min(10, $available_count) ?> of <?php echo $available_count ?> :: <a href="/jobs/available">see all</a>
			<?php endif ?>
		</h2>
		<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $available)); ?>
	</div>
	<div class="span6">
		<h2>
			Working Jobs
			<?php if ($taken_count): ?>
				:: 1-<?php echo min(10, $taken_count) ?> of <?php echo $taken_count ?> :: <a href="/jobs/taken">see all</a>
			<?php endif ?>
		</h2>
		<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $taken)); ?>
	</div>
</div>
<div class="row">
	<div class="span6">
		<h2>
			Completed Jobs
			<?php if ($complete_count): ?>
				:: 1-<?php echo min(10, $complete_count) ?> of <?php echo $complete_count ?> :: <a href="/jobs/complete">see all</a>
			<?php endif ?>
		</h2>
		<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete)); ?>
	</div>
	<div class="span6">
		<h2>
			Failed Jobs
			<?php if ($failure_count): ?>
				:: 1-<?php echo min(10, $failure_count) ?> of <?php echo $failure_count ?> :: <a href="/jobs/failure">see all</a>
			<?php endif ?>
		</h2>
		<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure)); ?>
	</div>
</div>