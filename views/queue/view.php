<h2><?=$queue->getName()?></h2>

<h3>Job Queue</h3>
<?= Controller::byName('jobs')->renderView('draw_jobs', array('jobs' => $jobs))?>