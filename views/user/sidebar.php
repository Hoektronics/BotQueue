<?php if ($user->isMe() || User::isAdmin()): ?>
	<h3>Meta</h3>
	<ul class="manage">
		<li><a href="/user:<?php echo $user->id ?>/edit">Edit <?php echo ($user->isMe() ? "My" : "This") ?> Profile</a></li>
		<li><a href="/user:<?php echo $user->id ?>/changepass">Edit <?php echo ($user->isMe() ? "My" : "User") ?> Password</a></li>
		<?php if (User::isAdmin()): ?>
			<li><a href="/user:<?php echo $user->id ?>/delete">Delete <?php echo ($user->isMe() ? "Myself" : "This User") ?></a></li>
		<?php endif ?>
	</ul>
<?php endif ?>

