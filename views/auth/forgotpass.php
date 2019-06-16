<?
/**
 * @package botqueue_auth
 * @var string $status
 * @var string $error
 * @var Form $form
 */
?>
<? if ($status): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('statusbar', array('message' => $status)) ?>
<? else: ?>
	<? if ($error): ?>
		<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error)) ?>
	<? endif ?>

	<div>
		<?php echo $form->render() ?>
	</div>
<? endif ?>
