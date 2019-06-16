<table class="table table-striped table-bordered table-condensed">
	<tbody>
    <tr>
      <th>Name</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
    <? if (!empty($jobs)): ?>
      <? foreach ($jobs AS $row): ?>
        <? $job = $row['SliceJob'] ?>
        <tr>
          <td><?php echo $job->getLink() ?></td>
          <td><?php echo JobStatus::getStatusHTML($job) ?></td>
          <? if ($job->get('status') == 'available'): ?>
            <td><?php echo Utility::relativeTime($job->get('add_date')) ?></td>
          <? elseif ($job->get('status') == 'slicing'): ?>
            <td><?php echo Utility::relativeTime($job->get('taken_date')) ?></td>
          <? else: ?>
            <td><?php echo Utility::relativeTime($job->get('finish_date')) ?></td>
          <? endif ?>
        </tr>
      <? endforeach ?>
    <? else: ?>
      <tr>
        <td colspan="3"><strong>No jobs found!</strong></td>
      </tr>
    <? endif ?>
  </tbody>
</table>