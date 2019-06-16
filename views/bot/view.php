<?php if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<?php if ($bot->get('status') == 'error'): ?>
		<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "This bot is offline with the following error: " . $bot->get('error_text'))) ?>
	<?php endif ?>
	<div class="row">
		<div class="span6">
			<?php if ($webcam->isHydrated()): ?>
				<h3>Latest Image - <span class="muted"><?php echo Utility::getTimeAgo($webcam->get('add_date')) ?></span></h3>
				<img src="<?php echo $webcam->getDownloadURL() ?>">
			<?php else: ?>
				<img src="/img/colorbars.gif">
			<?php endif ?>
		</div>
		<div class="span6">
			<h3>Basic Info</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Status:</th>
					<td><?php echo BotStatus::getStatusHTML($bot) ?></td>
				</tr>
				<?php if ($bot->get('remote_ip')): ?>
					<tr>
						<th>Remote IP:</th>
						<td><?php echo $bot->get('remote_ip') ?></td>
					</tr>
				<?php endif ?>
				<?php if ($bot->get('local_ip')): ?>
					<tr>
						<th>Local IP:</th>
						<td><?php echo $bot->get('local_ip') ?></td>
					</tr>
				<?php endif ?>
				<tr>
					<th>Current Job:</th>
					<td>
						<?php if ($job->isHydrated()): ?>
							<?php echo $job->getLink() ?>
						<?php else: ?>
							none
						<?php endif ?>
					</td>
				</tr>
				<tr>
					<th>Owner:</th>
					<td><?php echo $owner->getLink() ?></td>
				</tr>
				<?php if ($app->isHydrated()): ?>
					<tr>
						<th>Assigned to:</th>
						<td><a href="<?php echo $app->getUrl() ?>"><?php echo $app->getName() ?></a></td>
					</tr>
				<?php else: ?>
					<tr>
						<th>Assigned to:</th>
						<td><span class="text-error">No controlling app found.</span></td>
					</tr>
				<?php endif ?>
				<tr>
					<th>Queue(s):<br></th>
					<td>
						<?php foreach ($queue as $row): ?>
							<?php echo $row['Queue']->getLink() ?><br>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th>Slice Engine:</th>
					<?php if ($engine->isHydrated()): ?>
						<?php if ($bot->getDriverConfig()->can_slice): ?>
							<td><?php echo $engine->getLink() ?></td>
						<?php else: ?>
							<td><?php echo $engine->getLink() ?> (Slicing disabled)</td>
						<?php endif ?>
					<?php else: ?>
						<td><span class="text-error">No slice engine selected!</span></td>
					<?php endif ?>
				</tr>
				<tr>
					<th>Engine Config:</th>
					<?php if ($config->isHydrated()): ?>
						<td><?php echo $config->getLink() ?></td>
					<?php else: ?>
						<td><span class="text-error">No slice config selected!</span></td>
					<?php endif ?>
				</tr>
				<?php if ($bot->get('manufacturer')): ?>
					<tr>
						<th>Maker:</th>
						<td><?php echo $bot->get('manufacturer') ?></td>
					</tr>
				<?php endif ?>
				<?php if ($bot->get('model')): ?>
					<tr>
						<th>Model:</th>
						<td><?php echo $bot->get('model') ?></td>
					</tr>
				<?php endif ?>
				<?php if ($bot->get('electronics')): ?>
					<tr>
						<th>Electronics:</th>
						<td><?php echo $bot->get('electronics') ?></td>
					</tr>
				<?php endif ?>
				<?php if ($bot->get('firmware')): ?>
					<tr>
						<th>Firmware:</th>
						<td><?php echo $bot->get('firmware') ?></td>
					</tr>
				<?php endif ?>
				<?php if ($bot->get('extruder')): ?>
					<tr>
						<th>Extruder:</th>
						<td><?php echo $bot->get('extruder') ?></td>
					</tr>
				<?php endif ?>
				<tr>
					<th>Total Run Time</th>
					<td><?php echo Utility::getHours($stats['total_runtime']) ?></td>
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
				<!-- >
					<tr>
						<th>Total Jobs</th>
						<td><span class="label label-inverse"><?php echo (int)$stats['total'] ?></span></td>
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
				:: 1-<?php echo min(10, $job_count) ?> of <?php echo $job_count ?> :: <a href="<?php echo $bot->getUrl() ?>/jobs">see all</a>
			</h3>
			<?php echo Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs)) ?>
		</div>
	</div>
	<?php if (!empty($errors)): ?>
		<div class="row">
			<div class="span12">
				<h3>Error Log</h3>
				<?php echo Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'bot')) ?>
			</div>
		</div>
	<?php endif ?>
<?php endif ?>