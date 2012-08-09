<? if (!empty($queues)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th>Name</th>
				<th>Available</th>
				<th>Working</th>
				<th>Completed</th>
				<th>Failed</th>
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
					<td><span class="label <?=Job::getStatusHTMLClass('taken')?>"><?= (int)$stats['taken'] ?></span></td>
					<td><span class="label <?=Job::getStatusHTMLClass('complete')?>"><?= (int)$stats['complete'] ?></span></td>
					<td><span class="label <?=Job::getStatusHTMLClass('failure')?>"><?= (int)$stats['failure'] ?></span></td>
					<td><span class="label label-inverse"><?= (int)$stats['total'] ?></span></td>
				</tr>
			<?endforeach?>
		</tbody>
	</table>
<? else: ?>
	<b>No queues.</b>
<? endif ?>