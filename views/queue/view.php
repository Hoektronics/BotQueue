<h2><?=$queue->getName()?></h2>

<h3>Job Queue</h3>
<? if (!empty($jobs)): ?>
	<table>
		<tr>
			<th>J#</th>
			<th>Name</th>
			<th>Status</th>
			<th>Elapsed</th>
		</tr>
		<? foreach ($jobs AS $row): ?>
			<? $j = $row['Job'] ?>
			<tr>
				<td><?=$j->id?></td>
				<td><?=$j->getLink()?></td>
				<td><?=$j->get('status')?></td>
				<td><?=Utility::getElapsed($j->get('start'))?></td>
			</tr>
		<?endforeach?>
	</table>
<? else: ?>
	<b>No pending jobs.</b>
<? endif ?>