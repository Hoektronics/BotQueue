<?php if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>	
	<table class="table table-striped table-bordered table-condensed">
		<tbody>
			<?php if (strtotime($user->get('last_active'))): ?>
				<tr>
					<th>Last active</th>
					<td><?php echo Utility::getTimeAgo($user->get('last_active')) ?> (<?php echo Utility::formatDate($user->get('last_active')) ?>)</td>
				</tr>
			<?php endif ?>
			<tr>
				<th>I joined</th>
				<td><?php echo Utility::getTimeAgo($user->get('registered_on')) ?> (<?php echo Utility::formatDate($user->get('registered_on')) ?>)</td>
			</tr>
		</tbody>
	</table>
	
	<h2>Recent Activity Stream</h2>
	<?php echo Controller::byName('main')->renderView('draw_activities', array(
		'activities' => $activities->getAll(),
		'user' => $user
	)); ?>
	<?php
		echo Controller::byName('browse')->renderView('pagination', array(
			'collection' => $activities,
			'base_url' => '/user:' . $user->id . '/activity'
		));
	?>
<?php endif ?>
