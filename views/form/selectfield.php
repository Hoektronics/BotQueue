<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
		<label class="control-label" for="<?= $field->id ?>"><strong><?= $field->label ?></strong></label>
	<? endif ?>
	<div class="controls">
		<? if (!empty($field->options)): ?>
			<select <?= $field->getAttributes() ?>>
				<? foreach ($field->options AS $key => $option): ?>
					<? if ($key == $field->getValue()): ?>
						<option value="<?= $key ?>" selected><?= $option ?></option>
					<? else: ?>
						<option value="<?= $key ?>"><?= $option ?></option>
					<? endif ?>
				<? endforeach ?>
			</select>
		<? else: ?>
			<span class="help-inline">Oops!  You need to pass in options to the SelectField constructor.</span>
		<? endif ?>
		<? if ($field->hasError): ?>
			<span class="help-inline"><?= $field->errorText ?></span>
		<? endif ?>
		<? if ($field->help): ?>
			<p class="help-block"><?= $field->help ?></p>
		<? endif ?>
	</div>
</div>