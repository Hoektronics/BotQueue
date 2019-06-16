<?
/**
 * @package botqueue_form
 * @var Form $form
 */
?>
<? if ($form->hasError()): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "There was an error with your form submission.")) ?>
<? endif ?>

<form class="form-inline" method="<?php echo $form->method ?>" action="<?php echo $form->action ?>" enctype="multipart/form-data">
	<?php echo $form->renderFields() ?>
	<button type="submit" class="btn btn-primary"><?php echo $form->submitText ?></button>
</form>