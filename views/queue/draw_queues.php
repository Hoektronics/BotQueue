<? if (!empty($queues)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th>Name</th>
				<th colspan="2">Available</th>
        <th colspan="2">Slicing</th>
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
				<?
					$total['available'] += $stats['available'];
          $total['slicing'] += $stats['slicing'];
					$total['taken'] += $stats['taken'];
					$total['complete'] += $stats['complete'];
					$total['failure'] += $stats['failure'];
					$total['total'] += $stats['total'];
				?>
				<tr>
					<td><?=$q->getLink()?></td>
					<td><span class="label <?=Job::getStatusHTMLClass('available')?>"><?= (int)$stats['available'] ?></span></td>
					<td><?= round($stats['available_pct'], 2)?>%</td>
          <td><span class="label <?=Job::getStatusHTMLClass('slicing')?>"><?= (int)$stats['slicing'] ?></span></td>
          <td><?= round($stats['slicing_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$stats['taken'] ?></span></td>
					<td><?= round($stats['taken_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$stats['complete'] ?></span></td>
					<td><?= round($stats['complete_pct'], 2)?>%</td>
					<td><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$stats['failure'] ?></span></td>
					<td><?= round($stats['failure_pct'], 2)?>%</td>
					<td><span class="label label-inverse"><?= (int)$stats['total'] ?></span></td>
				</tr>
			<?endforeach?>
			<? if (count($queues) > 1): ?>
				<tr>
					<th>Total</th>
					<th><span class="label <?=Job::getStatusHTMLClass('available')?>"><?= (int)$total['available'] ?></span></th>
					<th><?= round(($total['available'] / $total['total'])*100, 2)?>%</th>
          <th><span class="label <?=Job::getStatusHTMLClass('slicing')?>"><?= (int)$total['slicing'] ?></span></th>
          <th><?= round(($total['slicing'] / $total['total'])*100, 2)?>%</th>
					<th><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$total['taken'] ?></span></th>
					<th><?= round(($total['taken'] / $total['total'])*100, 2)?>%</th>
					<th><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$total['complete'] ?></span></th>
					<th><?= round(($total['complete'] / $total['total'])*100, 2)?>%</th>
					<th><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$total['failure'] ?></span></th>
					<th><?= round(($total['failure'] / $total['total'])*100, 2)?>%</th>
					<th><span class="label label-inverse"><?= (int)$total['total'] ?></span></th>
				</tr>
			<? endif ?>
		</tbody>
	</table>
<? else: ?>
	<b>No queues.</b>
<? endif ?>