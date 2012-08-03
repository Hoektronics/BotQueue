<div id="login_box">
	<? if (User::isLoggedIn()): ?>
  	<strong>Welcome, <?= $user->getLink() ?></strong>
  	<br/>
  	<small><em><a href="/logout">log out</a></em></small>
	<? endif ?>
</div>
