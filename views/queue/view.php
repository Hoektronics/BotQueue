<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span8">
			<h3>
			  Jobs Pending QA
			  :: 1-<?=min(10, $qa_count)?> of <?=$qa_count?> :: <a href="<?=$queue->getUrl()?>/jobs/qa">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $qa))?>
			<br/><br/>

			<h3>
				Working Jobs
				:: 1-<?=min(10, $taken_count)?> of <?=$taken_count?> :: <a href="<?=$queue->getUrl()?>/jobs/taken">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $taken))?>
			<br/><br/>
			
			<h3>
				Available Jobs
				:: 1-<?=min(10, $available_count)?> of <?=$available_count?> :: <a href="<?=$queue->getUrl()?>/jobs/available">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $available))?>
			<br/><br/>

			<h3>
				Completed Jobs
				:: 1-<?=min(10, $complete_count)?> of <?=$complete_count?> :: <a href="<?=$queue->getUrl()?>/jobs/complete">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $complete))?>
			<br/><br/>
			
			<h3>
				Failed Jobs
				:: 1-<?=min(10, $failure_count)?> of <?=$failure_count?> :: <a href="/jobs/failure">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $failure))?>

		</div>
		<div class="span4">
		  <h3>Bots</h3>
		  <?= Controller::byName('bot')->renderView('draw_bots_small', array('bots' => $bots))?>

			<h3>Statistics</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Total Wait Time</th>
						<td><?= Utility::getElapsed($stats['total_waittime'])?></td>
					</tr>
					<tr>
						<th>Total Run Time</th>
						<td><?= Utility::getElapsed($stats['total_runtime'])?></td>
					</tr>
					<tr>
						<th>Total Overall Time</th>
						<td><?= Utility::getElapsed($stats['total_time'])?></td>
					</tr>
					<tr>
						<th>Average Wait Time</th>
						<td><?= Utility::getElapsed($stats['avg_waittime'])?></td>
					</tr>
					<tr>
						<th>Average Run Time</th>
						<td><?= Utility::getElapsed($stats['avg_runtime'])?></td>
					</tr>
					<tr>
						<th>Average Overall Time</th>
						<td><?= Utility::getElapsed($stats['avg_time'])?></td>
					</tr>
					<tr>
						<th>Available Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('available')?>"><?= (int)$stats['available'] ?></span> (<?= round($stats['available_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Taken Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$stats['taken'] ?></span> (<?= round($stats['taken_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Complete Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$stats['complete'] ?></span> (<?= round($stats['complete_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Failed Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$stats['failure'] ?></span> (<?= round($stats['failure_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Total Jobs</th>
						<td><span class="label label-inverse"><?= (int)$stats['total'] ?></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<? if (!empty($errors)): ?>
  	<div class="row">
  	  <div class="span12">
    	  <h3>Error Log</h3>
  	    <?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'queue'))?>
  	  </div>
  	</div>
  <? endif ?>
<? endif ?>