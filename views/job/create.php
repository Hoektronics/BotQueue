<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="/job/create/file:<?=$file->id?>">
	 <input type="hidden" name="submit" value="1">
	 <input type="hidden" name="file_id" value="<?=$file->id?>">
	 <fieldset>

	   <div class="control-group <?=$errorfields['queue_id']?>">
	     <label class="control-label" for="iqueue"><strong>File</strong></label>
	     <div class="controls">
		      <?= $file->getLink() ?>
	       <p class="help-block">The file that will be printed.</p>
	     </div>
	   </div>

	   <div class="control-group <?=$errorfields['queue_id']?>">
	     <label class="control-label" for="iqueue"><strong>Queue</strong></label>
	     <div class="controls">
		      <?= Controller::byName('form')->renderView('selectfield', array('options' => $queues, 'name' => 'queue_id'))?>
					<? if ($errors['queue_id']): ?>
						<span class="help-inline"><?= $errors['queue_id'] ?></span>
					<? endif ?>
	       <p class="help-block">Which queue are you adding this job to?</p>
	     </div>
	   </div>

	   <div class="control-group <?=$errorfields['quantity']?>">
	     <label class="control-label" for="iquantity"><strong>Quantity</strong></label>
	     <div class="controls">
	       <input type="text" class="input-xlarge" id="iquantity" name="quantity" value="1">
					<? if ($errors['quantity']): ?>
						<span class="help-inline"><?= $errors['quantity'] ?></span>
					<? endif ?>
	       <p class="help-block">How many copies? Minimum 1, Maximum 100</p>
	     </div>
	   </div>

	   <div class="form-actions">
	     <button type="submit" class="btn btn-primary">Create new Job(s)</button>
	   </div>
		</fieldset>
	</form>
<? endif ?>