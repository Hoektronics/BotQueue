<? if (empty($bots)): ?>
  <div class="alert alert-success">
    <strong>It looks like you're new here!</strong>  Head over to the <a href="/help">help page</a> for information on getting up and running.  You're going to like it here.
  </div>
<? endif ?>
<!-- >
<div class="row">
	<div class="span12">
    <a class="btn btn-primary btn-mini" href="/upload">New Job</a>
    <a class="btn btn-primary btn-mini" href="/bot/register">New Bot</a>
    <a class="btn btn-primary btn-mini" href="/queue/create">New Queue</a>
    <span class="muted">Auto refresh every 10s - Last Update <?=date("Y-m-d H:i:s")?></span>
  </div>
</div>
-->

<? if ($request_tokens_count): ?>
  <div class="alert alert-info">
    <strong><?=$request_tokens_count?> app<?=($request_tokens_count>1)? 's are' : ' is'?> requesting access!</strong>  Head over to the <a href="/apps">apps page</a> to approve or deny this request.
  </div>
<? endif ?>

<div class="row">
	<div class="span12">
		<? if (!empty($bots)): ?>
      <?= Controller::byName('main')->renderView('dashboard_' . $dashboard_style, array('bots' => $bots)) ?>
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