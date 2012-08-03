<a href="/queue/create">Create Queue</a>
<a href="/bot/register">Register Bot</a>
<a href="/job/add">Add a Job</a>

<h2>Queues</h2>

<h2>Bots</h2>

<h2>Jobs</h2>

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
