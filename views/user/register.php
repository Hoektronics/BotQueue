<div class="row-fluid">
	<div id="page_signin">
		<div id="signin" class="span6">
			<div class="title">Already a member? Sign in:</div>
			<form action="/login" method="post">
				<input type="hidden" name="next" value="/">
				<fieldset>
					<div class="clearfix">
						<label for="iusername"><span>Username:</span></label>
						<div class="input">
							<input tabindex="1" id="iusername" name="username" label="Username" value="" type="text">
						</div>
					</div>
					<div class="clearfix">
						<label for="ipassword"><span>Password:</span></label>
						<div class="input">
							<input tabindex="2" id="ipassword" name="password" label="Password" value="" type="password">
						</div>
					</div>
					<div class="clearfix">
							<input tabindex="3" id="irememberme" name="remember_me" label="Remember Me?" value="1" checked="true" type="checkbox">
							<label class="lrememberme" for="irememberme"><span>Remember me on this computer.</span></label>
					</div>
					<div class="actions">
						<input tabindex="4" class="btn btn-primary btn-large" type="submit" value="Sign into your account">
						<p class="reset"><a tabindex="4" href="/forgotpass" title="Recover your username or password">Forgot your username or password?</a></p>
					</div>
				</fieldset>
			</form>
		</div>
		<div id="signup" class="span6">
			<div class="title">Not a member? Create a free account:</div>
			<form action="/register" method="post" class="form-stacked">
				<fieldset>
					<div class="clearfix">
						<label for="isignup_username">Username:</label>
						<div class="input">
							<input id="isignup_username" tabindex="5" name="username" label="Username" value="" type="text">
						</div>
					</div>
					<div class="clearfix">
						<label for="isignup_email">Email address:</label>
						<div class="input">
							<input id="isignup_email" tabindex="6" name="email" label="Email address" value="" type="text">
						</div>
					</div>
					<div class="clearfix">
						<label for="isignup_password">Password:</label>
						<div class="input">
							<input id="isignup_password" tabindex="7" name="pass1" label="Password" value="" type="password">
						</div>
					</div>
					<div class="clearfix">
						<label for="isignup_password2">Password Again:</label>
						<div class="input">
							<input id="isignup_password2" tabindex="7" name="pass2" label="Password Again" value="" type="password">
						</div>
					</div>
					<div class="clearfix">
						<p class="clickedit">By clicking on the "Create your account" button below, you certify that you have read and agree to our 
							<a href="/tos" title="Terms of use" target="_blank">terms of use</a> and <a href="/privacy" title="Privacy policy" target="_blank">privacy policy</a>.</p>
					</div>
					<div class="actions"><input tabindex="9" class="btn btn-success btn-large" type="submit" value="Create your account"></div>
				</fieldset>
			</form>
		</div>
	</div>
</div>