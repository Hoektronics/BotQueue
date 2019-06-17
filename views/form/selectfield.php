<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<?php if ($field->label): ?>
		<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
	<?php endif ?>
	<div class="controls">
		<?php if (!empty($field->options)): ?>
			<select <?php echo $field->getAttributes() ?>>
				<?php foreach ($field->options AS $key => $option): ?>
					<?php if ($key == $field->getValue()): ?>
						<option value="<?php echo $key ?>" selected><?php echo $option ?></option>
					<?php else: ?>
						<option value="<?php echo $key ?>"><?php echo $option ?></option>
					<?php endif ?>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<span class="help-inline">Oops!  You need to pass in options to the SelectField constructor.</span>
		<?php endif ?>
		<?php if ($field->hasError): ?>
			<span class="help-inline"><?php echo $field->errorText ?></span>
		<?php endif ?>
		<?php if ($field->help): ?>
			<p class="help-block"><?php echo $field->help ?></p>
		<?php endif ?>
	</div>
</div>