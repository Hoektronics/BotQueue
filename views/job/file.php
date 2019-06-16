<?php if ($megaerror): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<div class="row">
		<div class="span6">
			<h3>File Details</h3>
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Download URL:</th>
					<td><a href="<?php echo $file->getDownloadURL() ?>"><?php echo $file->getName() ?></a></td>
				</tr>
				<?php if ($file->get('source_url')): ?>
					<tr>
						<th>Source:</th>
						<td><a href="<?php echo $file->get('source_url') ?>"><?php echo $file->get('source_url') ?></a></td>
					</tr>
				<?php endif ?>
				<?php if ($parent_file->isHydrated()): ?>
					<tr>
						<th>Parent File:</th>
						<td><?php echo $parent_file->getLink() ?></td>
					</tr>
				<?php endif ?>
				<tr>
					<th>Creator:</th>
					<td><?php echo $creator->getLink() ?></td>
				</tr>
				<tr>
					<th>Type:</th>
					<td><?php echo $file->get('type') ?></td>
				</tr>
				<tr>
					<th>Size:</th>
					<td><?php echo Utility::filesizeFormat($file->get('size')) ?></td>
				</tr>
				<tr>
					<th>MD5 Hash:</th>
					<td><?php echo $file->get('hash') ?></td>
				</tr>
				<tr>
					<th>Add Date:</th>
					<td><?php echo Utility::formatDateTime($file->get('add_date')) ?></td>
				</tr>
				<tr>
					<th>Manage:</th>
					<td><a class="btn btn-mini" href="/job/create/file:<?php echo $file->id ?>"><i class="icon-repeat"></i>
							re-run</a></td>
				</tr>
				</tbody>
			</table>
			<?php if (empty($kids)): ?>
				<h3>
					Jobs With This File
					:: 1-<?php echo min(10, $job_count) ?> of <?php echo $job_count ?> :: <a href="<?php echo $file->getUrl() ?>/jobs">see
						all</a>
				</h3>
				<?php echo Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $jobs)); ?>
			<?php endif ?>
		</div>
		<div class="span6">
			<?php if (!empty($kids)): ?>
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
					<?php foreach ($kids AS $row): ?>
						<?php $kid = $row['StorageInterface'] ?>
						<tr>
							<td><?php echo $kid->getLink() ?></td>
							<td><?php echo Utility::filesizeFormat($kid->get('size')) ?></td>
							<td><a class="btn btn-mini" href="/job/create/file:<?php echo $kid->id ?>"><i
										class="icon-repeat"></i> re-run</a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				<iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400"
				        src="<?php echo $file->getUrl() ?>/render"></iframe>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>