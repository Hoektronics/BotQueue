<?php foreach($notifications as $row): ?>
	<?php $notification = $row['Notification'] ?>
	<?php $date = Utility::formatDate($notification->get('timestamp')) ?>
	<h1><?php echo $notification->get('title') ?></h1>
	<i>Posted <?php echo $date ?></i>
	<hr>
	<p>
		<?php echo nl2br($notification->get('content')) ?>
	</p>
	<br>
<?php endforeach; ?>