<div class="control-group <?php echo ($field->hasError) ? 'error' : '' ?>">
	<div class="controls">
	  <div class="alert alert-warning">
      <?php echo $field->getValue() ?>
    </div>
	</div>
</div>