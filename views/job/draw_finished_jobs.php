<? if (!empty($jobs)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Duration</th>
				<th>Manage</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($jobs AS $row): ?>
				<? $j = $row['Job'] ?>
				<? $bot = $j->getBot() ?>
				<tr>
					<td><?=$j->id?></td>
					<td><?=$j->getLink()?></td>
					<td><?=$j->getElapsedText()?></td>
					<td><a class="btn btn-mini" href="/job/create/job:<?=$j->id?>"><i class="icon-repeat"></i> re-run</a></td>
				</tr>
			<?endforeach?>
		</tbody>
	</table>
<? else: ?>
  <div class="alert">
    <strong>No jobs found!</strong>  To get started, <a href="/upload">upload a job</a>.
  </div>
<? endif ?>