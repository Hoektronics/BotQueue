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
						<th>Manage:</th>
						<td>
							<? if ($job->get('status') == 'available'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
							<? endif ?>
							<? if ($job->get('status') != 'taken'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
							<? endif ?>
							<a class="btn btn-mini" href="/job/create/file:<?=$job->get('file_id')?>"><i class="icon-repeat"></i> re-run</a>
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