Hello <?=$user->getLink() ?>,
<br/><br/>
Someone requested a password reset on <a href="http://<?=SITE_HOSTNAME?>"><?=SITE_HOSTNAME?></a>.  In order to verify and complete with this password reset operation, please follow the link below:
<br/><br/>
<a href="<?=$link?>"><?=$link?></a>
<br/><br/>
If you did not request this password reset, don't worry.  Your password is safe and you can disregard this email.
<br/><br/>
Thanks,<br/>
<?=RR_PROJECT_NAME?> Mailman
