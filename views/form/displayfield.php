<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
  	<label class="control-label" for="<?=$field->id?>"><strong><?=$field->label?></strong></label>
  <? endif ?>
	<div class="controls">
    <?=$field->getValue()?>
		<? if ($field->help): ?>
    	<p class="help-block"><?=$field->help?></p>
  	<? endif ?>
	</div>
</div>