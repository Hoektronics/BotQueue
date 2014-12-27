<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 * @var Form $form
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<div class="row">
		<div class="span12">
			<?= $form->render() ?>
		</div>
	</div>
<? endif ?>