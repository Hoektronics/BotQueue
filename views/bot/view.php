<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if ($bot->get('status') == 'error'): ?>
  	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "This bot is offline with the following error: " . $bot->get('error_text')))?>    
  <? endif ?>
	<div class="row">
	  <div class="span6">
		  <? if ($webcam->isHydrated()): ?>
		    <h3>Latest Image - <span class="muted"><?=Utility::getTimeAgo($webcam->get('add_date'))?></span></h3>
		    <img src="<?=$webcam->getRealUrl()?>">
		  <? else: ?>
        <img src="/img/kitten-640x480.jpg">
		  <? endif ?>
		</div>
		<div class="span6">
			<h3>Basic Info</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Status:</th>
						<td><?=$bot->getStatusHTML() ?></td>
					</tr>
					<tr>
						<th>Current Job:</th>
						<td>
							<? if ($job->isHydrated()): ?>
								<?=$job->getLink()?>
							<? else: ?>
								none
							<? endif?>
						</td>
					</tr>
					<tr>
						<th>Owner:</th>
						<td><?=$owner->getLink()?></td>
					</tr>
					<tr>
						<th>Queue:</th>
						<td><?=$queue->getLink()?></td>
					</tr>
					<tr>
						<th>Slice Engine:</th>
						<? if ($engine->isHydrated()): ?>
  						<td><?=$engine->getLink()?></td>
  					<? else: ?>
  						<td><span class="text-error">No slice engine selected!</span></td>
            <? endif ?>
					</tr>
					<tr>
						<th>Engine Config:</th>
						<? if ($config->isHydrated()): ?>
  						<td><?=$config->getLink()?></td>
						<? else: ?>
  						<td><span class="text-error">No slice config selected!</span></td>
            <? endif ?>
					</tr>					
					<? if ($bot->get('manufacturer')): ?>
						<tr>
							<th>Maker:</th>
							<td><?=$bot->get('manufacturer')?></td>
						</tr>
					<? endif ?>
					<? if ($bot->get('model')): ?>
						<tr>
							<th>Model:</th>
							<td><?=$bot->get('model')?></td>
						</tr>
					<? endif ?>
					<? if ($bot->get('electronics')): ?>
						<tr>
							<th>Electronics:</th>
							<td><?=$bot->get('electronics')?></td>
						</tr>
					<? endif ?>
					<? if ($bot->get('firmware')): ?>
						<tr>
							<th>Firmware:</th>
							<td><?=$bot->get('firmware')?></td>
						</tr>
					<? endif ?>
					<? if ($bot->get('extruder')): ?>
						<tr>
							<th>Extruder:</th>
							<td><?=$bot->get('extruder')?></td>
						</tr>
					<? endif ?>
					<!-- >
					<tr>
						<th>Total Wait Time</th>
						<td><?= Utility::getHours($stats['total_waittime'])?></td>
					</tr>
					-->
					<tr>
						<th>Total Run Time</th>
						<td><?= Utility::getHours($stats['total_runtime'])?></td>
					</tr>
					<!-- >
					<tr>
						<th>Total Overall Time</th>
						<td><?= Utility::getHours($stats['total_time'])?></td>
					</tr>
					<tr>
						<th>Average Wait Time</th>
						<td><?= Utility::getHours($stats['avg_waittime'])?></td>
					</tr>
					<tr>
						<th>Average Run Time</th>
						<td><?= Utility::getHours($stats['avg_runtime'])?></td>
					</tr>
					<tr>
						<th>Average Overall Time</th>
						<td><?= Utility::getHours($stats['avg_time'])?></td>
					</tr>
					<tr>
						<th>Available Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('available')?>"><?= (int)$stats['available'] ?></span> (<?= round($stats['available_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Taken Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$stats['taken'] ?></span> (<?= round($stats['taken_pct'], 2)?>%)</td>
					</tr>
					-->
					<tr>
						<th>Complete Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$stats['complete'] ?></span> (<?= round($stats['complete_pct'], 2)?>%)</td>
					</tr>
					<tr>
						<th>Failed Jobs</th>
						<td><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$stats['failure'] ?></span> (<?= round($stats['failure_pct'], 2)?>%)</td>
					</tr>
					<!-- >
					<tr>
						<th>Total Jobs</th>
						<td><span class="label label-inverse"><?= (int)$stats['total'] ?></span></td>
					</tr>
					-->
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="span12">
			<h3>
				Jobs
				:: 1-<?=min(10, $job_count)?> of <?=$job_count?> :: <a href="<?=$bot->getUrl()?>/jobs">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs)) ?>
		</div>
	</div>
	<? if (!empty($errors)): ?>
  	<div class="row">
  	  <div class="span12">
    	  <h3>Error Log</h3>
  	    <?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'bot'))?>
  	  </div>
  	</div>
	<? endif ?>
<? endif ?>