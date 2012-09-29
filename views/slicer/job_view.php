<? if ($megaerror): ?>
	<?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if ($job->get('status') == 'pending'): ?>
  <h2>Oh no!  Something went wrong with the slicing process.</h3>

  <div class="alert alert-error">
    <p>Here is the error that the slice engine reported:</p>
    <blockquote><?= nl2br(Utility::sanitize($job->get('error_log')))?></blockquote>
  </div>
  
  <div class="row">
    <div class="span6">
        <div class="alert alert-success">
          <input type="hidden" name="submit" value="1">
          <a class="btn btn-large btn-success" style="float:right;" href="<?=$job->getUrl()?>/pass">PASS</a>
          <span>If everything is okay, click <strong>Pass</strong>.<br/> The slice job will be marked as good and your bot will run it.</span>
        </div>
      </form>
    </div>
    <div class="span6">
      <div class="alert alert-error">
        <a class="btn btn-large btn-danger" style="float:right;" href="<?=$job->getUrl()?>/fail">FAIL</a>
        <span>If there are problems, click <strong>Fail</strong>.<br/> The job will be cancelled and your bot will move onto the next job.</span>
      </div>
    </div>
  </div>
  <? endif ?>
	<div class="row">
		<div class="span12">
			<table class="table table-striped table-bordered table-condensed">
				<tbody>
					<tr>
						<th>Slice Job:</th>
						<td><?=$job->getName() ?></td>
					</tr>
					<tr>
						<th>Status:</th>
						<td><?=$job->getStatusHTML() ?></td>
					</tr>
					<tr>
						<th>Slice Engine:</th>
						<td><?=$engine->getLink() ?></td>
					</tr>
					<tr>
						<th>Slice Config:</th>
						<td><?=$config->getLink() ?></td>
					</tr>
					<tr>
						<th>Input File:</th>
						<td><?=$inputfile->getLink() ?></td>
					</tr>
					<tr>
						<th>Output File:</th>
						<? if ($outputfile->isHydrated()): ?>
						  <td><?=$outputfile->getLink() ?></td>
					  <? else: ?>
					    <td>n/a</td>
					  <? endif ?>
					</tr>					
					<tr>
						<th>Add Date:</th>
						<td><?=Utility::formatDateTime($job->get('add_date'))?></td>
					</tr>
					<? if(strtotime($job->get('taken_date')) > 0): ?>
  					<tr>
  						<th>Taken Date:</th>
  						<td><?=Utility::formatDateTime($job->get('taken_date'))?></td>
  					</tr>
  				<? endif ?>
					<? if(strtotime($job->get('finish_date')) > 0): ?>
  					<tr>
  						<th>Finished Date:</th>
  						<td><?=Utility::formatDateTime($job->get('finish_date'))?></td>
  					</tr>
  				<? endif ?>
					<? if ($job->get('output_log')): ?>
  					<tr>
  						<th>Output Log:</th>
  						<td><?= nl2br(Utility::sanitize($job->get('output_log'))) ?></td>
  					</tr>
  				<? endif ?>
					<? if ($job->get('error_log')): ?>
            <tr>
  						<th>Error Log:</th>
  						<td><?= nl2br(Utility::sanitize($job->get('error_log')))?></td>
  					</tr>
  				<? endif ?>
          <tr>
						<th>Slice Config Snapshot:</th>
						<td><button class="btn" onclick="$('#config_snapshot').show()">Click to display config snapshot information</button></td>
					</tr>
					<tr id="config_snapshot" style="display: none">
						<td colspan="2"><?= nl2br(Utility::sanitize($job->get('slice_config_snapshot')))?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
<? endif ?>