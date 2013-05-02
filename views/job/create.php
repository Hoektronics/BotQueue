<? if ($megaerror): ?>
  <?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if (!empty($kids)): ?>
    <script>
      $(document).ready(function(){
        $('#global_queue').on('change', function(e) {
          if ($(this).val())
            $('.job_queue').val($(this).val());
        });

        $('#global_qty_btn').on('click', function(e) {
          $('.job_qty').val($('#global_qty').val());
        });
        
        $('#global_use').on('change', function(e) {
          $('.job_use').each(function(key, ele) {
            $(ele).prop('checked', $('#global_use').prop('checked'));
          });
        });

        $('#global_priority').on('change', function(e) {
          $('.job_priority').each(function(key, ele) {
            $(ele).prop('checked', $('#global_priority').prop('checked'));
          });
        });
      });
    </script>
    <form method="post" action="/job/create/file:<?=$file->id?>">
      <div class="row">
        <div class="span3">
          <h3>Bulk Add Jobs</h3>
          <p>
            This file is a .zip file that contains other printable files.  Please use the form to the right to add these jobs to BotQueue.
          </p>
          <p>
            You can use the form below to change settings for all the sub-file jobs.
          </p>
          <p>
            <select id="global_queue" onchange="">
		          <option value="0">Change all Queues</option>
		          <? foreach ($queues AS $row2): ?>
		            <? $q = $row2['Queue'] ?>
		            <option value="<?=$q->id?>"><?=$q->getName()?></option>
              <? endforeach ?>
		        </select>
            <div class="input-prepend input-append">
  		        <span class="add-on">Quantity:</span>
  		        <input type="text" id="global_qty" value="1" class="input-mini">
  		        <input type="button" id="global_qty_btn" class="btn btn-primary" value="Update">
		        </div>
		        <label class="checkbox"><input type="checkbox" id="global_priority" value="1"> Are these all priority jobs?</label>
          </p>
          <input type="submit" name="submit" class="btn btn-primary" value="Create the Jobs!">
        </div>
        <div class="span9" id="file_list">
        	<table class="table table-striped table-bordered table-condensed">
        		<thead>
        			<tr>
        				<th align="center"><input type="checkbox" id="global_use" name="global_use" value="1" checked></th>
        				<th>Quantity</th>
        				<th style="width: 100%">File</th>
        				<th>Priority</th>
        				<th>Queue</th>
        			</tr>
        		</thead>
        		<tbody>
        		  <? foreach ($kids AS $row): ?>
        		    <? $kid = $row['S3File'] ?>
        		    <tr>
        		      <td align="center"><input type="checkbox" class="job_use" name="use[<?=$kid->id?>]" value="1" checked></td>
        		      <td><input type="text" name="qty[<?=$kid->id?>]" value="1" class="job_qty input-mini"></td>
        		      <td style="font-size: 125%"><?=$kid->getLink()?></td>
      		        <td align="center"><input type="checkbox" class="job_priority" name="priority[<?=$kid->id?>]" value="1"></td>
        		      <td>
        		        <select class="job_queue" name="queues[<?=$kid->id?>]">
        		          <? foreach ($queues AS $row2): ?>
        		            <? $q = $row2['Queue'] ?>
        		            <option value="<?=$q->id?>"><?=$q->getName()?></option>
                      <? endforeach ?>
        		        </select>
        		      </td>
        		    </tr>
        		  <? endforeach ?>
        		</tbody>
        	</table>
        </div>
      </div>
  	</form>
  <? else: ?>
  	<?= $form->render() ?>
  <? endif ?>  
<? endif ?>