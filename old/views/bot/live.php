<div class="row">
	<div class="span12">
		<? if (!empty($bots)): ?>
      <?= Controller::byName('main')->renderView('dashboard_medium_thumbnails', array('bots' => $bots)) ?>
    <? else: ?>
      <div class="alert">
        <strong>No active bots found!</strong>
      </div>
    <? endif ?>
	</div>
</div>
