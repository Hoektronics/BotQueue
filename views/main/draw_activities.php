<?php if (!empty($activities)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th>Who</th>
			<th>What</th>
			<th>When</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($activities AS $row): ?>
			<?php $activity = $row['Activity'] ?>
			<?php $datetime = $activity->get('action_date') ?>
			<tr>
				<td><?php echo $user->getLink() ?></td>
				<td><?php echo $activity->get('activity') ?></td>
				<td><span class="muted"><?php echo Utility::getTimeAgo($datetime) ?></span></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<b>No activity found.</b>
<?php endif ?>