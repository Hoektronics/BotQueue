<div class="row">
	<div class="span12">
		<? if (User::isAdmin()): ?>
  	  <p>
  	    <a class="btn btn-primary" href="/slicer/create">Create New Slicer</a>
  	  </p>
  	<? endif ?>
	  <? if (!empty($slicers)): ?>
    	<table class="table table-striped table-bordered table-condensed">
    		<thead>
    			<th>Slicer</th>
    			<th>Path</th>
    			<th>Public?</th>
    			<th>Featured?</th>
    			<th>Added</th>
    			<? if (User::isAdmin()): ?>
    			  <th>Manage</th>
          <? endif ?>
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
      	      <? if (User::isAdmin()): ?>
        	      <td>
      	        	<a class="btn btn-mini" href="<?=$engine->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
    							<a class="btn btn-mini" href="<?=$engine->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
        	      </td>
        	    <? endif ?>
      	    </tr>
    	    <? endforeach ?>
  	    </tbody>
  	  </table>
	  <? endif ?>
	</div>
</div>