<?php
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var Form $form
 */
?>
<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
  <p>
    <b>Please enter some details on the print failure, and determine what should be done with the bot and job.</b>
  </p>
  <?php echo $form->render() ?>
<?php endif ?>