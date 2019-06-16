<?
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var int $page
 * @var int $per_page
 * @var int $total
 * @var array $jobs
 * @var string $status
 */
?>
<? if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<?
		echo Controller::byName('browse')->renderView('pagination_info', array(
			'collection' => $jobs,
			'word' => 'job'
		));
	?>
	<?php echo Controller::byName('job')->renderView('draw_jobs'.($status == JobState::Available ? '_available' : ''), array('jobs' => $jobs->getAll())); ?>
	<?
	echo Controller::byName('browse')->renderView('pagination', array(
		'collection' => $jobs,
		'base_url' => "/jobs/{$status}"
	));
?>
<? endif ?>
