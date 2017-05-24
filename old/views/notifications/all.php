<?= Controller::byName('notifications')->renderView('draw', array('notifications' => $notifications)) ?>
<? if (count($notifications) == 0): ?>
	<h1>No notifications</h1>
<? endif ?>