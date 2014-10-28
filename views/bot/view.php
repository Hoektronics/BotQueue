<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<? if ($bot->get('status') == 'error'): ?>
		<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "This bot is offline with the following error: " . $bot->get('error_text'))) ?>
	<? endif ?>
	<div class="row">
		<div class="span6">
			<? if ($webcam->isHydrated()): ?>
				<h3>Latest Image - <span class="muted"><?= Utility::getTimeAgo($webcam->get('add_date')) ?></span></h3>
				<img src="<?= $webcam->getDownloadURL() ?>">
			<? else: ?>
				<img src="/img/colorbars.gif">
			<? endif ?>
		</div>
		<div class="span6">
			<h3>Basic Info</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Status:</th>
					<td><?= BotStatus::getStatusHTML($bot) ?></td>
				</tr>
				<? if ($bot->get('remote_ip')): ?>
					<tr>
						<th>Remote IP:</th>
						<td><?= $bot->get('remote_ip') ?></td>
					</tr>
				<? endif ?>
				<? if ($bot->get('local_ip')): ?>
					<tr>
						<th>Local IP:</th>
						<td><?= $bot->get('local_ip') ?></td>
					</tr>
				<? endif ?>
				<tr>
					<th>Current Job:</th>
					<td>
						<? if ($job->isHydrated()): ?>
							<?= $job->getLink() ?>
						<? else: ?>
							none
						<? endif ?>
					</td>
				</tr>
				<tr>
					<th>Owner:</th>
					<td><?= $owner->getLink() ?></td>
				</tr>
				<? if ($app->isHydrated()): ?>
					<tr>
						<th>Assigned to:</th>
						<td><a href="<?= $app->getUrl() ?>"><?= $app->getName() ?></a></td>
					</tr>
				<? else: ?>
					<tr>
						<th>Assigned to:</th>
						<td><span class="text-error">No controlling app found.</span></td>
					</tr>
				<? endif ?>
				<tr>
					<th>Queue:</th>
					<td><?= $queue->getLink() ?></td>
				</tr>
				<tr>
					<th>Slice Engine:</th>
					<? if ($engine->isHydrated()): ?>
						<? if ($bot->getDriverConfig()->can_slice): ?>
							<td><?= $engine->getLink() ?></td>
						<? else: ?>
							<td><?= $engine->getLink() ?> (Slicing disabled)</td>
						<? endif ?>
					<? else: ?>
						<td><span class="text-error">No slice engine selected!</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Engine Config:</th>
					<? if ($config->isHydrated()): ?>
						<td><?= $config->getLink() ?></td>
					<? else: ?>
						<td><span class="text-error">No slice config selected!</span></td>
					<? endif ?>
				</tr>
				<? if ($bot->get('manufacturer')): ?>
					<tr>
						<th>Maker:</th>
						<td><?= $bot->get('manufacturer') ?></td>
					</tr>
				<? endif ?>
				<? if ($bot->get('model')): ?>
					<tr>
						<th>Model:</th>
						<td><?= $bot->get('model') ?></td>
					</tr>
				<? endif ?>
				<? if ($bot->get('electronics')): ?>
					<tr>
						<th>Electronics:</th>
						<td><?= $bot->get('electronics') ?></td>
					</tr>
				<? endif ?>
				<? if ($bot->get('firmware')): ?>
					<tr>
						<th>Firmware:</th>
						<td><?= $bot->get('firmware') ?></td>
					</tr>
				<? endif ?>
				<? if ($bot->get('extruder')): ?>
					<tr>
						<th>Extruder:</th>
						<td><?= $bot->get('extruder') ?></td>
					</tr>
				<? endif ?>
				<tr>
					<th>Total Run Time</th>
					<td><?= Utility::getHours($stats['total_runtime']) ?></td>
				</tr>
				<tr>
					<th>Complete Jobs</th>
					<td><span
							class="label <?= JobStatus::getStatusHTMLClass('complete') ?>"><?= (int)$stats['complete'] ?></span>
						(<?= round($stats['complete_pct'], 2) ?>%)
					</td>
				</tr>
				<tr>
					<th>Failed Jobs</th>
					<td><span
							class="label <?= JobStatus::getStatusHTMLClass('failure') ?>"><?= (int)$stats['failure'] ?></span>
						(<?= round($stats['failure_pct'], 2) ?>%)
					</td>
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
				:: 1-<?= min(10, $job_count) ?> of <?= $job_count ?> :: <a href="<?= $bot->getUrl() ?>/jobs">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs)) ?>
		</div>
	</div>
	<? if (!empty($errors)): ?>
		<div class="row">
			<div class="span12">
				<h3>Error Log</h3>
				<?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'bot')) ?>
			</div>
		</div>
	<? endif ?>
<? endif ?>