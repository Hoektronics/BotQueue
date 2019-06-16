<?php if (count($request_tokens)): ?>
	<div class="alert alert-info">
		<div class="row">
			<div class="span3" style="margin-top: 8px; margin-bottom: 8px;">
				<strong><?php echo count($request_tokens) ?> app<?php echo (count($request_tokens) > 1) ? 's' : ' ' ?> on your local
					network <?php echo (count($request_tokens) > 1) ? 'are' : 'is' ?> requesting access:</strong>
			</div>
			<div class="span8">
				<div class="row">
					<?php foreach ($request_tokens AS $row): ?>
						<?php $token = $row['OAuthToken'] ?>
						<?php $app = $row['OAuthConsumer'] ?>
						<div class="span4" style="margin-top: 8px; margin-bottom: 8px;">
							<?php echo $app->getLink() ?> requested access
							on <?php echo Utility::formatDateTime($token->get('last_seen')) ?>
							<a href="/app/authorize?oauth_token=<?php echo $token->get('token') ?>"
							   class="btn btn-primary btn-mini">view</a>
							<a href="<?php echo $token->getUrl() ?>/revoke" class="btn btn-danger btn-mini">deny</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif ?>

<div class="row">
	<div id="dashtronView" class="span12"></div>
</div>
<div class="row">
	<div class="span6">
		<div id="onDeckJobs"></div>
	</div>
	<div class="span6">
		<div id="finishedJobs"></div>
	</div>
</div>