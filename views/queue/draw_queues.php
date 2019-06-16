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
			<? $stats = QueueStats::getStats($q) ?>
			<?
			$total['available'] += $stats['available'];
			$total['slicing'] += $stats['slicing'];
			$total['taken'] += $stats['taken'];
			$total['complete'] += $stats['complete'];
			$total['failure'] += $stats['failure'];
			$total['total'] += $stats['total'];
			?>
			<tr>
				<td><?php echo $q->getLink() ?></td>
				<td><span
						class="label <?php echo JobStatus::getStatusHTMLClass('available') ?>"><?php echo (int)$stats['available'] ?></span>
				</td>
				<td><?php echo round($stats['available_pct'], 2) ?>%</td>
				<td><span
						class="label <?php echo JobStatus::getStatusHTMLClass('slicing') ?>"><?php echo (int)$stats['slicing'] ?></span>
				</td>
				<td><?php echo round($stats['slicing_pct'], 2) ?>%</td>
				<td><span class="label <?php echo JobStatus::getStatusHTMLClass('taken') ?>"><?php echo (int)$stats['taken'] ?></span>
				</td>
				<td><?php echo round($stats['taken_pct'], 2) ?>%</td>
				<td><span
						class="label <?php echo JobStatus::getStatusHTMLClass('complete') ?>"><?php echo (int)$stats['complete'] ?></span>
				</td>
				<td><?php echo round($stats['complete_pct'], 2) ?>%</td>
				<td><span
						class="label <?php echo JobStatus::getStatusHTMLClass('failure') ?>"><?php echo (int)$stats['failure'] ?></span>
				</td>
				<td><?php echo round($stats['failure_pct'], 2) ?>%</td>
				<td><span class="label label-inverse"><?php echo (int)$stats['total'] ?></span></td>
			</tr>
		<? endforeach ?>
		<? if (count($queues) > 1): ?>
			<tr>
				<th>Total</th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('available') ?>"><?php echo (int)$total['available'] ?></span>
				</th>
				<th><?php echo round(($total['available'] / $total['total']) * 100, 2) ?>%</th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('slicing') ?>"><?php echo (int)$total['slicing'] ?></span>
				</th>
				<th><?php echo round(($total['slicing'] / $total['total']) * 100, 2) ?>%</th>
				<th><span class="label <?php echo JobStatus::getStatusHTMLClass('taken') ?>"><?php echo (int)$total['taken'] ?></span>
				</th>
				<th><?php echo round(($total['taken'] / $total['total']) * 100, 2) ?>%</th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('complete') ?>"><?php echo (int)$total['complete'] ?></span>
				</th>
				<th><?php echo round(($total['complete'] / $total['total']) * 100, 2) ?>%</th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('failure') ?>"><?php echo (int)$total['failure'] ?></span>
				</th>
				<th><?php echo round(($total['failure'] / $total['total']) * 100, 2) ?>%</th>
				<th><span class="label label-inverse"><?php echo (int)$total['total'] ?></span></th>
			</tr>
		<? endif ?>
		</tbody>
	</table>
<? else: ?>
	<b>No queues.</b>
<? endif ?>