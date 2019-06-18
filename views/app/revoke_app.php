<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthToken $token
 * @var OAuthConsumer $app
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<?php echo $form->render(); ?>
<?php endif ?>