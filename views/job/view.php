<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
	<div class="row">
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Status:</th>
						<td><?=$job->getStatusHTML() ?></td>
					</tr>
					<tr>
						<th>Progress:</th>
						<td><div class="progress progress-striped"><div class="bar" style="width: <?=round($job->get('progress'))?>%;"></div></div></td>
					</tr>
					<tr>
						<th>Created:</th>
						<td><?= Utility::formatDatetime($job->get('created_time'))?> (<?=Utility::relativeTime($job->get('created_time'))?>)</td>
					</tr>
					<tr>
						<th>Grabbed:</th>
						<? if (strtotime($job->get('taken_time')) > 0): ?>
							<td><?= Utility::formatDatetime($job->get('taken_time'))?> (<?=Utility::relativeTime($job->get('taken_time'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Downloaded:</th>
						<? if (strtotime($job->get('downloaded_time')) > 0): ?>
							<td><?= Utility::formatDatetime($job->get('downloaded_time'))?> (<?=Utility::relativeTime($job->get('downloaded_time'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Print Complete:</th>
						<? if (strtotime($job->get('finished_time')) > 0): ?>
							<td><?= Utility::formatDatetime($job->get('finished_time'))?> (<?=Utility::relativeTime($job->get('finished_time'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Finished:</th>
						<? if (strtotime($job->get('verified_time')) > 0): ?>
							<td><?= Utility::formatDatetime($job->get('verified_time'))?> (<?=Utility::relativeTime($job->get('verified_time'))?>)</td>
						<? else: ?>
							<td>n/a</td>
						<? endif?>
					</tr>
					<tr>
						<th>Elapsed:</th>
						<td><?=$job->getElapsedText()?></td>
					</tr>
					<? if ($job->get('status') == 'taken'): ?>
  					<tr>
  						<th>Remaining:</th>
  						<td><?=$job->getEstimatedText()?></td>
  					</tr>
  				<? endif ?>
				</tbody>
			</table>
		</div>
		<div class="span6">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Manage:</th>
						<td>
							<? if ($job->get('status') == 'available'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
							<? endif ?>
							<? if ($job->get('status') == 'qa'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/qa"><i class="icon-check"></i> verify</a>
							<? endif ?>
							<? if ($job->get('status') != 'taken' && $job->get('status') != 'qa'): ?>
								<a class="btn btn-mini" href="<?=$job->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
							<? endif ?>
							<a class="btn btn-mini" href="/job/create/job:<?=$job->id?>"><i class="icon-repeat"></i> re-run</a>
						</td>
					</tr>
					<? if ($source_file->isHydrated()): ?>			
  					<tr>
  						<th>Source File:</th>
  						<td><?=$source_file->getLink()?></td>
  					</tr>
  				<? endif ?>
					<? if ($gcode_file->isHydrated()): ?>
  					<tr>
  						<th>GCode File:</th>
  						<td><?=$gcode_file->getLink()?></td>
  					</tr>
					<? endif ?>
					<? if ($slicejob->isHydrated()): ?>
  					<tr>
  						<th>Slice Job:</th>
  						<td><?=$slicejob->getLink()?></td>
  					</tr>
          <? endif ?>
					<tr>
						<th>Queue:</th>
						<td><?=$queue->getLink()?></td>
					</tr>
					<? if ($bot->isHydrated()): ?>
						<tr>
							<th>Bot:</th>
							<td><?=$bot->getLink()?>
						</tr>
					<? endif ?>
					<tr>
						<th>Creator:</th>
						<td><?=$creator->getLink()?></td>
					</tr>			
				</tbody>
			</table>
		</div>
	</div>
	
  <div class="row">
		<div class="span6">
		  <h3>Source File: <?=$source_file->getLink()?></h3>
		  <? if ($gcode_file->isHydrated()): ?>
		    <iframe id="input_frame" frameborder="0" scrolling="no" width="100%" height="400" src="<?=$source_file->getUrl()?>/render"></iframe>
		  <? else: ?>
        Source file does not exist.
      <? endif ?>
  	</div>
		<div class="span6">
		  <h3>GCode File: <?=$gcode_file->getLink() ?></h3>
		  <? if ($gcode_file->isHydrated()): ?>
  		  <iframe id="output_frame" frameborder="0" scrolling="no" width="100%" height="400" src="<?=$gcode_file->getUrl()?>/render"></iframe>
      <? else: ?>
        GCode file does not exist yet.
      <? endif ?>
		</div>
	</div>
		
	<? if (!empty($errors)): ?>
  	<div class="row">
  	  <div class="span12">
    	  <h3>Error Log</h3>
  	    <?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'job'))?>
      </div>
  	</div>
  <? endif ?>
<? endif ?>