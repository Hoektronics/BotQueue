<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
<?
		echo Controller::byName('browse')->renderView('pagination_info', array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => $total,
			'word' => 'activitie'
		));
	?>
	<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
	<?
		echo Controller::byName('browse')->renderView('pagination', array(
			'page' => $page,
			'per_page' => $per_page,
			'base_url' => '/user:' . $user->id . '/activity',
			'total' => $total
		));
	?>
<? endif ?>
