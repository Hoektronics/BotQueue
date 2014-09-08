<?
/**
 * @package botqueue_form
 * @var Form $form
 */
?>
<? if ($form->hasError()): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "There was an error with your form submission."))?>
<? endif ?>

<form class="form-inline" method="<?=$form->method?>" action="<?= $form->action ?>" enctype="multipart/form-data">
	<?= $form->renderFields() ?>
	<button type="submit" class="btn btn-primary"><?= $form->submitText ?></button>
</form>