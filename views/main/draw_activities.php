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
			<? $datetime = $row['DateTime'] ?>
			<tr>
				<td><?= $row['user_link'] ?></td>
				<td><?= $row['activity'] ?></td>
				<td><span class="muted"><?= Utility::getTimeAgo($datetime->format("Y-m-d H:i:s")) ?></span></td>
			</tr>
		<? endforeach ?>
		</tbody>
	</table>
<? else: ?>
	<b>No activity found.</b>
<? endif ?>