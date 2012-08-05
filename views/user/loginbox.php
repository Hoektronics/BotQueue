<div id="login_box">
	<? if (User::isLoggedIn()): ?>
  	<strong>Welcome, <?= $user->getLink() ?></strong>
  	<br/>
  	<small><em><a href="/logout">log out</a></em></small>
	<? else: ?>
	  <strong>Welcome, Friend</strong>
		<br/>
		<small><em><a href="/login">Log in</a> or <a href="/register">Register</a></em></small>
	<? endif ?>
</div>
