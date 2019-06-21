<?php if (!empty($queues)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th>Name</th>
			<th>Available</th>
			<th>Slicing</th>
			<th>Working</th>
			<th>Completed</th>
			<th>Failed</th>
			<th>Total</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($queues AS $row): ?>
			<?php $q = $row['Queue'] ?>
			<?php $stats = QueueStats::getStats($q) ?>
			<?php
			$total['available'] += $stats['available'];
			$total['slicing'] += $stats['slicing'];
			$total['taken'] += $stats['taken'];
			$total['complete'] += $stats['complete'];
			$total['failure'] += $stats['failure'];
			$total['total'] += $stats['total'];
			?>
			<tr>
				<td><?php echo $q->getLink() ?></td>
                <td><?php echo JobStatus::getStatsHtml($stats, "available") ?></td>
                <td><?php echo JobStatus::getStatsHtml($stats, "slicing") ?></td>
                <td><?php echo JobStatus::getStatsHtml($stats, "taken") ?></td>
                <td><?php echo JobStatus::getStatsHtml($stats, "complete") ?></td>
                <td><?php echo JobStatus::getStatsHtml($stats, "failure") ?></td>
				<td><span class="label label-inverse"><?php echo (int)$stats['total'] ?></span></td>
			</tr>
		<?php endforeach; ?>
		<?php if (count($queues) > 1): ?>
			<tr>
				<th>Total</th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('available') ?>"><?php echo (int)$total['available'] ?></span>
                    <?php echo round(($total['available'] / $total['total']) * 100, 2) ?>%
                </th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('slicing') ?>"><?php echo (int)$total['slicing'] ?></span>
                    <?php echo round(($total['slicing'] / $total['total']) * 100, 2) ?>%
                </th>
				<th><span class="label <?php echo JobStatus::getStatusHTMLClass('taken') ?>"><?php echo (int)$total['taken'] ?></span>
                    <?php echo round(($total['taken'] / $total['total']) * 100, 2) ?>%
                </th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('complete') ?>"><?php echo (int)$total['complete'] ?></span>
                    <?php echo round(($total['complete'] / $total['total']) * 100, 2) ?>%
                </th>
				<th><span
						class="label <?php echo JobStatus::getStatusHTMLClass('failure') ?>"><?php echo (int)$total['failure'] ?></span>
                    <?php echo round(($total['failure'] / $total['total']) * 100, 2) ?>%
                </th>
				<th><span class="label label-inverse"><?php echo (int)$total['total'] ?></span></th>
			</tr>
		<?php endif ?>
		</tbody>
	</table>
<?php else: ?>
	<b>No queues.</b>
<?php endif ?>