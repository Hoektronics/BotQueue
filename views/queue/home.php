<?
	echo Controller::byName('browse')->renderView('pagination_info', array(
		'page' => $page,
		'per_page' => $per_page,
		'total' => $total,
		'word' => 'queue'
	));
?>
<?= Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues)); ?>
<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'page' => $page,
		'per_page' => $per_page,
		'total' => $total,
		'base_url' => '/queues',
	));
?>