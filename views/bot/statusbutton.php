<div class="btn-group bot_status_button">
  <a id="bot_status_button_<?=$bot->id?>" class="btn btn-mini btn-bot-status btn-<?=$bot->getStatusHTMLClass()?> dropdown-toggle" data-toggle="dropdown" href="#">
    <span id="bot_status_txt_<?=$bot->id?>"><?=$bot->getStatus()?></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">
    <? if ($bot->getStatus() == 'working'): ?>
      <li><a href="<?=$bot->getUrl()?>/pause"><i class="icon-pause"></i>pause job</a></li>
      <li><a href="<?=$bot->getUrl()?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <? elseif ($bot->getStatus() == 'paused'): ?>
      <li><a href="<?=$bot->getUrl()?>/play"><i class="icon-play"></i>resume job</a></li>
      <li><a href="<?=$bot->getUrl()?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <? elseif ($bot->getStatus() == 'slicing'): ?>
      <li><a href="<?=$bot->getUrl()?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <? elseif ($bot->getStatus() == 'waiting'): ?>
      <li><a href="<?=$bot->getCurrentJob()->getUrl()?>/qa"><i class="icon-check"></i> verify output</a></li>
    <? elseif ($bot->getStatus() == 'idle'): ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/offline"><i class="icon-stop"></i> take offline</a></li>
    <? elseif ($bot->getStatus() != 'retired'): ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/idle"><i class="icon-play"></i> bring online</a></li>
	<? endif ?>
    <? if ($bot->getStatus() != 'retired'): ?>
		  <li><a href="<?=$bot->getUrl()?>/edit"><i class="icon-cog"></i> edit bot</a></li>
    <? endif ?>
		<li><a href="<?=$bot->getUrl()?>/delete"><i class="icon-remove"></i> delete bot</a></li>
    <? if ($bot->getStatus() == 'offline'): ?>
      <li><a href="<?=$bot->getUrl()?>/retire"><i class="icon-lock"></i> retire bot</a></li>
    <? endif ?>
  </ul>
</div>