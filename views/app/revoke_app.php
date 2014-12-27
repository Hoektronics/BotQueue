<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthToken $token
 * @var OAuthConsumer $app
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<?= $form->render(); ?>
<? endif ?>