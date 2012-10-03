<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span12">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
          <tr>
            <th>Manage:</th>
    	      <td>
  	        	<a class="btn btn-mini" href="<?=$config->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
							<a class="btn btn-mini" href="<?=$config->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
    	      </td>
    	    </tr>
					<tr>
						<th>Config Name:</th>
						<td><?=$config->getLink() ?></td>
					</tr>
					<tr>
						<th>Slice Engine Name:</th>
						<td><?=$engine->getLink() ?></td>
					</tr>
					<? if (User::isAdmin()):?>
  					<tr>
  						<th>User:</th>
  						<td><?=$user->getLink() ?></td>
  					</tr>
  				<? endif ?>
					<tr>
						<th>Add Date:</th>
						<td><?=Utility::formatDateTime($config->get('add_date'))?></td>
					</tr>
					<tr>
						<th>Edit Date:</th>
						<td><?=Utility::formatDateTime($config->get('edit_date'))?></td>
					</tr>
					<tr>
						<th>Config Data:</th>
						<td><?=nl2br(Utility::sanitize($config->get('config_data'))) ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="span6">
      <h3>Slice Jobs</h3>
      <?=Controller::byName('slicer')->renderView('draw_jobs_small', array('jobs' => $jobs))?>
      
		</div>
		<div class="span6">
      <h3>Bots</h3>
      <?=Controller::byName('bot')->renderView('draw_bots_small', array('bots' => $bots))?>
		</div>
	</div>
<? endif ?>