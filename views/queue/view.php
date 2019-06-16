<? if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<div class="row">
		<div class="span8">
			<h3>
				Jobs Pending QA
				:: 1-<?php echo min(10, $qa_count) ?> of <?php echo $qa_count ?> :: <a href="<?php echo $queue->getUrl() ?>/jobs/qa">see
					all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs', array('jobs' => $qa)) ?>
			<br/><br/>

			<h3>
				Working Jobs
				:: 1-<?php echo min(10, $taken_count) ?> of <?php echo $taken_count ?> :: <a
					href="<?php echo $queue->getUrl() ?>/jobs/taken">see all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs', array('jobs' => $taken)) ?>
			<br/><br/>

			<h3>
				Available Jobs
				:: 1-<?php echo min(10, $available_count) ?> of <?php echo $available_count ?> :: <a
					href="<?php echo $queue->getUrl() ?>/jobs/available">see all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs', array('jobs' => $available)) ?>
			<br/><br/>

			<h3>
				Completed Jobs
				:: 1-<?php echo min(10, $complete_count) ?> of <?php echo $complete_count ?> :: <a
					href="<?php echo $queue->getUrl() ?>/jobs/complete">see all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete)) ?>
			<br/><br/>

			<h3>
				Failed Jobs
				:: 1-<?php echo min(10, $failure_count) ?> of <?php echo $failure_count ?> :: <a href="/jobs/failure">see all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure)) ?>

		</div>
		<div class="span4">
			<p>
				<a class="btn btn-primary" href="<?php echo $queue->getUrl() ?>/edit">Edit Queue</a>
				<a class="btn btn-warning" href="<?php echo $queue->getUrl() ?>/empty">Empty Queue</a>
				<a class="btn btn-danger" href="<?php echo $queue->getUrl() ?>/delete">Delete Queue</a>
			</p>

			<h3>Delay: <? if($queue->get('delay')==0) {echo "none";} else {echo $queue->get('delay')." seconds";} ?></h3>

			<h3>Bots</h3>
			<?php echo Controller::byName('bot')->renderView('draw_bots_small', array('bots' => $bots)) ?>

			<h3>Statistics</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Total Wait Time</th>
					<td><?php echo Utility::getHours($stats['total_waittime']) ?></td>
				</tr>
				<tr>
					<th>Total Run Time</th>
					<td><?php echo Utility::getHours($stats['total_runtime']) ?></td>
				</tr>
				<tr>
					<th>Total Overall Time</th>
					<td><?php echo Utility::getHours($stats['total_time']) ?></td>
				</tr>
				<tr>
					<th>Average Wait Time</th>
					<td><?php echo Utility::getHours($stats['avg_waittime']) ?></td>
				</tr>
				<tr>
					<th>Average Run Time</th>
					<td><?php echo Utility::getHours($stats['avg_runtime']) ?></td>
				</tr>
				<tr>
					<th>Average Overall Time</th>
					<td><?php echo Utility::getHours($stats['avg_time']) ?></td>
				</tr>
				<tr>
					<th>Available Jobs</th>
					<td><span
							class="label <?php echo JobStatus::getStatusHTMLClass('available') ?>"><?php echo (int)$stats['available'] ?></span>
						(<?php echo round($stats['available_pct'], 2) ?>%)
					</td>
				</tr>
				<tr>
					<th>Taken Jobs</th>
					<td><span
							class="label <?php echo JobStatus::getStatusHTMLClass('taken') ?>"><?php echo (int)$stats['taken'] ?></span>
						(<?php echo round($stats['taken_pct'], 2) ?>%)
					</td>
				</tr>
				<tr>
					<th>Complete Jobs</th>
					<td><span
							class="label <?php echo JobStatus::getStatusHTMLClass('complete') ?>"><?php echo (int)$stats['complete'] ?></span>
						(<?php echo round($stats['complete_pct'], 2) ?>%)
					</td>
				</tr>
				<tr>
					<th>Failed Jobs</th>
					<td><span
							class="label <?php echo JobStatus::getStatusHTMLClass('failure') ?>"><?php echo (int)$stats['failure'] ?></span>
						(<?php echo round($stats['failure_pct'], 2) ?>%)
					</td>
				</tr>
				<tr>
					<th>Total Jobs</th>
					<td><span class="label label-inverse"><?php echo (int)$stats['total'] ?></span></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<? if (!empty($errors)): ?>
		<div class="row">
			<div class="span12">
				<h3>Error Log</h3>
				<?php echo Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'queue')) ?>
			</div>
		</div>
	<? endif ?>
<? endif ?>