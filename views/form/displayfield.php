<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<?php if ($field->label): ?>
  	<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
  <?php endif ?>
	<div class="controls" style="margin-top: 5px">
    <?php echo $field->getValue() ?>
		<?php if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<?php endif ?>
	</div>
</div>