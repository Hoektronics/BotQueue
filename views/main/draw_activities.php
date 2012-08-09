<? if (!empty($activities)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<tbody>
			<? foreach ($activities AS $row): ?>
				<? $user = $row['User'] ?>
				<? $activity = $row['Activity'] ?>
				<tr>
					<td valign="top" align="left">
						<?= $user->getLink() ?> <?= $activity->get('activity') ?>
						<?= Utility::getTimeAgo($activity->get('action_date')) ?>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
<? else: ?>
	<b>No activity found.</b>
<? endif ?>