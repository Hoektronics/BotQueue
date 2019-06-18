<?php
/**
 * @package botqueue_auth
 * @var string $status
 * @var string $error
 * @var Form $form
 */
?>
<?php if (isset($status)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('statusbar', array('message' => $status)) ?>
<?php else: ?>
	<?php if (isset($error)): ?>
		<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error)) ?>
	<?php endif ?>

	<div>
		<?php echo $form->render() ?>
	</div>
<?php endif ?>
