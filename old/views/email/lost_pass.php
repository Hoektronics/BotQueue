<?
/**
 * @package botqueue_email
 * @var User $user
 * @var string $link
 */
?>
Dear <?=$user->getName() ?>,

Someone requested a password reset on <?=SITE_HOSTNAME?>.  In order to verify and complete with this password reset operation, please follow the link below:

<?=$link?>

If you did not request this password reset, don't worry.  Your password is safe and you can disregard this email.

Sincerely,
The <?=RR_PROJECT_NAME?> Team
