<? foreach($notifications as $row): ?>
	<? $notification = $row['Notification'] ?>
	<? $date = Utility::formatDate($notification->get('timestamp')) ?>
	<h1><?= $notification->get('title') ?></h1>
	<i>Posted <?=$date?></i>
	<hr>
	<p>
		<?= nl2br($notification->get('content')) ?>
	</p>
	<br>
<? endforeach ?>