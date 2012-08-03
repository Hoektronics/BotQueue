<? if (User::isLoggedIn()): ?>
	<?= Controller::byName('upload')->renderView('uploader', array(
		'payload' => array(
			'type' => 'new_file'
		)
	)); ?><br/>
	
<? else: ?>
	<div class="BaseError">You must be logged in to upload things.</div>
<? endif ?>