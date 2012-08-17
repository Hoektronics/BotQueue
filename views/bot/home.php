<?
	echo Controller::byName('browse')->renderView('pagination_info', array(
		'page' => $page,
		'per_page' => $per_page,
		'total' => $total,
		'word' => 'bot'
	));
?>
<?= Controller::byName('bot')->renderView('draw_bots', array('bots' => $bots)) ?>
<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'page' => $page,
		'per_page' => $per_page,
		'total' => $total,
		'base_url' => '/bots',
	));
?>