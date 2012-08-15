<? if (!empty($queues)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th>Name</th>
				<th colspan="2">Available</th>
				<th colspan="2">Working</th>
				<th colspan="2">Completed</th>
				<th colspan="2">Failed</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($queues AS $row): ?>
				<? $q = $row['Queue'] ?>
				<? $stats = $q->getStats() ?>
				<tr>
					<td><?=$q->getLink()?></td>
					<td><span class="label <?=Job::getStatusHTMLClass('available')?>"><?= (int)$stats['available'] ?></span></td>
					<td><?= round($stats['available_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$stats['taken'] ?></span></td>
					<td><?= round($stats['taken_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$stats['complete'] ?></span></td>
					<td><?= round($stats['complete_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$stats['failure'] ?></span></td>
					<td><?= round($stats['failure_pct'], 2)?>%</td>
					<td><span class="label label-inverse"><?= (int)$stats['total'] ?></span></td>
				</tr>
			<?endforeach?>
		</tbody>
	</table>
<? else: ?>
	<b>No queues.</b>
<? endif ?>