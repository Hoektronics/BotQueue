<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?=$job->getUrl()?>/edit">
	 <input type="hidden" name="submit" value="1">
	 <fieldset>
	   <div class="control-group <?=$errorfields['queue_id']?>">
	     <label class="control-label" for="iqueue"><strong>Queue</strong></label>
	     <div class="controls">
		      <?= Controller::byName('form')->renderView('selectfield', array('options' => $queues, 'name' => 'queue_id', 'value' => $job->get('queue_id')))?>
					<? if ($errors['queue_id']): ?>
						<span class="help-inline"><?= $errors['queue_id'] ?></span>
					<? endif ?>
	       <p class="help-block">Which queue does this job belong to?</p>
	     </div>
	   </div>

	   <div class="form-actions">
	     <button type="submit" class="btn btn-primary">Edit Job</button>
	   </div>
		</fieldset>
	</form>
<? endif ?>