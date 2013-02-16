<div class="btn-group">
  <a id="bot_status_button_<?=$bot->id?>" class="btn btn-mini btn-bot-status btn-<?=$bot->getStatusHTMLClass()?> dropdown-toggle" data-toggle="dropdown" href="#">
    <span id="bot_status_txt_<?=$bot->id?>"><?=$bot->get('status')?></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">
    <? if ($bot->get('status') == 'working'): ?>
      <!--
      <li><i class="icon-pause"></i>pause job</li>
      <li><i class="icon-play"></i>resume job</li>
    -->
      <li><a href="<?=$bot->getUrl()?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <? elseif ($bot->get('status') == 'slicing'): ?>
      <li><a href="<?=$bot->getUrl()?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <? elseif ($bot->get('status') == 'waiting'): ?>
      <li><a href="<?=$bot->getCurrentJob()->getUrl()?>/qa"><i class="icon-check"></i> verify output</a></li>
    <? elseif ($bot->get('status') == 'idle'): ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/offline"><i class="icon-pause"></i> take offline</a></li>
    <? else: ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/idle"><i class="icon-play"></i> bring online</a></li>
		<? endif ?>
		<li><a href="<?=$bot->getUrl()?>/edit"><i class="icon-cog"></i> edit bot</a></li>
		<li><a href="<?=$bot->getUrl()?>/delete"><i class="icon-remove"></i> delete bot</a></li>
  </ul>
</div>