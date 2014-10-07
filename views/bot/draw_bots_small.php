<table class="table table-striped table-bordered table-condensed">
	<tbody>
	<tr>
		<th>Name</th>
		<th>Status</th>
		<th>Last Seen</th>
	</tr>
	<? if (!empty($bots)): ?>
		<? foreach ($bots AS $row): ?>
			<? $bot = $row['Bot'] ?>
			<tr>
				<td><?=$bot->getLink()?></td>
				<td><?=BotStatus::getStatusHTML($bot)?></td>
				<td><?=Utility::relativeTime($bot->get('last_seen'))?></td>
			</tr>
		<? endforeach ?>
	<? else: ?>
		<tr>
			<td colspan="2"><strong>No bots found!</strong></td>
		</tr>
	<? endif ?>
	</tbody>
</table>