<? if (!empty($users)): ?>
	<? foreach ($users AS $row): ?>
		<? $user = $row['User'] ?>
		<div class="user_row">
			<div class="user_name">
				<?= $user->getLink() ?>
			</div>
		</div>
	<? endforeach ?>
	<div class="clear"></div>
<? else: ?>
	<b>No users found.</b>
<? endif ?>
