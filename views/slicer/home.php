<div class="row">
	<div class="span12">
		<? if (User::isAdmin()): ?>
  	  <p>
  	    <a class="btn btn-primary" href="/slicer/create">Create New Slice Engine</a>
  	  </p>
  	<? endif ?>
	  <? if (!empty($slicers)): ?>
    	<table class="table table-striped table-bordered table-condensed">
    		<thead>
    			<th>Slicer</th>
    			<th>Added</th>
    			<th>Description</th>
    			<? if (User::isAdmin()): ?>
    			  <th>Manage</th>
          <? endif ?>
    		</thead>
    		<tbody>
    	    <? foreach ($slicers AS $row): ?>
    	      <? $engine = $row['SliceEngine']?>
    	      <tr>
      	      <td><?=$engine->getLink()?></td>
      	      <td><?=Utility::formatDateTime($engine->get('add_date'))?></td>
      	      <td><?= Utility::sanitize($engine->get('engine_description'))?></td>
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
<? if (User::isLoggedIn()): ?>
  <h2>My Slice Engine Configurations <a class="btn btn-primary" href="/slicer/createconfig">Create New Config</a></h2>
	<div class="row">
		<div class="span12">
			<table class="table table-striped table-bordered table-condensed">
        <thead>
          <tr>
            <th>Config Name</th>
            <th>Slice Engine</th>
            <th>Add Date</th>
            <th>Edit Date</th>
            <th>Manage</th>
          </tr>
        </thead>
				<tbody>
				  <?foreach ($configs AS $row): ?>
				    <?$config = $row['SliceConfig']?>
				    <?$engine = $row['SliceEngine']?>
				    <tr>
				      <td><?=$config->getLink()?></td>
				      <td><?=$engine->getLink()?></td>
              <td><?=Utility::formatDateTime($config->get('add_date'))?></td>
              <td><?=Utility::formatDateTime($config->get('edit_date'))?></td>
              <td>
    	        	<a class="btn btn-mini" href="<?=$config->getUrl()?>/edit"><i class="icon-cog"></i> edit</a>
  							<a class="btn btn-mini" href="<?=$config->getUrl()?>/delete"><i class="icon-remove"></i> delete</a>
      	      </td>
				    </tr>
				  <? endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
<? endif ?>