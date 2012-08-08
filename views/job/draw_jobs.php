<? if (!empty($jobs)): ?>
	<table>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Status</th>
			<th>Elapsed</th>
			<th>Progress</th>
			<th>Bot</th>
			<th>Manage</th>
		</tr>
		<? foreach ($jobs AS $row): ?>
			<? $j = $row['Job'] ?>
			<? $bot = $j->getBot() ?>
			<tr>
				<td><?=$j->id?></td>
				<td><?=$j->getLink()?></td>
				<td><?=$j->get('status')?></td>
				<td><?=Utility::getElapsed($j->get('start'))?></td>
				<td align="right"><?=round($j->get('progress'), 2)?>%</td>
				<? if ($bot->isHydrated()): ?>
					<td><?=$bot->getLink()?></td>
				<? else: ?>
					<td>n/a</td>
				<?endif?>
				<td>delete re-run</td>
			</tr>
		<?endforeach?>
	</table>
<? else: ?>
	<b>No pending jobs.</b>
<? endif ?>