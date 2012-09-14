<? if (!empty($jobs)): ?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Status</th>
				<th>%</th>
				<th>Elapsed</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($jobs AS $row): ?>
				<? $j = $row['Job'] ?>
				<? $bot = $j->getBot() ?>
				<tr>
					<td><?=$j->id?></td>
					<td><?=$j->getLink()?></td>
					<td><?=$j->getStatusHTML()?></td>
					<td><?=round($j->get('progress'), 2)?>%</td>
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