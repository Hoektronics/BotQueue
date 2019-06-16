<?php
/**
 * @package botqueue_email
 * @var User $user
 * @var string $link
 */
?>
Dear <?php echo $user->getName() ?>,

Someone requested a password reset on <?php echo SITE_HOSTNAME ?>.  In order to verify and complete with this password reset operation, please follow the link below:

<?php echo $link ?>

If you did not request this password reset, don't worry.  Your password is safe and you can disregard this email.

Sincerely,
The <?php echo RR_PROJECT_NAME ?> Team
