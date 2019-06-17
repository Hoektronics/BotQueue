<?php echo Controller::byName('notifications')->renderView('draw', array('notifications' => $notifications)) ?>
<?php if (count($notifications) == 0): ?>
	<h1>No new notifications</h1>
<?php endif ?>

<a href="/notifications/all">Click here</a> to see past notifications.