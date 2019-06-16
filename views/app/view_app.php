<?
/**
 * @package botqueue_app
 * @var string $megaerror
 * @var OAuthConsumer $consumer
 * @var OAuthToken $app
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
					<td><?php echo $consumer->getName() ?></td>
				</tr>
				<tr>
					<th>Application URL:</th>
					<td><a href="<?php echo $consumer->get('app_url') ?>"><?php echo $consumer->get('app_url') ?></a></td>
				</tr>
				<tr>
					<th>Active:</th>
					<td><?php echo ($consumer->get('active') == 1) ? 'yes' : 'no' ?></td>
				</tr>
				<? if ($consumer->canEdit()): ?>
					<tr>
						<th>API Key:</th>
						<td><?php echo $consumer->get('consumer_key') ?></td>
					</tr>
					<tr>
						<th>API Secret:</th>
						<td><?php echo $consumer->get('consumer_secret') ?></td>
					</tr>
					<tr>
						<th>Manage</th>
						<td><a href="<?php echo $consumer->getUrl() ?>/edit">Edit App</a> or <a href="<?php echo $consumer->getUrl() ?>/delete">Delete
								App</a></td>
					</tr>
				<? endif ?>
				<tbody>
			</table>
		</div>
		<div class="span3">
			<h3>Apps</h3>
			<? if(!empty($apps)): ?>
				<p>These apps are using this consumer:</p>
				<ul>
					<? foreach ($apps AS $row): ?>
						<? $app = $row['OAuthToken'] ?>
						<li><?php echo $app->getLink() ?></li>
					<? endforeach ?>
				</ul>
			<? else: ?>
				<p>No apps are using this consumer.</p>
			<? endif ?>
		</div>
	</div>
<? endif ?>