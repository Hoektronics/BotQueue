<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<div class="row">
		<div class="span6">
			<h3>File Details</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Download URL:</th>
					<td><a href="<?= $file->getDownloadURL() ?>"><?= $file->getName() ?></a></td>
				</tr>
				<? if ($file->get('source_url')): ?>
					<tr>
						<th>Source:</th>
						<td><a href="<?= $file->get('source_url') ?>"><?= $file->get('source_url') ?></a></td>
					</tr>
				<? endif ?>
				<? if ($parent_file->isHydrated()): ?>
					<tr>
						<th>Parent File:</th>
						<td><?= $parent_file->getLink() ?></td>
					</tr>
				<? endif ?>
				<tr>
					<th>Creator:</th>
					<td><?= $creator->getLink() ?></td>
				</tr>
				<tr>
					<th>Type:</th>
					<td><?= $file->get('type') ?></td>
				</tr>
				<tr>
					<th>Size:</th>
					<td><?= Utility::filesizeFormat($file->get('size')) ?></td>
				</tr>
				<tr>
					<th>MD5 Hash:</th>
					<td><?= $file->get('hash') ?></td>
				</tr>
				<tr>
					<th>Add Date:</th>
					<td><?= Utility::formatDateTime($file->get('add_date')) ?></td>
				</tr>
				<tr>
					<th>Manage:</th>
					<td><a class="btn btn-mini" href="/job/create/file:<?= $file->id ?>"><i class="icon-repeat"></i>
							re-run</a></td>
				</tr>
				</tbody>
			</table>
			<? if (empty($kids)): ?>
				<h3>
					Jobs With This File
					:: 1-<?= min(10, $job_count) ?> of <?= $job_count ?> :: <a href="<?= $file->getUrl() ?>/jobs">see
						all</a>
				</h3>
				<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $jobs)); ?>
			<? endif ?>
		</div>
		<div class="span6">
			<? if (!empty($kids)): ?>
				<h3>Contained Files</h3>
				<table class="table table-striped table-bordered table-condensed">
					<thead>
					<tr>
						<th>File</th>
						<th>Size</th>
						<th>Manage</th>
					</tr>
					</thead>
					<tbody>
					<? foreach ($kids AS $row): ?>
						<? $kid = $row['StorageInterface'] ?>
						<tr>
							<td><?= $kid->getLink() ?></td>
							<td><?= Utility::filesizeFormat($kid->get('size')) ?></td>
							<td><a class="btn btn-mini" href="/job/create/file:<?= $kid->id ?>"><i
										class="icon-repeat"></i> re-run</a></td>
						</tr>
					<? endforeach ?>
					</tbody>
				</table>
			<? else: ?>
				<iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400"
				        src="<?= $file->getUrl() ?>/render"></iframe>
			<? endif ?>
		</div>
	</div>
<? endif ?>