<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
		<label class="control-label" for="<?php echo $field->id ?>"><strong><?php echo $field->label ?></strong></label>
	<? endif ?>
	<div class="controls">
		<? if (!empty($field->options)): ?>
			<select <?php echo $field->getAttributes() ?>>
				<? foreach ($field->options AS $key => $option): ?>
					<? if ($key == $field->getValue()): ?>
						<option value="<?php echo $key ?>" selected><?php echo $option ?></option>
					<? else: ?>
						<option value="<?php echo $key ?>"><?php echo $option ?></option>
					<? endif ?>
				<? endforeach ?>
			</select>
		<? else: ?>
			<span class="help-inline">Oops!  You need to pass in options to the SelectField constructor.</span>
		<? endif ?>
		<? if ($field->hasError): ?>
			<span class="help-inline"><?php echo $field->errorText ?></span>
		<? endif ?>
		<? if ($field->help): ?>
			<p class="help-block"><?php echo $field->help ?></p>
		<? endif ?>
	</div>
</div>