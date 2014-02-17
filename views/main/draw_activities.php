<? if (!empty($activities)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th>Who</th>
			<th>What</th>
			<th>When</th>
		</tr>
		</thead>
		<tbody>
		<? foreach ($activities AS $row): ?>
			<? $user = $row['User'] ?>
			<? $activity = $row['Activity'] ?>
			<tr>
				<td><?= $user->getLink() ?></td>
				<td><?= $activity->get('activity') ?></td>
				<td><span class="muted"><?= Utility::getTimeAgo($activity->get('action_date')) ?></span></td>
			</tr>
		<? endforeach ?>
		</tbody>
	</table>
<? else: ?>
	<b>No activity found.</b>
<? endif ?>