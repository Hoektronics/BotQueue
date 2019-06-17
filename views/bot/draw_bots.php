<?php if (!empty($bots)): ?>
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
		<?php foreach ($bots AS $row): ?>
			<?php $b = $row['Bot'] ?>
			<?php $j = $row['Job'] ?>
			<tr>
				<td><?php echo $b->getLink() ?></td>
				<td><?php echo BotStatus::getStatusHTML($b); ?></td>
				<td><?php echo Utility::relativeTime($b->get('last_seen')) ?></td>
				<?php if ($j->isHydrated()): ?>
					<td><?php echo $j->getLink() ?></td>
				<?php else: ?>
					<td>none</td>
				<?php endif ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<div class="alert">
		<strong>No bots found!</strong> To get started, <a href="/bot/register">register a bot</a>.
	</div>
<?php endif ?>