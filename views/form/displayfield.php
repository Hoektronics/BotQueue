<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
  	<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
  <? endif ?>
	<div class="controls" style="margin-top: 5px">
    <?php echo $field->getValue() ?>
		<? if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<? endif ?>
	</div>
</div>