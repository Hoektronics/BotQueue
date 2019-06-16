<?php
	echo Controller::byName('browse')->renderView('pagination_info', array(
		'collection' => $activities,
		'word' => 'activity'
	));
?>

<?php echo Controller::byName('main')->renderView('draw_activities', array(
	'activities' => $activities->getAll(),
	'user' => $user
)); ?>

<?php
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $activities,
		'base_url' => '/activity'
	));
?>
