<div class="control-group">
	<div class="controls">
	  <? if ($field->label): ?>
    	<label class="checkbox">
    	  <input type="checkbox" <?php echo $field->getAttributes() ?> <?php echo ($field->is_checked) ? 'checked="true"' : '' ?> value="1">
    	  <strong><?php echo $field->label ?></strong>
      </label>
    <? endif ?>
		<? if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<? endif ?>
	</div>
</div>