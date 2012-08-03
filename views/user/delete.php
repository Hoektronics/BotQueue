<? if ($megaerror): ?>
	<div class="BaseError"><?=$megaerror?></div>
<? elseif ($status): ?>
	<div class="BaseStatus"><?=$status?></div>
<? else: ?>
	<form method="post" action="/user:<?=$user->id?>/delete">
		<p>Are you sure you want to delete <?=$user->getLink()?>?</p>
		<p>This is permanent.  We will delete all data including files and activities.  Are you sure you want to do this?</p>
		<input type="submit" name="submit" value="Yes, delete it!"/>
	</form>
<? endif ?>
