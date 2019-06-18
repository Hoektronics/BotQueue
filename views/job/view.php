<?php
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var int $comment_count
 * @var array $errors
 * @var StorageInterface $webcam
 * @var Job $job
 * @var StorageInterface $parent_file
 * @var StorageInterface $source_file
 * @var StorageInterface $gcode_file
 * @var SliceJob $slicejob
 * @var SliceEngine $sliceengine
 * @var SliceConfig $sliceconfig
 * @var Queue $queue
 * @var Bot $bot
 * @var User $creator
 */
?>
<?php if (defined($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
	<ul id="myTab" class="nav nav-tabs">
		<li class="active"><a href="#details" data-toggle="tab">Details</a></li>
		<li><a href="#temperature" id="temperatureTab" data-toggle="tab">Temperature Log</a></li>
		<li><a href="#files" data-toggle="tab">Files</a></li>
		<li><a href="#comments" data-toggle="tab">Comments<?php if ($comment_count > 0): ?> <span
					class="badge badge-info"><?php echo $comment_count ?></span><?php endif ?></a></li>
		<li><a href="#errors" data-toggle="tab">Error Log<?php if (count($errors) > 0): ?> <span
					class="badge badge-important"><?php echo count($errors) ?></span><?php endif ?></a></li>
	</ul>

	<div id="myTabContent" class="tab-content">
		<div class="row tab-pane fade in active" id="details">
			<div class="span6">
				<?php if ($webcam->isHydrated()): ?>
					<img src="<?php echo $webcam->getDownloadURL() ?>">
				<?php else: ?>
					<img src="/img/colorbars.gif">
				<?php endif ?>
			</div>
			<div class="span6">
				<table class="table table-striped table-bordered table-condensed">
					<tbody>
					<tr>
						<th>Manage:</th>
						<td>
							<?php if ($job->get('status') == 'available'): ?>
								<a class="btn btn-mini" href="<?php echo $job->getUrl() ?>/bump"><i class="icon-arrow-up"></i>
									bump</a>
								<a class="btn btn-mini" href="<?php echo $job->getUrl() ?>/edit"><i class="icon-cog"></i> edit</a>
								<a class="btn btn-mini" href="<?php echo $job->getUrl() ?>/cancel"><i class="icon-eject"></i>
									cancel</a>
							<?php endif ?>
							<?php if ($job->get('status') == 'qa'): ?>
								<a class="btn btn-mini" href="<?php echo $job->getUrl() ?>/qa"><i class="icon-check"></i>
									verify</a>
							<?php endif ?>
							<?php if ($job->get('status') != 'taken' && $job->get('status') != 'qa' && $job->get('status') != 'slicing'): ?>
								<a class="btn btn-mini" href="<?php echo $job->getUrl() ?>/delete"><i class="icon-remove"></i>
									delete</a>
							<?php endif ?>
							<a class="btn btn-mini" href="/job/create/job:<?php echo $job->id ?>"><i class="icon-repeat"></i>
								re-run</a>
						</td>
					</tr>
					<tr>
						<th>Status:</th>
						<td><?php echo JobStatus::getStatusHTML($job) ?></td>
					</tr>
					<tr>
						<th>Progress:</th>
						<td>
							<div class="progress progress-striped">
								<div class="bar" style="width: <?php echo round($job->get('progress')) ?>%;"></div>
							</div>
						</td>
					</tr>
					<tr>
						<th>Created:</th>
						<td><?php echo Utility::formatDateTime($job->get('created_time')) ?> <span
								class="muted">(<?php echo Utility::relativeTime($job->get('created_time')) ?>)</span></td>
					</tr>
					<tr>
						<th>Grabbed:</th>
						<?php if (strtotime($job->get('taken_time')) > 0): ?>
							<td><?php echo Utility::formatDateTime($job->get('taken_time')) ?> <span
									class="muted">(<?php echo Utility::relativeTime($job->get('taken_time')) ?>)</span></td>
						<?php else: ?>
							<td><span class="muted">n/a</span></td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Sliced:</th>
						<?php if (strtotime($job->get('slice_complete_time')) > 0): ?>
							<td><?php echo Utility::formatDateTime($job->get('slice_complete_time')) ?> <span
									class="muted">(<?php echo Utility::relativeTime($job->get('slice_complete_time')) ?>
									)</span>
							</td>
						<?php else: ?>
							<td><span class="muted">n/a</span></td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Downloaded:</th>
						<?php if (strtotime($job->get('downloaded_time')) > 0): ?>
							<td><?php echo Utility::formatDateTime($job->get('downloaded_time')) ?> <span
									class="muted">(<?php echo Utility::relativeTime($job->get('downloaded_time')) ?>)</span>
							</td>
						<?php else: ?>
							<td><span class="muted">n/a</span></td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Print Complete:</th>
						<?php if (strtotime($job->get('finished_time')) > 0): ?>
							<td><?php echo Utility::formatDateTime($job->get('finished_time')) ?> <span
									class="muted">(<?php echo Utility::relativeTime($job->get('finished_time')) ?>)</span></td>
						<?php else: ?>
							<td><span class="muted">n/a</span></td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Finished:</th>
						<?php if (strtotime($job->get('verified_time')) > 0): ?>
							<td><?php echo Utility::formatDateTime($job->get('verified_time')) ?> <span
									class="muted">(<?php echo Utility::relativeTime($job->get('verified_time')) ?>)</span></td>
						<?php else: ?>
							<td><span class="muted">n/a</span></td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Elapsed:</th>
						<td><?php echo $job->getElapsedText() ?></td>
					</tr>
					<?php if ($job->get('status') == 'taken'): ?>
						<tr>
							<th>Remaining:</th>
							<td><?php echo $job->getEstimatedText() ?></td>
						</tr>
					<?php endif ?>
					<?php if ($parent_file->isHydrated()): ?>
						<tr>
							<th>Parent File:</th>
							<td><?php echo $parent_file->getLink() ?></td>
						</tr>
					<?php endif ?>
					<tr>
						<th>Source File:</th>
						<?php if ($source_file->isHydrated()): ?>
							<td><?php echo $source_file->getLink() ?></td>
						<?php else: ?>
							<td class="muted">n/a</td>
						<?php endif ?>
					</tr>
					<tr>
						<th>GCode File:</th>
						<?php if ($gcode_file->isHydrated()): ?>
							<td><?php echo $gcode_file->getLink() ?></td>
						<?php else: ?>
							<td class="muted">n/a</td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Slice Job:</th>
						<?php if ($slicejob->isHydrated()): ?>
							<td><?php echo $slicejob->getLink() ?></td>
						<?php else: ?>
							<td class="muted">n/a</td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Slice Engine:</th>
						<?php if ($sliceengine->isHydrated()): ?>
							<td><?php echo $sliceengine->getLink() ?></td>
						<?php else: ?>
							<td class="muted">n/a</td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Slice Config:</th>
						<?php if ($sliceconfig->isHydrated()): ?>
							<td><?php echo $sliceconfig->getLink() ?></td>
						<?php else: ?>
							<td class="muted">n/a</td>
						<?php endif ?>
					</tr>
					<tr>
						<th>Queue:</th>
						<td><?php echo $queue->getLink() ?></td>
					</tr>
					<?php if ($bot->isHydrated()): ?>
						<tr>
							<th>Bot:</th>
							<td><?php echo $bot->getLink() ?>
						</tr>
					<?php endif ?>
					<tr>
						<th>Creator:</th>
						<td><?php echo $creator->getLink() ?></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

		<?php $temps = JSON::decode($job->get('temperature_data')) ?>

		<div class="row tab-pane fade" id="temperature">
			<div class="span12">
				<?php if (is_object($temps) && $temps != false): ?>
					<div id="temperature_graph" style="width:100%; height:420px;"></div>
					<br clear="all"/>

					<script type="text/javascript">
						function graphme() {
							var tempData = [];
							<?php
								// Indent 6 times to match tempData;
								$tabIndent = "\t\t\t\t\t";
								echo $tabIndent."var bed = [];\n";
								echo $tabIndent."var extruder = [];\n";

								foreach ($temps AS $time => $data)
								{
									echo $tabIndent."bed.push([" . $time*1000 . ", " . round($data->bed, 1) . "]);\n";
									echo $tabIndent."extruder.push([" . $time*1000 . ", " . round($data->extruder, 1) . "]);\n";
								}

								echo $tabIndent."tempData.push({label: 'Bed Temp (C)', data: bed })\n\n";
								echo $tabIndent."tempData.push({label: 'Extruder Temp (C)', data: extruder })\n\n";
							?>
							var options = {
								legend: {show: true, position: "se"},
								series: {
									lines: {show: true},
									points: {show: false}
								},
								xaxis: {mode: 'time', timeformat: "%h:%M"},
								yaxis: {ticks: 10},
								selection: {mode: "xy"},
								grid: {
									hoverable: true
								},
								tooltip: true,
								tooltipOpts: {
									content: "%x - %s - <b>%y</b>",
									xDateFormat: "%h:%M",
									yDateFormat: "%h:%M",
									shifts: {
										x: 10,
										y: 20
									},
									defaultTheme: true
								}
							};

							var tempGraph_handler = $("#temperature_graph");
							var tempGraph = $.plot(tempGraph_handler, tempData, options);

							// now connect the two
							tempGraph_handler.bind("plotselected", function (event, ranges) {
								// clamp the zooming to prevent eternal zoom
								if (ranges.xaxis.to - ranges.xaxis.from < 0.00001)
									ranges.xaxis.to = ranges.xaxis.from + 0.00001;
								if (ranges.yaxis.to - ranges.yaxis.from < 0.00001)
									ranges.yaxis.to = ranges.yaxis.from + 0.00001;

								// do the zooming
								tempGraph = $.plot(tempGraph_handler, tempData,
									$.extend(true, {}, options, {
										xaxis: {min: ranges.xaxis.from, max: ranges.xaxis.to},
										yaxis: {min: ranges.yaxis.from, max: ranges.yaxis.to}
									}));

							});
						}

						$('a[data-toggle="tab"]').on('shown', function (e) {
							if (e.target.id == 'temperatureTab')
								graphme();
						});
					</script>
				<?php else: ?>
					<div class="alert alert-error">
						No temperature data recorded.
					</div>
				<?php endif ?>
			</div>
		</div>

		<div class="row tab-pane fade" id="files">
			<div class="span6">
				<h3>Source File: <?php echo $source_file->getLink() ?></h3>
				<?php if ($source_file->isHydrated()): ?>
					<iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400"
					        src="<?php echo $source_file->getUrl() ?>/render"></iframe>
				<?php else: ?>
					Source file does not exist.
				<?php endif ?>
			</div>
			<div class="span6">
				<h3>GCode File: <?php echo $gcode_file->getLink() ?></h3>
				<?php if ($gcode_file->isHydrated()): ?>
					<iframe id="output_frame" frameborder="0" scrolling="no" width="100%" height="400"
					        src="<?php echo $gcode_file->getUrl() ?>/render"></iframe>
				<?php else: ?>
					GCode file does not exist yet.
				<?php endif ?>
			</div>
		</div>

		<div class="row tab-pane fade" id="comments">
			<div class="span12 comments">
				<?php echo Controller::byName('comment')->renderView('add_comment', array('content_type' => 'job', 'content_id' => $job->id)) ?>
			</div>
		</div>

		<div class="row tab-pane fade" id="errors">
			<div class="span12">
				<?php echo Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'job')) ?>
			</div>
		</div>
	</div>
<?php endif ?>