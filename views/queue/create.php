<? if (!empty($errors)): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => 'There were errors creating your queue.'))?>
<? endif ?>

<form class="form-horizontal" method="post" autocomplete="off" action="/queue/create">
 <input type="hidden" name="submit" value="1">
 <fieldset>
    <div class="control-group <?=$errorfields['name']?>">
      <label class="control-label" for="iname">Text input</label>
      <div class="controls">
        <input type="text" class="input-xlarge" id="iname" name="name" value="<?=$name?>">
				<? if ($errors['name']): ?>
					<span class="help-inline"><?= $errors['name'] ?></span>
				<? endif ?>
        <p class="help-block">To help you identify your queue.</p>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create Queue</button>
    </div>
	</fieldset>
</form>