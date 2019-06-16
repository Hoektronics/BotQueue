<?php
	echo Controller::byName('browse')->renderView('pagination_info', array(
		'collection' => $queues,
		'word' => 'queue'
	));
?>
<?php echo Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues->getAll())); ?>
<?php
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $queues,
		'base_url' => '/queues'
	));
?>