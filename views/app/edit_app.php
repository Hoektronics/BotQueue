<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<? if ($error): ?>
		<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error))?>
	<? endif ?>
	<form class="form-horizontal" method="post" autocomplete="off" action="<?=$app->getUrl()?>/edit">
	 <input type="hidden" name="submit" value="1">
	 <fieldset>
	   <div class="control-group <?=$errorfields['name']?>">
	     <label class="control-label" for="iname">App Name</label>
	     <div class="controls">
	       <input type="text" class="input-xlarge" id="iname" name="name" value="<?=$app->get('name')?>">
					<? if ($errors['name']): ?>
						<span class="help-inline"><?= $errors['name'] ?></span>
					<? endif ?>
	       <p class="help-block">What do you call your app?</p>
	     </div>
	   </div>
	   <div class="control-group <?=$errorfields['app_url']?>">
	     <label class="control-label" for="iapp_url">App URL / Website</label>
	     <div class="controls">
	       <input type="text" class="input-xlarge" id="iapp_url" name="app_url" value="<?=$app->get('app_url')?>">
					<? if ($errors['app_url']): ?>
						<span class="help-inline"><?= $errors['app_url'] ?></span>
					<? endif ?>
	       <p class="help-block">Homepage with more information about your app.</p>
	     </div>
	   </div>
	   <div class="form-actions">
	     <button type="submit" class="btn btn-primary">Save Changes</button>
	   </div>
	</form>
<? endif ?>