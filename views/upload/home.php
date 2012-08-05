<? if (User::isLoggedIn()): ?>

	<?= Controller::byName('upload')->renderView('uploader', array(
		'payload' => array(
			'type' => 'new_file',
			'queue_id' => $queue->id
		)
	)); ?><br/>
	
<? else: ?>
	<div class="BaseError">You must be logged in to upload things.</div>
<? endif ?>