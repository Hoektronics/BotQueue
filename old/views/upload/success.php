<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<!-- we shouldnt ever get here b/c of controller redirect. -->
	<div class="BaseStatus">Your file was uploaded successfully.</div>
<? endif ?>