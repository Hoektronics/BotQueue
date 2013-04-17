<div class="span<?=$size?> bot_thumbnail bot_thumbnail_<?=$size?>">
  <a href="<?=$b->getUrl()?>">
    <? if ($webcam->isHydrated()): ?>
      <img class="webcam" src="<?=$webcam->getRealUrl()?>">
    <? else: ?>
      <img class="webcam" src="/img/kitten-640x480.jpg">
    <? endif ?>
  </a>

	<div class="bot_header">
	  <h3><?=$b->getLink()?> <span class="muted">- <?=$b->getLastSeenHTML()?></span></h3>
  	<?=$b->getStatusHTML()?>
	</div>

	<? if ($j->isHydrated()): ?>
    <div class="bot_info">
  		<?=$j->getLink()?>
      <? $temps = JSON::decode($b->get('temperature_data')) ?>
  		<? if ($b->get('status') == 'working' && $temps != NULL): ?>
  		    E: <?=$temps->extruder?>C
  		    B: <?=$temps->bed?>C
  		<? endif ?>
  	  <?=$j->getStatusHTML()?>
  	  <span class="muted"><?=$j->getElapsedText()?></span>
  		<span class="muted"><?=$j->getEstimatedText()?></span>
      <? if ($j->get('status') == 'qa'): ?>
        <a class="btn btn-success" href="<?=$j->getUrl()?>/qa/pass">PASS</a>
        <a class="btn btn-primary" href="<?=$j->getUrl()?>/qa">VIEW</a>
        <a class="btn btn-danger" href="<?=$j->getUrl()?>/qa/fail">FAIL</a>
      <? elseif ($j->get('status') == 'slicing' && $sj->get('status') == 'pending'): ?>
        <a class="btn btn-success" href="<?=$sj->getUrl()?>/pass">PASS</a>
        <a class="btn btn-primary" href="<?=$sj->getUrl()?>">VIEW</a>
        <a class="btn btn-danger" href="<?=$sj->getUrl()?>/fail">FAIL</a>
      <? else: ?>
  			<div class="progress progress-striped active" style="width: 250px">
  			  <div class="bar" style="width: <?=round($j->get('progress'))?>%;"></div>
  			</div>
  		<? endif ?>
  		<span class="muted"><?= round($j->get('progress'), 2) ?>%</span>
  	</div>
  <? elseif ($b->get('status') == 'error'): ?>
    <div class="bot_info">
  	  <span class="text-error"><?=$b->get('error_text')?></span>
    </div>
	<? endif ?>
</div>