<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthToken $token
 * @var OAuthConsumer $app
 */
?>
<? if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<?php echo $form->render(); ?>
<? endif ?>