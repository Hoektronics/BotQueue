<? if (empty($bots)): ?>
  <div class="alert alert-success">
    <strong>It looks like you're new here!</strong>  Head over to the <a href="/help">help page</a> for information on getting up and running.  You're going to like it here.
  </div>
<? endif ?>
<? /* ?>
<? if (!empty($action_jobs)): ?>
  <div class="row">
		<div class="span12">
      <h2 class="text-error">Jobs Requiring Action:</h2>
  	  <table class="table table-striped table-bordered table-condensed">
        <tr>
          <th>Job</th>
          <th>Bot</th>
          <th>Do</th>
        </tr>
        <? foreach ($action_jobs AS $row): ?>
          <? $job = $row['Job']?>
          <? $bot = $row['Bot']?>
          <? $queue = $row['Queue']?>
          <tr>
            <td><?=$job->getLink()?></td>
            <td><?=$bot->getLink()?></td>
            <td>

            </td>
          </tr>
        <? endforeach ?>
      </table>
    </div>
  </div>
  <? endif ?>
  <? if (!empty($action_slicejobs)): ?>
  <div class="row">
  	<div class="span8">
      <h2 class="text-error">Slice Jobs Requiring Action:</h2>
      <table class="table table-striped table-bordered table-condensed">
        <tr>
          <th>Slice Job</th>
          <th>Input File</th>
          <th>Job</th>
          <th>Date</th>
          <th>Status</th>
          <th>Manage</th>
        </tr>
        <? foreach ($action_slicejobs AS $row): ?>
          <? $sj = $row['SliceJob']?>
          <? $file = $row['S3File']?>
          <? $job = $row['Job']?>
          <tr>
            <td><?=$sj->getLink()?></td>
            <td><?=$file->getLink()?></td>
            <td><?=$job->getLink()?></td>
            <td><?=Utility::formatDateTime($sj->get('finish_date'))?> <span class="muted">(<?=Utility::relativeTime($sj->get('finish_date'))?>)</span></td>
            <td><?=$sj->getStatusHTML()?></td>
            <td><a class="btn btn-success" href="<?=$sj->getUrl()?>">Take Action</a></td>
          </tr>
        <? endforeach ?>
      </table>
    </div>
  </div>
<? endif ?>
<? /*?>
  <? /* ?>
	<div class="span6">
		<h3>
			Latest Jobs
			:: 1-<?=min(10, $job_count)?> of <?=$job_count?> :: <a href="/jobs">see all</a>
		</h3>
		<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $jobs)); ?>
	</div>
	*/
	 ?>
<div class="row">
	<div class="span12">
		<h3>
			Bot Activity Dashboard
			<a class="btn btn-primary" href="/upload">New Job</a>
	    <a class="btn btn-primary" href="/bot/register">New Bot</a>
	    <a class="btn btn-primary" href="/queue/create">New Queue</a>
		</h3>
   <span class="muted">Auto refresh every 10s - Last Update <?=date("Y-m-d H:i:s")?></span>
		<? if (!empty($bots)): ?>
    	<table class="table table-striped table-bordered table-condensed">
    		<thead>
    			<tr>
    				<th>Name</th>
    				<th>Bot Status</th>
    				<th>Last Seen</th>
    				<th>Job</th>
    				<th>Temps</th>
    				<th>Status</th>
    				<th>Elapsed</th>
    				<th>ETA</th>
    				<th colspan="2">Progress</th>
    			</tr>
    		</thead>
    		<tbody>
    			<? foreach ($bots AS $row): ?>
    				<? $b = $row['Bot'] ?>
    				<? $q = $row['Queue'] ?>
    				<? $j = $row['Job'] ?>
    				<? $sj = $j->getSliceJob() ?>
    				<tr>
    					<td><?=$b->getLink()?></td>
    					<td><?=$b->getStatusHTML()?></td>
    					<td class="muted"><?=$b->getLastSeenHTML()?></td>
    					<? if ($j->isHydrated()): ?>
    						<td><?=$j->getLink()?></td>
                <? $temps = JSON::decode($b->get('temperature_data')) ?>
    						<? if ($b->get('status') == 'working' && $temps != NULL): ?>
    						  <td>
    						    E: <?=$temps->extruder?>C<br/>
    						    B: <?=$temps->bed?>C
    						  </td>
    						<? else: ?>
    						  <td class="muted">n/a <?print_r($temps)?></td>
    						<? endif ?>
    					  <td><?=$j->getStatusHTML()?></td>
    					  <td class="muted"><?=$j->getElapsedText()?></td>
      					<td class="muted"><?=$j->getEstimatedText()?></td>
    					  <td style="width:250px">
    					    <? if ($j->get('status') == 'qa'): ?>
                    <a class="btn btn-primary" href="<?=$j->getUrl()?>/qa">VERIFY JOB</a>
                  <? elseif ($j->get('status') == 'slicing' && $sj->get('status') == 'pending'): ?>
                    <a class="btn btn-primary" href="<?=$sj->getUrl()?>">VIEW</a>
                    <a class="btn btn-success" href="<?=$sj->getUrl()?>/pass">PASS</a>
                    <a class="btn btn-danger" href="<?=$sj->getUrl()?>/fail">FAIL</a>
                  <? else: ?>
        						<div class="progress progress-striped active" style="width: 250px">
        						  <div class="bar" style="width: <?=round($j->get('progress'))?>%;"></div>
        						</div>
        					<? endif ?>
      					</td>
      					<td class="muted">
                  <?= round($j->get('progress'), 2) ?>%      					  
    					  </td>
    					<? elseif ($b->get('status') == 'error'): ?>
  						  <td colspan="7" class="muted"><span class="text-error"><?=$b->get('error_text')?></span></td>
    					<? else: ?>
    						<td colspan="7" class="muted">&nbsp;</td>
    					<? endif ?>
    				</tr>
    			<?endforeach?>
    		</tbody>
    	</table>
    <? else: ?>
      <div class="alert">
        <strong>No bots found!</strong>  To get started, <a href="/bot/register">register a bot</a>.
      </div>
    <? endif ?>
	</div>
</div>
<div class="row">
	<div class="span6">
		<h3>
			On Deck Jobs
			:: 1-<?=min(5, $on_deck_count)?> of <?=$on_deck_count?> :: <a href="/jobs/available">see all</a>
		</h3>
		<?= Controller::byName('job')->renderView('draw_on_deck_jobs', array('jobs' => $on_deck)); ?>
	</div>
	<div class="span6">
		<h3>
			Finished Jobs
			:: 1-<?=min(5, $finished_count)?> of <?=$finished_count?> :: <a href="/jobs/complete">see all</a>
		</h3>
		<?= Controller::byName('job')->renderView('draw_finished_jobs', array('jobs' => $finished)); ?>
	</div>
</div>

<? /* ?>
<div class="row">
	<div class="span6">
		<h3>
			My Queues
			 :: 1-<?=min(10, $queue_count)?> of <?=$queue_count?> :: <a href="/queues">see all</a>
		</h3>
		<?= Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues)); ?>
	</div>
	<div class="span6">
		<h3>
			Latest Activity
			 :: 1-<?=min(10, $activity_count)?> of <?=$activity_count?> :: <a href="/activity">see all</a>
		</h3>
		<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
	</div>
</div>
<? if (!empty($errors)): ?>
	<div class="row">
	  <div class="span12">
  	  <h3>Error Log</h3>
	    <?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'user'))?>
	  </div>
	</div>
<? endif ?>
<? */ ?>