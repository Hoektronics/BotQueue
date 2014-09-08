<div id="signin" class="span6">
	<? if($error): ?>
		<?= $error ?>
	<? else: ?>
		<div class="title">Already a member? Sign in:</div>
		<?= $login_form->render() ?>
	<? endif ?>
</div>