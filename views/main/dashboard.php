<? if (empty($bots)): ?>
  <div class="alert alert-success">
    <strong>It looks like you're new here!</strong>  Head over to the <a href="/help">help page</a> for information on getting up and running.  You're going to like it here.
  </div>
<? endif ?>
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
      <?= Controller::byName('main')->renderView('dashboard_large_thumbnails', array('bots' => $bots)) ?>
      <? //Controller::byName('main')->renderView('dashboard_medium_thumbnails', array('bots' => $bots)) ?>
      <? //Controller::byName('main')->renderView('dashboard_small_thumbnails', array('bots' => $bots)) ?>
      <? //Controller::byName('main')->renderView('dashboard_list', array('bots' => $bots)) ?>
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