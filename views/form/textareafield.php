<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<?php if ($field->label): ?>
  	<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
  <?php endif ?>
	<div class="controls">
	  <textarea <?php echo $field->getAttributes() ?> style="width: <?php echo $field->width ?>;"><?php echo $field->getValue() ?></textarea>
		<?php if ($field->hasError): ?>
			<span class="help-inline"><?php echo $field->errorText ?></span>
		<?php endif ?>
		<?php if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<?php endif ?>
	</div>
</div>