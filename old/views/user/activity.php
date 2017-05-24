<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>	
<?
		echo Controller::byName('browse')->renderView('pagination_info', array(
			'collection' => $activities,
			'word' => 'activity'
		));
	?>
	<?= Controller::byName('main')->renderView('draw_activities', array(
		'activities' => $activities->getAll(),
		'user' => $user
	)); ?>
	<?
		echo Controller::byName('browse')->renderView('pagination', array(
			'collection' => $activities,
			'base_url' => '/user:' . $user->id . '/activity'
		));
	?>
<? endif ?>
