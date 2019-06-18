<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<?php
		echo Controller::byName('browse')->renderView('pagination_info', array(
			'collection' => $jobs,
			'word' => 'job'
		));
	?>
	<?php echo Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs->getAll())); ?>
	<?php
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $jobs,
		'base_url' => $bot->getUrl() . "/jobs/{$status}"
	));
?>
<?php endif ?>