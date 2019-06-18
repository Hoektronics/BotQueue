<?php
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $app
 * @var Form $form
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<div class="row">
		<div class="span12">
			<?php echo $form->render() ?>
		</div>
	</div>
<?php endif ?>