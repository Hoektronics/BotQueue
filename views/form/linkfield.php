<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
		<a title="<?php echo $field->label ?>" href="<?php echo $field->link ?>"><?php echo $field->getValue() ?></a>
	</div>
</div>