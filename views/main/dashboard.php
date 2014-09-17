<? if (empty($bots)): ?>
	<div class="alert alert-success">
		<strong>It looks like you're new here!</strong> Head over to the <a href="/help">help page</a> for information
		on getting up and running. You're going to like it here.
	</div>
<? endif ?>

<? if (count($request_tokens)): ?>
	<div class="alert alert-info">
		<div class="row">
			<div class="span3" style="margin-top: 8px; margin-bottom: 8px;">
				<strong><?= count($request_tokens) ?> app<?= (count($request_tokens) > 1) ? 's' : ' ' ?> on your local
					network <?= (count($request_tokens) > 1) ? 'are' : 'is' ?> requesting access:</strong>
			</div>
			<div class="span8">
				<div class="row">
					<? foreach ($request_tokens AS $row): ?>
						<? $token = $row['OAuthToken'] ?>
						<? $app = $row['OAuthConsumer'] ?>
						<div class="span4" style="margin-top: 8px; margin-bottom: 8px;">
							<?= $app->getLink() ?> requested access
							on <?= Utility::formatDateTime($token->get('last_seen')) ?>
							<a href="/app/authorize?oauth_token=<?= $token->get('token') ?>"
							   class="btn btn-primary btn-mini">view</a>
							<a href="<?= $token->getUrl() ?>/revoke" class="btn btn-danger btn-mini">deny</a>
						</div>
					<? endforeach ?>
				</div>
			</div>
		</div>
	</div>
<? endif ?>

<div class="row">
	<div id="dashtronView" class="span12"></div>
</div>
<div class="row">
	<div class="span6">
		<h3>
			On Deck Jobs
			:: 1-<?= min(5, $on_deck_count) ?> of <?= $on_deck_count ?> :: <a href="/jobs/available">see all</a>
		</h3>
		<?= Controller::byName('job')->renderView('draw_on_deck_jobs', array('jobs' => $on_deck)); ?>
	</div>
	<div class="span6">
		<h3>
			Finished Jobs
			:: 1-<?= min(5, $finished_count) ?> of <?= $finished_count ?> :: <a href="/jobs/complete">see all</a>
		</h3>
		<?= Controller::byName('job')->renderView('draw_finished_jobs', array('jobs' => $finished)); ?>
	</div>
</div>