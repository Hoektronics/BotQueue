<? if ($megaerror): ?>
	<div class="BaseError"><?=$megaerror?></div>
<? else: ?>
	<? if ($status): ?>
		<div class="BaseStatus"><?=$status?></div>
	<? elseif ($error): ?>
		<div class="BaseError"><?=$error?></div>
	<? endif ?>
	
	<div id="user_details">
		<form method="post" action="/user:<?=$user->id?>/edit">
			<table class="BaseForm">
				<? if (User::isAdmin()): ?>
					<tr>
						<td><b>Admin access</b></td>
						<td><label><input type="checkbox" name="is_admin" value="1" <? if ($user->get('is_admin')) { echo "checked"; }?>> Is the user an admin?</label></td>
					</tr>
				<? endif ?>
				<tr>
					<td valign="top" width="250"><b>Username</b></td>
					<? if (User::isAdmin()): ?>
						<td><input type="text" name="username" value="<?=$user->get('username')?>" style="width: 100%"></td>
					<? else: ?>
						<td><?=$user->get('username')?></td>
					<? endif ?>
				</tr>
				<tr>
					<td valign="top"><b>Email</b></td>
					<td><input type="text" name="email" value="<?=$user->get('email')?>" style="width: 100%"></td>
				</tr>
				<? if ($errors['email']): ?>
					<tr>
						<td class="2"><span class="FormError"><?=$errors['email']?></span></td>
					</tr>
				<? endif ?>
				<tr>
					<td valign="top">
						<b>Birthday</b><br/>
						<span class="formtip">MM/DD/YYYY is the best format.</span>
					</td>
					<td>
						<input type="text" name="birthday" value="<? if (strtotime($user->get('birthday'))) { echo date("m/d/Y", strtotime($user->get('birthday'))); } ?>" style="width: 100%">
					</td>
				</tr>
				<? if ($errors['birthday']): ?>
					<tr>
						<td class="2"><span class="FormError"><?=$errors['birthday']?></span></td>
					</tr>
				<? endif ?>
				<tr>
					<td valign="top"><b>Location</b></td>
					<td><input type="text" name="location" value="<?=$user->get('location')?>" style="width: 100%"></td>
				</tr>
				<tr>
					<td valign="top"><b>Change Password</b></td>
					<td>Please visit the <a href="/user:<?=$user->id?>/changepass">change password</a> page.</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="submit" value="Update your information"></td>
				</tr>
			</table>
		</form>
	</div>
<? endif ?>
	
