<div id="signup" class="span6">
	<div class="title">Not a member? Create a free account:</div>
	<form action="/register" method="post" class="form-stacked">
		<input type="hidden" name="action" value="register">
		<input type="hidden" name="submit" value="1">
		<fieldset>
			<? if (!empty($errors)): ?>
				<div class="control-group">
					<div class="alert alert-error">
						<strong>Oops!</strong> There was a problem creating your account.
					</div>
				</div>
			<? endif ?>
			<div class="control-group <?=$errorfields['username']?>">
				<label class="control-label" for="isignup_username">Username:</label>
				<div class="controls">
					<input id="isignup_username" tabindex="5" name="username" label="Username" value="<?=$username?>" type="text">
					<? if ($errors['username']): ?>
						<span class="help-inline"><?= $errors['username'] ?></span>
					<? endif ?>
				</div>
			</div>
			<div class="control-group <?=$errorfields['email']?>">
				<label class="control-label" for="isignup_email">Email address:</label>
				<div class="controls">
					<input id="isignup_email" tabindex="6" name="email" label="Email address" value="<?=$email?>" type="text">
					<? if ($errors['email']): ?>
						<span class="help-inline"><?= $errors['email'] ?></span>
					<? endif ?>
				</div>
			</div>
			<div class="control-group <?=$errorfields['password']?>">
				<label class="control-label" for="isignup_password">Password:</label>
				<div class="controls">
					<input id="isignup_password" tabindex="7" name="pass1" label="Password" value="" type="password">
					<? if ($errors['password']): ?>
						<span class="help-inline"><?= $errors['password'] ?></span>
					<? endif ?>
				</div>
			</div>
			<div class="control-group <?=$errorfields['password']?>">
				<label class="control-label" for="isignup_password2">Password Again:</label>
				<div class="controls">
					<input id="isignup_password2" tabindex="7" name="pass2" label="Password Again" value="" type="password">
				</div>
			</div>
			<div class="control-group">
				<p class="clickedit">By clicking on the "Create your account" button below, you certify that you have read and agree to our 
					<a href="/tos" title="Terms of use" target="_blank">terms of use</a> and <a href="/privacy" title="Privacy policy" target="_blank">privacy policy</a>.</p>
			</div>
			<div class="actions"><input tabindex="9" class="btn btn-success btn-large" type="submit" value="Create your account"></div>
		</fieldset>
	</form>
</div>