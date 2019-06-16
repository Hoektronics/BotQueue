<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 * @var Form $form
 */
?>
<? if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<div class="row">
		<div class="span12">
			<?php echo $form->render() ?>
		</div>
	</div>
<? endif ?>