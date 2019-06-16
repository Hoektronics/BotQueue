<div id="signin" class="span6">
	<? if($error): ?>
		<?php echo $error ?>
	<? else: ?>
		<div class="title">Already a member? Sign in:</div>
		<?php echo $login_form->render() ?>
	<? endif ?>
</div>