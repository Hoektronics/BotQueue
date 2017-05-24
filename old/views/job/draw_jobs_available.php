<? if (!empty($jobs)): ?>
	<table class="table table-striped table-bordered table-condensed jobtable">
		<thead>
			<tr>
				<th></th>
				<th>#</th>
				<th>Name</th>
				<th>Status</th>
				<th>Elapsed</th>
				<th>ETA</th>
				<th>Progress</th>
				<th>Queue</th>
				<th>Manage</th>
			</tr>
		</thead>
		<tbody class="joblist">
			<? foreach ($jobs AS $row): ?>
				<? $j = $row['Job'] ?>
				<? $bot = $j->getBot() ?>
				<tr id="job_<?=$j->id?>">
					<td><i class="icon-resize-vertical"></i></td>
					<td><?=$j->id?></td>
					<td><?=$j->getLink()?></td>
					<td><?=JobStatus::getStatusHTML($j)?></td>
					<td class="muted"><?=$j->getElapsedText()?></td>
					<td class="muted"><?=$j->getEstimatedText()?></td>
					<td align="right" style="width: 300px">
						<div class="progress progress-striped <?=($j->get('status') == 'working') ? 'active' : ''?>">
						  <div class="bar" style="width: <?=round($j->get('progress'))?>%;"></div>
						</div>
					</td>
					<td><?=$j->getQueue()->getLink()?></td>
					<td>
						<? if ($j->get('status') == 'available'): ?>
  						<a class="btn btn-mini" href="<?=$j->getUrl()?>/bump"><i class="icon-arrow-up"></i> bump</a>
  						<a class="btn btn-mini" href="<?=$j->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
						<? endif ?>
						<? if ($j->get('status') != 'taken' && $j->get('status') != 'qa' && $j->get('status') != 'downloading' && $j->get('status') != 'slicing' ): ?>
							<a class="btn btn-mini" href="<?=$j->getUrl()?>/cancel"><i class="icon-eject"></i> cancel</a>
						<? endif ?>
						<? if ($j->get('status') == 'qa'): ?>
						  <a class="btn btn-mini" href="<?=$j->getUrl()?>/qa"><i class="icon-check"></i> verify</a>
						<? endif ?>
						<a class="btn btn-mini" href="/job/create/job:<?=$j->id?>"><i class="icon-repeat"></i> re-run</a>
					</td>
				</tr>
			<?endforeach?>
		</tbody>
	</table>
<? else: ?>
	<b>No pending jobs.</b>
<? endif ?>
