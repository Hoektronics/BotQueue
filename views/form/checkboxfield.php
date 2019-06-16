<div class="control-group">
	<div class="controls">
	  <?php if ($field->label): ?>
    	<label class="checkbox">
    	  <input type="checkbox" <?php echo $field->getAttributes() ?> <?php echo ($field->is_checked) ? 'checked="true"' : '' ?> value="1">
    	  <strong><?php echo $field->label ?></strong>
      </label>
    <?php endif ?>
		<?php if ($field->help): ?>
    	<p class="help-block"><?php echo $field->help ?></p>
  	<?php endif ?>
	</div>
</div>