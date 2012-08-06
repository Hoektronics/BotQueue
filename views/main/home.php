<h2>Queues</h2>
<?= Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues)); ?>


<h2>Bots</h2>
<?= Controller::byName('bot')->renderView('draw_bots', array('bots' => $bots)); ?>

<h2>Jobs</h2>
<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs)); ?>


<h2>Latest Activity</h2>

<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'page' => $page,
		'per_page' => $per_page,
		'base_url' => '/activity',
		'total' => $total
	));
?>
