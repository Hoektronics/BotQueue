<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
	<table class="table table-striped table-bordered table-condensed">
		<tbody>
			<? if ($user->get('location')): ?>
				<tr>
					<th>Location:</th>
					<td><?= Utility::convertToHTML($user->get('location')) ?></td>
				</tr>
			<? endif ?>
			<? if (strtotime($user->get('birthday'))): ?>
				<tr>
					<th>Birthday:</th>
					<td><?= Utility::formatDate($user->get('birthday')) ?></td>
				</tr>
			<? endif ?>
			<? if (strtotime($user->get('last_active'))): ?>
				<tr>
					<th>Last active</th>
					<td><?= Utility::getTimeAgo($user->get('last_active')) ?> (<?= Utility::formatDate($user->get('last_active')) ?>)</td>
				</tr>
			<? endif ?>
			<tr>
				<th>I joined</th>
				<td><?= Utility::getTimeAgo($user->get('registered_on')) ?> (<?= Utility::formatDate($user->get('registered_on')) ?>)</td>
			</tr>
		</tbody>
	</table>
	<? if ($user->get('bio')): ?>
		<p>
			<?= Utility::convertToHTML($user->get('bio')) ?>
		</p>
	<? endif ?>
	
	<h2>Recent Activity Stream</h2>
	<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
	<?
		echo Controller::byName('browse')->renderView('pagination', array(
			'page' => 1,
			'per_page' => 25,
			'base_url' => '/user:' . $user->id . '/activity',
			'total' => $activity_total
		));
	?>
<? endif ?>
