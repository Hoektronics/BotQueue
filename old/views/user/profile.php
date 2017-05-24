<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
	<table class="table table-striped table-bordered table-condensed">
		<tbody>
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
	
	<h2>Recent Activity Stream</h2>
	<?= Controller::byName('main')->renderView('draw_activities', array(
		'activities' => $activities->getAll(),
		'user' => $user
	)); ?>
	<?
		echo Controller::byName('browse')->renderView('pagination', array(
			'collection' => $activities,
			'base_url' => '/user:' . $user->id . '/activity'
		));
	?>
<? endif ?>
