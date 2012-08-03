<form method="post" autocomplete="off" action="/register">
	<? if (!empty($errors)): ?>
		<div class="BaseError">There were errors with your registration.</div>
	<? endif ?>
	<table>
		<tr>
			<td>Username</td>
			<td><input type="text" name="username" value="<?=$username?>"></td>
		</tr>
		<? if ($errors['username']): ?>
			<tr>
				<td class="2"><span class="FormError"><?=$errors['username']?></span></td>
			</tr>
		<? endif ?>
		<tr>
			<td>Email</td>
			<td><input type="text" name="email" value="<?=$email?>"></td>
		</tr>
		<? if ($errors['email']): ?>
			<tr>
				<td class="2"><span class="FormError"><?=$errors['email']?></span></td>
			</tr>
		<? endif ?>
		<tr>
			<td>Password</td>
			<td><input type="password" name="pass1" value="<?=$pass1?>"></td>
		</tr>
		<tr>
			<td>Password again</td>
			<td><input type="password" name="pass2" value="<?=$pass2?>"></td>
		</tr>
		<? if ($errors['password']): ?>
			<tr>
				<td class="2"><span class="FormError"><?=$errors['password']?></span></td>
			</tr>
		<? endif ?>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Register"></td>
		</tr>
	</table>
</form>
