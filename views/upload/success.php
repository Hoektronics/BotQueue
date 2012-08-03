<? if ($megaerror): ?>
	<div class="BaseError"><?=$megaerror?></div>
<? else: ?>
	<!-- we shouldnt ever get here... -->
	<div class="BaseStatus">Your file was uploaded successfully.</div>
<? endif ?>