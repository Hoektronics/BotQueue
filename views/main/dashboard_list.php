<?
/**
 * @namespace botqueue_main
 * @var array $bots
 * @var Bot $b
 * @var Queue $q
 * @var Job $j
 * @var SliceJob $sj
 */
?>
<table class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<th>Name</th>
			<th>Bot Status</th>
			<th>Last Seen</th>
			<th>Job</th>
			<th>Temps</th>
			<th>Status</th>
			<th>Elapsed</th>
			<th>ETA</th>
			<th colspan="2">Progress</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($bots AS $row): ?>
			<? $b = $row['Bot'] ?>
			<? $q = $row['Queue'] ?>
			<? $j = $row['Job'] ?>
			<? $sj = $j->getSliceJob() ?>
			<tr>
				<td><?=$b->getLink()?></td>
				<td><?=BotStatus::getStatusHTML($b)?></td>
				<td class="muted"><?=BotLastSeen::getHTML($b)?></td>
				<? if ($j->isHydrated()): ?>
					<td><?=$j->getLink()?></td>
          <? $temps = JSON::decode($b->get('temperature_data')) ?>
					<? if ($b->get('status') == 'working' && $temps != NULL): ?>
					  <td>
					    E: <?=$temps->extruder?>C<br/>
					    B: <?=$temps->bed?>C
					  </td>
					<? else: ?>
					  <td class="muted">n/a</td>
					<? endif ?>
				  <td><?=JobStatus::getStatusHTML($job)?></td>
				  <td class="muted"><?=$j->getElapsedText()?></td>
					<td class="muted"><?=$j->getEstimatedText()?></td>
				  <td style="width:250px">
				    <? if ($j->get('status') == 'qa'): ?>
              <a class="btn btn-success" href="<?=$j->getUrl()?>/qa/pass">PASS</a>
              <a class="btn btn-primary" href="<?=$j->getUrl()?>/qa">VIEW</a>
              <a class="btn btn-danger" href="<?=$j->getUrl()?>/qa/fail">FAIL</a>
            <? elseif ($j->get('status') == 'slicing' && $sj->get('status') == 'pending'): ?>
              <a class="btn btn-success" href="<?=$sj->getUrl()?>/pass">PASS</a>
              <a class="btn btn-primary" href="<?=$sj->getUrl()?>">VIEW</a>
              <a class="btn btn-danger" href="<?=$sj->getUrl()?>/fail">FAIL</a>
            <? else: ?>
  						<div class="progress progress-striped active" style="width: 250px">
  						  <div class="bar" style="width: <?=round($j->get('progress'))?>%;"></div>
  						</div>
  					<? endif ?>
					</td>
					<td class="muted">
            <?= round($j->get('progress'), 2) ?>%      					  
				  </td>
				<? elseif ($b->get('status') == 'error'): ?>
				  <td colspan="7" class="muted"><span class="text-error"><?=$b->get('error_text')?></span></td>
				<? else: ?>
					<td colspan="7" class="muted">&nbsp;</td>
				<? endif ?>
			</tr>
		<?endforeach?>
	</tbody>
</table>