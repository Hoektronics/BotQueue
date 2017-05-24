<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
		<a title="<?= $field->label ?>" href="<?= $field->link ?>"><?=$field->getValue() ?></a>
	</div>
</div>