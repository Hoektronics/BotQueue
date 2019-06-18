<?php if (isset($megaerror)): ?>
	<?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<?php else: ?>
  <div class="tabbable"> <!-- Only required for left/right tabs -->
    <ul class="nav nav-tabs">
      <li class="active"><a href="#tab1" data-toggle="tab">Upload Config</a></li>
      <li><a href="#tab2" data-toggle="tab">Edit Raw Config</a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="tab1">
      	<?php echo $uploadform->render() ?>
      </div>
      <div class="tab-pane" id="tab2">
      	<?php echo $rawform->render() ?>
      </div>
    </div>
  </div>
<?php endif ?>