<div class="control-group <?= ($field->hasError) ? 'error' : '' ?>">
	<? if ($field->label): ?>
  	<label class="control-label" for="<?=$field->id?>"><strong><?=$field->label?></strong></label>
  <? endif ?>
	<div class="controls">
    <input type="text" class="input-xlarge" <?=$field->getAttributes()?> value="<?=htmlentities($field->getValue())?>">
		<? if ($field->hasError): ?>
			<span class="help-inline"><?= $field->errorText ?></span>
		<? endif ?>
		<? if ($field->help): ?>
    	<p class="help-block"><?=$field->help?></p>
  	<? endif ?>
	</div>
</div>