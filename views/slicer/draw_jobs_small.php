<table class="table table-striped table-bordered table-condensed">
	<tbody>
    <tr>
      <th>Name</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
    <?php if (!empty($jobs)): ?>
      <?php foreach ($jobs AS $row): ?>
        <?php $job = $row['SliceJob'] ?>
        <tr>
          <td><?php echo $job->getLink() ?></td>
          <td><?php echo JobStatus::getStatusHTML($job) ?></td>
          <?php if ($job->get('status') == 'available'): ?>
            <td><?php echo Utility::relativeTime($job->get('add_date')) ?></td>
          <?php elseif ($job->get('status') == 'slicing'): ?>
            <td><?php echo Utility::relativeTime($job->get('taken_date')) ?></td>
          <?php else: ?>
            <td><?php echo Utility::relativeTime($job->get('finish_date')) ?></td>
          <?php endif ?>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="3"><strong>No jobs found!</strong></td>
      </tr>
    <?php endif ?>
  </tbody>
</table>