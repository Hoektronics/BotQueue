<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
		<div class="g-recaptcha" data-sitekey="<?=GOOGLE_CAPTCHA_SITE_KEY?>"></div>
	</div>
</div>