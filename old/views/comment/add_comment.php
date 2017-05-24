<?
/**
 * @package botqueue_comment
 * @var string $megaerror
 * @var Form $form
 */
?>
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<?= Controller::byName('comment')->renderView('draw_all', array('comments' => $comments)) ?>
	<?= $form->render('horizontal') ?>
<? endif ?>