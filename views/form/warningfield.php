<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
	  <div class="alert alert-warning">
      <?=$field->getValue()?>
    </div>
	</div>
</div>