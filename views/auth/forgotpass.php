<? if ($status): ?>
	<div class="BaseStatus"><?=$status?></div>
<? else: ?>
	<? if ($error): ?>
		<div class="BaseError"><?=$error?></div>
	<? endif ?>

	Enter your email below and we'll send you an email with your username and a link to reset your password.

	<form method="post" action="/forgotpass">
		<table>
			<tr>
				<td><b>Email</b></td>
				<td><input type="text" name="email" value="<?=$email?>" class="input-xlarge"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="submit" value="Send it"></td>
			</tr>
		</table>
	</form>
<? endif ?>
