<? if (!empty($bots)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th>Name</th>
			<th>Status</th>
			<th>Last Seen</th>
			<th>Job</th>
		</tr>
		</thead>
		<tbody>
		<? foreach ($bots AS $row): ?>
			<? $b = $row['Bot'] ?>
			<? $j = $row['Job'] ?>
			<tr>
				<td><?=$b->getLink()?></td>
				<td><?=BotStatus::getStatusHTML($b);?></td>
				<td><?=Utility::relativeTime($b->get('last_seen'))?></td>
				<? if ($j->isHydrated()): ?>
					<td><?=$j->getLink()?></td>
				<? else: ?>
					<td>none</td>
				<? endif ?>
			</tr>
		<?endforeach?>
		</tbody>
	</table>
<? else: ?>
	<div class="alert">
		<strong>No bots found!</strong> To get started, <a href="/bot/register">register a bot</a>.
	</div>
<? endif ?>