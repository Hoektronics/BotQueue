<?
/**
 * @package botqueue_app
 * @var array $errorfields
 * @var array $errors
 * @var string $name
 * @var string $app_url
 */
?>
<form class="form-horizontal" method="post" autocomplete="off" action="/app/register">
 <input type="hidden" name="submit" value="1">
 <fieldset>
   <div class="control-group <?=$errorfields['name']?>">
     <label class="control-label" for="iname">App Name</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="iname" name="name" value="<?=$name?>">
				<? if ($errors['name']): ?>
					<span class="help-inline"><?= $errors['name'] ?></span>
				<? endif ?>
       <p class="help-block">What do you call your app?</p>
     </div>
   </div>
   <div class="control-group <?=$errorfields['app_url']?>">
     <label class="control-label" for="iapp_url">App URL / Website</label>
     <div class="controls">
       <input type="text" class="input-xlarge" id="iapp_url" name="app_url" value="<?=$app_url?>">
				<? if ($errors['app_url']): ?>
					<span class="help-inline"><?= $errors['app_url'] ?></span>
				<? endif ?>
       <p class="help-block">Homepage with more information about your app.</p>
     </div>
   </div>
   <div class="form-actions">
     <button type="submit" class="btn btn-primary">Register App</button>
   </div>
</form>