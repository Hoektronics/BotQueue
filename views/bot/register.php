<? if (!empty($errors)): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => "There were errors with your bot registration."))?>
<? endif ?>

<form class="form-horizontal" method="post" autocomplete="off" action="/bot/register">
 <input type="hidden" name="submit" value="1">
 <fieldset>
	
   <div class="control-group <?=$errorfields['name']?>">
     <label class="control-label" for="iname"><strong>Bot Name</strong></label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="iname" name="name" value="<?=$bot->get('name')?>">
				<? if ($errors['name']): ?>
					<span class="help-inline"><?= $errors['name'] ?></span>
				<? endif ?>
       <p class="help-block">What should humans call your bot? (required)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['queue']?>">
     <label class="control-label" for="iqueue"><strong>Queue</strong></label>
     <div class="controls">
	      <?= Controller::byName('form')->renderView('selectfield', array('options' => $queues, 'name' => 'queue_id'))?>
				<? if ($errors['queue_id']): ?>
					<span class="help-inline"><?= $errors['queue_id'] ?></span>
				<? endif ?>
       <p class="help-block">Which queue does this bot pull jobs from? (required)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['manufacturer']?>">
     <label class="control-label" for="iqueue">Manufacturer</label>
     <div class="controls">
       <? /* Controller::byName('form')->renderView('selectfield', array('name' => 'manufacturer', 'options' => array(
				'MakerBot',
				'Ultimaker',
				'Printrbot',
				'Self Made (DIY)',
				'Printrbot',
				'Other'
			))) */ ?>
      <input type="text" class="input-xlarge" id="imodel" name="manufacturer" value="<?=$bot->get('manufacturer')?>">
			<? if ($errors['manufacturer']): ?>
				<span class="help-inline"><?= $errors['manufacturer'] ?></span>
			<? endif ?>
      <p class="help-block">Which company (or person) built your bot? (optional)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['manufacturer']?>">
     <label class="control-label" for="iqueue">Model</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="imodel" name="model" value="<?=$bot->get('model')?>">
				<? if ($errors['model']): ?>
					<span class="help-inline"><?= $errors['model'] ?></span>
				<? endif ?>
       <p class="help-block">What is the model or name of your bot design? (optional)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['electronics']?>">
     <label class="control-label" for="iqueue">Electronics</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="ielectronics" name="electronics" value="<?=$bot->get('electronics')?>">
				<? if ($errors['electronics']): ?>
					<span class="help-inline"><?= $errors['electronics'] ?></span>
				<? endif ?>
       <p class="help-block">What electronics are you using to control your bot? (optional)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['firmware']?>">
     <label class="control-label" for="iqueue">Firmware</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="ifirmware" name="firmware" value="<?=$bot->get('firmware')?>">
				<? if ($errors['firmware']): ?>
					<span class="help-inline"><?= $errors['firmware'] ?></span>
				<? endif ?>
       <p class="help-block">What firmware are you running on your electronics (optional)</p>
     </div>
   </div>

   <div class="control-group <?=$errorfields['extruder']?>">
     <label class="control-label" for="iqueue">Extruder</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="iextruder" name="extruder" value="<?=$bot->get('extruder')?>">
				<? if ($errors['extruder']): ?>
					<span class="help-inline"><?= $errors['extruder'] ?></span>
				<? endif ?>
       <p class="help-block">What model of extruder is on your bot? (optional)</p>
     </div>
   </div>

   <div class="form-actions">
     <button type="submit" class="btn btn-primary">Register Bot</button>
   </div>
	</fieldset>
</form>