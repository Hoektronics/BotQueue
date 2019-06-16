<div id="signin" class="span6">
	<?php if($error): ?>
		<?php echo $error ?>
	<?php else: ?>
		<div class="title">Already a member? Sign in:</div>
		<?php echo $login_form->render() ?>
	<?php endif ?>
</div>