<?php if ($form->hasError()): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "There was an error with your form submission.")) ?>
<?php endif ?>

<form class="form-horizontal" method="<?php echo $form->method ?>" action="<?php echo $form->action ?>" enctype="multipart/form-data">
	<fieldset>
		<?php echo $form->renderFields() ?>
  		<div class="form-actions">
			<button type="submit" class="<?php echo $form->submitClass ?>"><?php echo $form->submitText ?></button>
		</div>
	</fieldset>
</form>