<?php
/**
 * @package botqueue_job
 * @var string $megaerror
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
  Hmm.  This shouldn't happen.
<?php endif ?>