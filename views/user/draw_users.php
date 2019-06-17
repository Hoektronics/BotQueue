<?php if (!empty($users)): ?>
	<?php foreach ($users AS $row): ?>
		<?php $user = $row['User'] ?>
		<div class="user_row">
			<div class="user_name">
				<?php echo $user->getLink() ?>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="clear"></div>
<?php else: ?>
	<b>No users found.</b>
<?php endif ?>
