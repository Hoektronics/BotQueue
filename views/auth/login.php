<h1>Login to <?=RR_PROJECT_NAME?></h1>
<form method="post" action="/login">
	<? if ($errors['login']): ?>
		<span class="FormError"><?=$errors['login']?></span>
	<? endif ?>

	<label>Username</label>
	<input type="text" name="username" value="" id="username">
	<? if ($errors['username']): ?>
		<span class="FormError"><?=$errors['username']?></span>
	</tr>
	<? endif ?>

	<label>Password</label>
	<input type="password" name="password" value="" id="password">
	<? if ($errors['password']): ?>
		<span class="FormError"><?=$errors['password']?></span>
	<? endif ?>

	<label><input type="checkbox" name="rememberme" value="1" checked="true"> Remember me</label>

	<input type="submit" name="submit" value="Login" class="login">
	<br/>
	<a href="/forgotpass">Forgot your username or password?</a>  Don't have an account? <a href="/register">Register today!</a>
</form>