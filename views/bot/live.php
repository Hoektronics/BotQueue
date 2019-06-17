<div class="row">
	<div class="span12">
		<?php if (!empty($bots)): ?>
      <?php echo Controller::byName('main')->renderView('dashboard_medium_thumbnails', array('bots' => $bots)) ?>
    <?php else: ?>
      <div class="alert">
        <strong>No active bots found!</strong>
      </div>
    <?php endif ?>
	</div>
</div>
