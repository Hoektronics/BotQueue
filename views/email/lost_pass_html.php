<?php
/**
 * @package botqueue_email
 * @var User $user
 * @var string $link
 */
?>
Hello <?php echo $user->getLink() ?>,
<br/><br/>
Someone requested a password reset on <a href="http://<?php echo SITE_HOSTNAME ?>"><?php echo SITE_HOSTNAME ?></a>.  In order to verify and complete with this password reset operation, please follow the link below:
<br/><br/>
<a href="<?php echo $link ?>"><?php echo $link ?></a>
<br/><br/>
If you did not request this password reset, don't worry.  Your password is safe and you can disregard this email.
<br/><br/>
Sincerely,<br/>
The <?php echo RR_PROJECT_NAME ?> Team
