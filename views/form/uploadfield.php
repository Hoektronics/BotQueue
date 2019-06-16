<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
  	<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
  <? endif ?>
	<div class="controls">
    <input type="file" <?php echo $field->getAttributes() ?>>
		<? if ($field->hasError): ?>
			<span class="help-inline"><?php echo $field->errorText ?></span>
		<? endif ?>
		<? if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<? endif ?>
	</div>
</div>