<?php if (!empty($errors)): ?>
  <table class="table table-striped table-bordered table-condensed">
  	<thead>
  	  <th>Error</th>
  	  <?php if ($hide != 'job'): ?>
  			<th>Job</th>
  		<?php endif ?>
  	  <?php if ($hide != 'bot'): ?>
  		  <th>Bot</th>
  	  <?php endif ?>
  		<?php if ($hide != 'queue'): ?>
  			<th>Queue</th>
  		<?php endif ?>
  		<?php /* if ($hide != 'user'): ?>
  			<th>User</th>
  		<?php endif */ ?>
  		<th>Date</th>
  	</thead>
  	<tbody>
    <?php foreach ($errors AS $row): ?>
      <?php $log = $row['ErrorLog'] ?>
      <tr>
    	  <td><span class="text-error"><?php echo Utility::sanitize($log->get('reason')) ?></span></td>
    	  <?php if ($hide != 'job'): ?>
    			<td><?php echo $log->getJob()->getLink() ?></td>
    		<?php endif ?>
    	  <?php if ($hide != 'bot'): ?>
    		  <td><?php echo $log->getBot()->getLink() ?></td>
    	  <?php endif ?>
    		<?php if ($hide != 'queue'): ?>
    			<td><?php echo $log->getQueue()->getLink() ?></td>
    		<?php endif ?>
    		<?php /*if ($hide != 'user'): ?>
    			<td><?php echo $log->getUser()->getLink() ?></td>
    		<?php endif*/ ?>
    		<td><?php echo Utility::formatDateTime($log->get('error_date')) ?></td>        
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <div class="alert alert-success ">
    Yay!  No errors!!
  </div>
<?php endif ?>