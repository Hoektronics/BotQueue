<?
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var Form $form
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <p>
    <b>Please enter some details on the print failure, and determine what should be done with the bot and job.</b>
  </p>
  <?= $form->render() ?>
<? endif ?>