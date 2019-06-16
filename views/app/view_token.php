<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthToken $token
 */
?>
<? if ($megaerror): ?>
	<div class="MegaError"><?php echo $megaerror ?></div>
<? else: ?>
	<div class="row">
		<div class="span9">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Application Name:</th>
					<td><?php echo $token->getName() ?></td>
				</tr>
				<tr>
					<th>Verified:</th>
					<td><?php echo $token->isVerified() ? 'yes' : 'no' ?></td>
				</tr>
				<tr>
					<th>Consumer:</th>
					<td><?php echo $token->getConsumer()->getLink() ?></td>
				</tr>
				<? if ($token->isMine()): ?>
					<tr>
						<th>API Key:</th>
						<td><?php echo $token->get('token') ?></td>
					</tr>
					<tr>
						<th>API Secret:</th>
						<td><?php echo $token->get('token_secret') ?></td>
					</tr>
					<tr>
						<th>Manage</th>
						<td><a href="<?php echo $token->getUrl() ?>/edit">Edit App</a> or <a
								href="<?php echo $token->getUrl() ?>/revoke">Revoke App</a></td>
					</tr>
				<? endif ?>
				<tbody>
			</table>
		</div>
		<div class="span3">
			<h3>Bots</h3>
			<? if (!empty($bots)): ?>
				<p>These bots are assigned to this app:</p>
				<ul>
					<? foreach ($bots AS $row): ?>
						<? $b = $row['Bot'] ?>
						<li><?php echo $b->getLink() ?></li>
					<? endforeach ?>
				</ul>
			<? else: ?>
				<p>No bots are assigned to this app.</p>
			<? endif ?>
		</div>
	</div>
<? endif ?>