<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
	<? if ($status): ?>
    <?= Controller::byName('htmltemplate')->renderView('statusbar', array('message' => $status))?>
	<? elseif ($error): ?>
	  <?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error))?>
	<? elseif ($user->get('force_password_change')): ?>
	  <?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => 'You are required to change your password before continuing.'))?>
	<? endif ?>
	
	<form method="post" autocomplete="off" action="/user:<?=$user->id?>/changepass">
		<table class="BaseForm">
			<tr>
				<td valign="top"><b>Enter Password</b></td>
				<td><input type="password" name="changepass1" class="input-medium"></td>
			</tr>
			<tr>
				<td valign="top">
					<b>Password Again</b>
				</td>
				<td>
					<input type="password" name="changepass2" value="" class="input-medium"><br/>
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
