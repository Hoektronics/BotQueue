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
		'base_url' => "/jobs/{$status}"
	));
?>
<? endif ?>