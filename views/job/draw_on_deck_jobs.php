<? if (!empty($jobs)): ?>
	<table class="table table-striped table-bordered table-condensed jobtable">
		<thead>
			<tr>
			  <th></th>
				<th>Name</th>
				<th>Queue</th>
				<th>Age</th>
			</tr>
		</thead>
		<tbody class="joblist">
			<? foreach ($jobs AS $row): ?>
				<? $j = $row['Job'] ?>
				<? $bot = $j->getBot() ?>
				<tr id="job_<?=$j->id?>">
				  <td><i class="icon-resize-vertical"></i></td>
					<td><?=$j->getLink()?></td>
					<td><?=$j->getQueue()->getLink()?></td>
					<td><?=$j->getElapsedText()?></td>
				</tr>
			<?endforeach?>
		</tbody>
	</table>
<? else: ?>
  <div class="alert">
    <strong>No jobs found!</strong>  To get started, <a href="/upload">upload a job</a>.
  </div>
<? endif ?>