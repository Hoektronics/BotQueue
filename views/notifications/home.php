<?php echo Controller::byName('notifications')->renderView('draw', array('notifications' => $notifications)) ?>
<? if (count($notifications) == 0): ?>
	<h1>No new notifications</h1>
<? endif ?>

<a href="/notifications/all">Click here</a> to see past notifications.