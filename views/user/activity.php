<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>	
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
			'base_url' => '/user:' . $user->id . '/activity'
		));
	?>
<?php endif ?>
