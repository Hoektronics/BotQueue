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
					<td><?= Utility::formatDate($job->get('start'))?> (<?=Utility::relativeTime($job->get('start'))?>)</td>
				</tr>
				<tr>
					<th>Finished:</th>
					<td><?= Utility::formatDate($job->get('end'))?> (<?=Utility::relativeTime($job->get('end'))?>)</td>
				</tr>
				<tr>
					<th>Elapsed:</th>
					<td><?=$job->getElapsedText()?></td>
				</tr>

			</tbody>
		</table>
	</div>
	<div class="span6">
		<table class="table table-striped table-bordered table-condensed">
			<tbody>
				<tr>
					<th>Actions:</th>
					<td>
						edit
						cancel
						delete
						re-run
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