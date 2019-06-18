<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
  Hmm.  Shouldn't get here.
<?php endif ?>