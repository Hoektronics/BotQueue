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
	<?= $form->render('horizontal') ?>
<? endif ?>