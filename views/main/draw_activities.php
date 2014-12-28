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
			<? $activity = $row['Activity'] ?>
			<? $datetime = $activity->get('action_date') ?>
			<tr>
				<td><?= $user->getLink() ?></td>
				<td><?= $activity->get('activity') ?></td>
				<td><span class="muted"><?= Utility::getTimeAgo($datetime) ?></span></td>
			</tr>
		<? endforeach ?>
		</tbody>
	</table>
<? else: ?>
	<b>No activity found.</b>
<? endif ?>