<?
/**
 * @package botqueue_job
 * @var string $megaerror
 * @var array $comments
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
<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
	<ul id="myTab" class="nav nav-tabs">
		<li class="active"><a href="#details" data-toggle="tab">Details</a></li>
		<li><a href="#temperature" id="temperatureTab" data-toggle="tab">Temperature Log</a></li>
		<li><a href="#files" data-toggle="tab">Files</a></li>
		<li><a href="#comments" data-toggle="tab">Comments<? if (count($comments) > 0): ?> <span
					class="badge badge-info"><?= count($comments) ?></span><? endif ?></a></li>
		<li><a href="#errors" data-toggle="tab">Error Log<? if (count($errors) > 0): ?> <span
					class="badge badge-important"><?= count($errors) ?></span><? endif ?></a></li>
	</ul>

	<div id="myTabContent" class="tab-content">
	<div class="row tab-pane fade in active" id="details">
		<div class="span6">
			<? if ($webcam->isHydrated()): ?>
				<img src="<?= $webcam->getDownloadURL() ?>">
			<? else: ?>
				<img src="/img/colorbars.gif">
			<? endif ?>
		</div>
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr>
					<th>Manage:</th>
					<td>
						<? if ($job->get('status') == 'available'): ?>
							<a class="btn btn-mini" href="<?= $job->getUrl() ?>/bump"><i class="icon-arrow-up"></i> bump</a>
							<a class="btn btn-mini" href="<?= $job->getUrl() ?>/edit"><i class="icon-cog"></i> edit</a>
							<a class="btn btn-mini" href="<?= $job->getUrl() ?>/cancel"><i class="icon-eject"></i>
								cancel</a>
						<? endif ?>
						<? if ($job->get('status') == 'qa'): ?>
							<a class="btn btn-mini" href="<?= $job->getUrl() ?>/qa"><i class="icon-check"></i>
								verify</a>
						<? endif ?>
						<? if ($job->get('status') != 'taken' && $job->get('status') != 'qa' && $job->get('status') != 'slicing'): ?>
							<a class="btn btn-mini" href="<?= $job->getUrl() ?>/delete"><i class="icon-remove"></i>
								delete</a>
						<? endif ?>
						<a class="btn btn-mini" href="/job/create/job:<?= $job->id ?>"><i class="icon-repeat"></i>
							re-run</a>
					</td>
				</tr>
				<tr>
					<th>Status:</th>
					<td><?= JobStatus::getStatusHTML($job) ?></td>
				</tr>
				<tr>
					<th>Progress:</th>
					<td>
						<div class="progress progress-striped">
							<div class="bar" style="width: <?= round($job->get('progress')) ?>%;"></div>
						</div>
					</td>
				</tr>
				<tr>
					<th>Created:</th>
					<td><?= Utility::formatDateTime($job->get('created_time')) ?> <span
							class="muted">(<?= Utility::relativeTime($job->get('created_time')) ?>)</span></td>
				</tr>
				<tr>
					<th>Grabbed:</th>
					<? if (strtotime($job->get('taken_time')) > 0): ?>
						<td><?= Utility::formatDateTime($job->get('taken_time')) ?> <span
								class="muted">(<?= Utility::relativeTime($job->get('taken_time')) ?>)</span></td>
					<? else: ?>
						<td><span class="muted">n/a</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Sliced:</th>
					<? if (strtotime($job->get('slice_complete_time')) > 0): ?>
						<td><?= Utility::formatDateTime($job->get('slice_complete_time')) ?> <span
								class="muted">(<?= Utility::relativeTime($job->get('slice_complete_time')) ?>)</span>
						</td>
					<? else: ?>
						<td><span class="muted">n/a</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Downloaded:</th>
					<? if (strtotime($job->get('downloaded_time')) > 0): ?>
						<td><?= Utility::formatDateTime($job->get('downloaded_time')) ?> <span
								class="muted">(<?= Utility::relativeTime($job->get('downloaded_time')) ?>)</span></td>
					<? else: ?>
						<td><span class="muted">n/a</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Print Complete:</th>
					<? if (strtotime($job->get('finished_time')) > 0): ?>
						<td><?= Utility::formatDateTime($job->get('finished_time')) ?> <span
								class="muted">(<?= Utility::relativeTime($job->get('finished_time')) ?>)</span></td>
					<? else: ?>
						<td><span class="muted">n/a</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Finished:</th>
					<? if (strtotime($job->get('verified_time')) > 0): ?>
						<td><?= Utility::formatDateTime($job->get('verified_time')) ?> <span
								class="muted">(<?= Utility::relativeTime($job->get('verified_time')) ?>)</span></td>
					<? else: ?>
						<td><span class="muted">n/a</span></td>
					<? endif ?>
				</tr>
				<tr>
					<th>Elapsed:</th>
					<td><?= $job->getElapsedText() ?></td>
				</tr>
				<? if ($job->get('status') == 'taken'): ?>
					<tr>
						<th>Remaining:</th>
						<td><?= $job->getEstimatedText() ?></td>
					</tr>
				<? endif ?>
				<? if ($parent_file->isHydrated()): ?>
					<tr>
						<th>Parent File:</th>
						<td><?= $parent_file->getLink() ?></td>
					</tr>
				<? endif ?>
				<tr>
					<th>Source File:</th>
					<? if ($source_file->isHydrated()): ?>
						<td><?= $source_file->getLink() ?></td>
					<? else: ?>
						<td class="muted">n/a</td>
					<? endif ?>
				</tr>
				<tr>
					<th>GCode File:</th>
					<? if ($gcode_file->isHydrated()): ?>
						<td><?= $gcode_file->getLink() ?></td>
					<? else: ?>
						<td class="muted">n/a</td>
					<? endif ?>
				</tr>
				<tr>
					<th>Slice Job:</th>
					<? if ($slicejob->isHydrated()): ?>
						<td><?= $slicejob->getLink() ?></td>
					<? else: ?>
						<td class="muted">n/a</td>
					<? endif ?>
				</tr>
				<tr>
					<th>Slice Engine:</th>
					<? if ($sliceengine->isHydrated()): ?>
						<td><?= $sliceengine->getLink() ?></td>
					<? else: ?>
						<td class="muted">n/a</td>
					<? endif ?>
				</tr>
				<tr>
					<th>Slice Config:</th>
					<? if ($sliceconfig->isHydrated()): ?>
						<td><?= $sliceconfig->getLink() ?></td>
					<? else: ?>
						<td class="muted">n/a</td>
					<? endif ?>
				</tr>
				<tr>
					<th>Queue:</th>
					<td><?= $queue->getLink() ?></td>
				</tr>
				<? if ($bot->isHydrated()): ?>
					<tr>
						<th>Bot:</th>
						<td><?= $bot->getLink() ?>
					</tr>
				<? endif ?>
				<tr>
					<th>Creator:</th>
					<td><?= $creator->getLink() ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>

	<? $temps = JSON::decode($job->get('temperature_data')) ?>

	<div class="row tab-pane fade" id="temperature">
		<div class="span12">
			<? if (is_object($temps) && $temps != false): ?>
				<div id="temperature_graph" style="width:100%; height:420px;"></div>
				<br clear="all"/>

				<script type="text/javascript">
					function graphme() {
						var tempData = [];
						<?
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
							legend: { show: true, position: "se" },
							series: {
								lines: { show: true },
								points: { show: false }
							},
							xaxis: { mode: 'time', timeformat: "%h:%M"},
							yaxis: { ticks: 10 },
							selection: { mode: "xy" },
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
									xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to },
									yaxis: { min: ranges.yaxis.from, max: ranges.yaxis.to }
								}));

						});
					}

					$('a[data-toggle="tab"]').on('shown', function (e) {
						if (e.target.id == 'temperatureTab')
							graphme();
					});
				</script>
			<? else: ?>
				<div class="alert alert-error">
					No temperature data recorded.
				</div>
			<? endif ?>
		</div>
	</div>

	<div class="row tab-pane fade" id="files">
		<div class="span6">
			<h3>Source File: <?= $source_file->getLink() ?></h3>
			<? if ($source_file->isHydrated()): ?>
				<iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400"
				        src="<?= $source_file->getUrl() ?>/render"></iframe>
			<? else: ?>
				Source file does not exist.
			<? endif ?>
		</div>
		<div class="span6">
			<h3>GCode File: <?= $gcode_file->getLink() ?></h3>
			<? if ($gcode_file->isHydrated()): ?>
				<iframe id="output_frame" frameborder="0" scrolling="no" width="100%" height="400"
				        src="<?= $gcode_file->getUrl() ?>/render"></iframe>
			<? else: ?>
				GCode file does not exist yet.
			<? endif ?>
		</div>
	</div>

	<div class="row tab-pane fade" id="comments">
		<div class="span12 comments">
			<?= Controller::byName('comment')->renderView('draw_all', array('comments' => $comments)) ?>
			<?= Controller::byName('comment')->renderView('add_comment', array('content_type' => 'job', 'content_id' => $job->id)) ?>
		</div>
	</div>

	<div class="row tab-pane fade" id="errors">
		<div class="span12">
			<?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'job')) ?>
		</div>
	</div>
	</div>
<? endif ?>