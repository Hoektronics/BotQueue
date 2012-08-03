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
