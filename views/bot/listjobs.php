<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<?
		echo Controller::byName('browse')->renderView('pagination_info', array(
			'collection' => $jobs,
			'word' => 'job'
		));
	?>
	<?= Controller::byName('job')->renderView('draw_jobs', array('jobs' => $jobs->getAll())); ?>
	<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $jobs,
		'base_url' => $bot->getUrl() . "/jobs/{$status}"
	));
?>
<? endif ?>