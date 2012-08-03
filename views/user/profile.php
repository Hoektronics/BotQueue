<? if ($megaerror): ?>
	<div class="BaseError"><?=$megaerror?></div>
<? else: ?>
	<table>
		<tr>
			<td valign="top">
				<? if ($user->isMe()): ?>
					<h1>Welcome, <?=$user->getName()?></h1>
				<? else: ?>
					<h1>About <?=$user->getName()?></h1>
				<? endif ?>
				<table width="100%">
					<? if ($user->get('location')): ?>
						<tr>
							<td><b>Location:</b></td>
							<td><?= Utility::convertToHTML($user->get('location')) ?></td>
						</tr>
					<? endif ?>
					<? if (strtotime($user->get('birthday'))): ?>
						<tr>
							<td><b>Birthday:</b></td>
							<td><?= Utility::formatDate($user->get('birthday')) ?></td>
						</tr>
					<? endif ?>
					<? if (strtotime($user->get('last_active'))): ?>
						<tr>
							<td><b>Last active</b></td>
							<td><?= Utility::getTimeAgo($user->get('last_active')) ?> (<?= Utility::formatDate($user->get('last_active')) ?>)</td>
						</tr>
					<? endif ?>
					<tr>
						<td><b>I joined</b></td>
						<td><?= Utility::getTimeAgo($user->get('registered_on')) ?> (<?= Utility::formatDate($user->get('registered_on')) ?>)</td>
					</tr>
				</table>
				<? if ($user->get('bio')): ?>
					<p>
						<?= Utility::convertToHTML($user->get('bio')) ?>
					</p>
				<? endif ?>
			</td>
		</tr>
	</table>
	
	<br/>

	<h1>Your Recent Activity Stream</h1>
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
