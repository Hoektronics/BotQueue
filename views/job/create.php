<? if ($megaerror): ?>
  <?= Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror))?>
<? else: ?>
  <? if (!empty($kids)): ?>
    <div class="row">
      <div class="span3">
        <h3>Bulk Add Jobs</h3>
        <p>
          This file is a .zip file that contains other printable files.  Please use the form to the right to add these jobs to BotQueue.
        </p>
      </div>
      <div class="span9">
        <form method="post" action="/job/create/file:<?=$file->id?>">
        	<table class="table table-striped table-bordered table-condensed">
        		<thead>
        			<tr>
        				<th align="center">Use?</th>
        				<th>Quantity</th>
        				<th>File</th>
        				<th>Queue</th>
        				<th>Priority</th>
        			</tr>
        		</thead>
        		<tbody>
        		  <? foreach ($kids AS $row): ?>
        		    <? $kid = $row['S3File'] ?>
        		    <tr>
        		      <td align="center"><input type="checkbox" name="use[<?=$kid->id?>]" value="1" checked></td>
        		      <td><input type="text" name="qty[<?=$kid->id?>]" value="1" class="input-mini"></td>
        		      <td><?=$kid->getLink()?></td>
        		      <td>
        		        <select name="queues[<?=$kid->id?>]">
        		          <? foreach ($queues AS $row2): ?>
        		            <? $q = $row2['Queue'] ?>
        		            <option value="<?=$q->id?>"><?=$q->getName()?></option>
                      <? endforeach ?>
        		        </select>
        		      </td>
        		      <td><label class="checkbox"><input type="checkbox" name="priority[<?=$kid->id?>]" value="1"> Is this a priority job?</label></td>
        		    </tr>
        		  <? endforeach ?>
        		</tbody>
        	</table>
        	<input type="submit" name="submit" class="btn btn-primary" value="Create ALL The Jobs!">
      	</form>
      </div>
    </div>
  <? else: ?>
  	<?= $form->render() ?>
  <? endif ?>  
<? endif ?>