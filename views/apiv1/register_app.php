<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? endif ?>

<form method="post" action="/api/v1/register">
	Application Name: <input type="text" name="name" value="">
	<input type="submit" name="submit" value="Register App">
</form>