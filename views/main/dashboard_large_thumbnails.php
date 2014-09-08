<div class="row">
  <? foreach ($bots AS $row): ?>
    <?= Controller::byName("bot")->renderView("thumbnail", array('size' => 6, 'bot' => $row['Bot'], 'job' => $row['Job']))?>
  <? endforeach ?>
</div>