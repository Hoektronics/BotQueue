<div id="signin" class="span6">
	<div class="title">Already a member? Sign in:</div>
	<form action="/login" method="post">
		<input type="hidden" name="action" value="login">
		<input type="hidden" name="submit" value="1">
		<fieldset>
			<? if (!empty($errors)): ?>
				<div class="control-group">
					<div class="alert alert-error">
						<strong>Oops!</strong> There was a problem logging you in.
					</div>
				</div>
			<? endif ?>
			<div class="control-group <?=$errorfields['username']?>">
				<label for="iusername"><span>Username:</span></label>
				<div class="input">
					<input tabindex="1" id="iusername" name="username" label="Username" value="<?=$username?>" type="text">
					<? if ($errors['username']): ?>
						<span class="help-inline"><?= $errors['username'] ?></span>
					<? endif ?>
				</div>
			</div>
			<div class="control-group <?=$errorfields['password']?>">
				<label for="ipassword"><span>Password:</span></label>
				<div class="input">
					<input tabindex="2" id="ipassword" name="password" label="Password" value="" type="password">
					<? if ($errors['password']): ?>
						<span class="help-inline"><?= $errors['password'] ?></span>
					<? endif ?>
				</div>
			</div>
			<div class="control-group">
					<input tabindex="3" id="irememberme" name="rememberme" label="Remember Me?" value="1" checked="true" type="checkbox">
					<label class="lrememberme" for="irememberme"><span>Remember me on this computer.</span></label>
			</div>
			<div class="actions">
				<input tabindex="4" class="btn btn-primary btn-large" type="submit" value="Sign into your account">
				<p class="reset"><a tabindex="4" href="/forgotpass" title="Recover your username or password">Forgot your username or password?</a></p>
			</div>
		</fieldset>
	</form>
</div>