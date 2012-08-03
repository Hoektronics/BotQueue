<? if ($user->isMe() || User::isAdmin()): ?>
	<h3>Meta</h3>
	<ul class="manage">
		<li><a href="/user:<?=$user->id?>/edit">Edit <?=($user->isMe() ? "My" : "This")?> Profile</a></li>
		<li><a href="/user:<?=$user->id?>/changepass">Edit <?=($user->isMe() ? "My" : "User")?> Password</a></li>
		<? if (User::isAdmin()): ?>
			<li><a href="/user:<?=$user->id?>/delete">Delete <?=($user->isMe() ? "Myself" : "This User")?></a></li>
		<? endif ?>
	</ul>
<? endif ?>

