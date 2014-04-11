<? if ($status): ?>
	<?= Controller::byName('htmltemplate')->renderView('statusbar', array('message' => $status))?>
<? else: ?>
	<? if ($error): ?>
		<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $error))?>
	<? endif ?>

	Enter your email below and we'll send you an email with your username and a link to reset your password.

	<div>
		<?= $form->render() ?>
	</div>
<? endif ?>
