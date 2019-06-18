<?php
/**
 * @package botqueue_comment
 * @var string $megaerror
 * @var Form $form
 */
?>
<?php if (defined($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<?php echo Controller::byName('comment')->renderView('draw_all', array('comments' => $comments)) ?>
	<?php echo $form->render('horizontal') ?>
<?php endif ?>