<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Status:</th>
						<td><?=$job->getStatusHTML() ?></td>
					</tr>
					<tr>
						<th>Progress:</th>
						<td><div class="progress progress-striped"><div class="bar" style="width: <?=round($job->get('progress'))?>%;"></div></div></td>
					</tr>
					<tr>
						<th>Created:</th>
						<td><?= Utility::formatDatetime($job->get('created'))?> (<?=Utility::relativeTime($job->get('created'))?>)</td>
					</tr>
					<tr>
						<th>Started:</th>
						<? if (strtotime($job->get('start')) > 0): ?>
							<td><?= Utility::formatDate($job->get('start'))?> (<?=Utility::relativeTime($job->get('start'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Finished:</th>
						<? if (strtotime($job->get('end')) > 0): ?>
							<td><?= Utility::formatDate($job->get('end'))?> (<?=Utility::relativeTime($job->get('end'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Elapsed:</th>
						<td><?=$job->getElapsedText()?></td>
					</tr>
					<tr>
						<th>Remaining:</th>
						<td><?=$job->getEstimatedText()?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Manage:</th>
						<td>
							<? if ($job->get('status') == 'available'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
							<? endif ?>
							<? if ($job->get('status') == 'qa'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/qa"><i class="icon-check"></i> verify</a>
							<? endif ?>
							<? if ($job->get('status') != 'taken' && $job->get('status') != 'qa'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
							<? endif ?>
							<a class="btn btn-mini" href="/job/create/job:<?=$job->id?>"><i class="icon-repeat"></i> re-run</a>
						</td>
					</tr>
					<tr>
						<th>File:</th>
						<td><?=$file->getLink()?></td>
					</tr>
					<tr>
						<th>Queue:</th>
						<td><?=$queue->getLink()?></td>
					</tr>
					<? if ($bot->isHydrated()): ?>
						<tr>
							<th>Bot:</th>
							<td><?=$bot->getLink()?>
						</tr>
					<? endif ?>
					<tr>
						<th>Creator:</th>
						<td><?=$creator->getLink()?></td>
					</tr>			
				</tbody>
			</table>
		</div>
	</div>
<? endif ?>