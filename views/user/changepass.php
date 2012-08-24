<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
	<? if ($status): ?>
		<div class="BaseStatus"><?=$status?></div>
	<? elseif ($error): ?>
		<div class="BaseError"><?=$error?></div>
	<? elseif ($user->get('force_password_change')): ?>
		<div class="BaseError">You are required to change your password before continuing.</div>
	<? endif ?>
	
	<form method="post" autocomplete="off" action="/user:<?=$user->id?>/changepass">
		<table class="BaseForm">
			<tr>
				<td valign="top"><b>Change Password</b></td>
				<td><input type="password" name="changepass1" value="" style="width: 50%"></td>
			</tr>
			<tr>
				<td valign="top">
					<b>Password Again</b>
				</td>
				<td>
					<input type="password" name="changepass2" value="" style="width: 50%"><br/>
				</td>
			</tr>
			<? if ($errors['password']): ?>
				<tr>
					<td class="2">
						<span class="FormError"><?=$errors['password']?></span>
					</td>
				</tr>
			<? endif ?>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="submit" value="Change <?=($user->isMe() ? "My" : "User")?> Password"></td>
			</tr>
		</table>
	</form>
<? endif ?>
