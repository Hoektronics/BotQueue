<? if ($status): ?>
	<?= Controller::byName('htmltemplate')->renderView('statusbar', array('message' => $status))?>
<? else: ?>
	<? if ($error): ?>
		<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error))?>
	<? endif ?>

	<div>
		<?= $form->render() ?>
	</div>
<? endif ?>
