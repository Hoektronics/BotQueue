<?
/**
 * @package botqueue_job
 * @var string $megaerror
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  Hmm.  This shouldn't happen.
<? endif ?>