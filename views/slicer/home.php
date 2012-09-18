<div class="row">
	<div class="span12">
	  <a href="/slicer/create">Create new slicer</a>
	  <? if (!empty($slicers)): ?>
    	<table class="table table-striped table-bordered table-condensed">
    		<thead>
    			<th>Slicer</th>
    			<th>Path</th>
    			<th>Public?</th>
    			<th>Featured?</th>
    			<th>Added</th>
    		</thead>
    		<tbody>
    	    <? foreach ($slicers AS $row): ?>
    	      <? $engine = $row['SliceEngine']?>
    	      <tr>
      	      <td><?=$engine->getLink()?></td>
      	      <td><?=$engine->get('engine_path')?></td>
      	      <td><?=($engine->get('is_public')) ? 'yes':'no'?></td>
      	      <td><?=($engine->get('is_featured')) ? 'yes':'no'?></td>
      	      <td><?=Utility::formatDateTime($engine->get('add_date'))?></td>
      	    </tr>
    	    <? endforeach ?>
  	    </tbody>
  	  </table>
	  <? endif ?>
	</div>
</div>