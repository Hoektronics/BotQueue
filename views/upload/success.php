<?php if (defined($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<!-- we shouldnt ever get here b/c of controller redirect. -->
	<div class="BaseStatus">Your file was uploaded successfully.</div>
<?php endif ?>