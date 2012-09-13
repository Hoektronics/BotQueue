<div class="control-group">
	<div class="controls">
	  <? if ($field->label): ?>
    	<label class="checkbox">
    	  <input type="checkbox" id="<?=$field->id?>" name="<?=$field->name?>" <?= ($field->getValue()) ? 'checked="true"' : '' ?> value="1">
    	  <strong><?=$field->label?></strong>
      </label>
    <? endif ?>
		<? if ($field->help): ?>
    	<p class="help-block"><?=$field->help?></p>
  	<? endif ?>
	</div>
</div>