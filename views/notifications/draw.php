<? foreach($notifications as $row): ?>
	<? $notification = $row['Notification'] ?>
	<? $date = Utility::formatDate($notification->get('timestamp')) ?>
	<h1><?php echo $notification->get('title') ?></h1>
	<i>Posted <?php echo $date ?></i>
	<hr>
	<p>
		<?php echo nl2br($notification->get('content')) ?>
	</p>
	<br>
<? endforeach ?>