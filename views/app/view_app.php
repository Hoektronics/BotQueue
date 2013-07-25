<? if ($megaerror): ?>
	<div class="MegaError"><?=$megaerror?></div>
<? else: ?>
	<div class="row">
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Application Name:</th>
						<td><?= $app->getName() ?></td>
					</tr>
					<tr>
						<th>Application URL:</th>
						<td><a href="<?=$app->get('app_url')?>"><?=$app->get('app_url')?></a></td>
					</tr>
					<tr>
						<th>Active:</th>
						<td><?= ($app->get('active') == 1) ? 'yes' : 'no'?></td>
					</tr>
          <? if ($app->canEdit()): ?>
  					<tr>
  						<th>API Key:</th>
  						<td><?=$app->get('consumer_key') ?></td>
  					</tr>
  					<tr>
  						<th>API Secret:</th>
  						<td><?=$app->get('consumer_secret') ?></td>
  					</tr>
  					<tr>
  						<th>Manage</th>
  						<td><a href="<?=$app->getUrl()?>/edit">Edit App</a> or <a href="<?=$app->getUrl()?>/delete">Delete App</a></td>
  					</tr>
  				<? endif ?>
				<tbody>
			</table>
		</div>
	</div>
<? endif ?>