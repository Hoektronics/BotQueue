<? if ($form->hasError()): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "There was an error with your form submission."))?>
<? endif ?>

<form class="form-horizontal" method="<?=$form->method?>" action="<?= $form->action ?>">
	<fieldset>
		<?= $form->renderFields() ?>
  	<div class="form-actions">
			<button type="submit" class="btn btn-primary"><?= $form->submitText ?></button>
		</div>
	</fieldset>
</form>