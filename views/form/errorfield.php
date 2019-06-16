<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
	  <div class="alert alert-error">
      <?php echo $field->getValue() ?>
    </div>
	</div>
</div>