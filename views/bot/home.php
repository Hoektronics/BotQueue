<?
	echo Controller::byName('browse')->renderView('pagination_info', array(
		'collection' => $bots,
		'word' => 'bot'
	));
?>
<?php echo Controller::byName('bot')->renderView('draw_bots', array('bots' => $bots->getAll())) ?>
<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $bots,
		'base_url' => '/bots',
	));
?>