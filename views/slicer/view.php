<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span12">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Engine Name:</th>
						<td><?=$engine->getLink() ?></td>
					</tr>
					<tr>
						<th>Is Public:</th>
						<td><?=$engine->get('is_public') ? 'yes':'no' ?></td>
					</tr>
					<tr>
						<th>Is Featured:</th>
						<td><?=$engine->get('is_featured') ? 'yes':'no' ?></td>
					</tr>
					<tr>
						<th>Add Date:</th>
						<td><?=Utility::formatDateTime($engine->get('add_date'))?></td>
					</tr>
					<tr>
						<th>Engine Path:</th>
						<td><?=$engine->get('engine_path') ?></td>
					</tr>
					<tr>
						<th>Description:</th>
						<td><?=$engine->get('engine_description') ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<? if (User::isAdmin()): ?>
	  <h2>All User Configurations</h2>
	<? else: ?>
	  <h2>My Engine Configurations</h2>
	<? endif ?>
	<div class="row">
		<div class="span12">
			<table class="table table-striped table-bordered table-condensed">
        <thead>
          <tr>
            <th>Config Name</th>
            <? if (User::isAdmin()): ?>
              <th>User</th>
            <? endif ?>
            <th>Add Date</th>
            <th>Edit Date</th>
            <th>Manage</th>
          </tr>
        </thead>
				<tbody>
				  <?foreach ($configs AS $row): ?>
				    <?$config = $row['SliceConfig']?>
				    <tr>
				      <td><?=$config->getLink()?></td>
				      <? if (User::isAdmin()): ?>
  				      <td><?=$config->getUser()->getLink()?></td>
              <? endif ?>
              <td><?=Utility::formatDateTime($config->get('add_date'))?></td>
              <td><?=Utility::formatDateTime($config->get('edit_date'))?></td>
              <td>edit delete</td>
				    </tr>
				  <? endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
<? endif ?>