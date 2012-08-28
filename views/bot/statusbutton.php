<div class="btn-group">
  <a id="bot_status_button_<?=$bot->id?>" class="btn btn-mini btn-bot-status btn-<?=$bot->getStatusHTMLClass()?> dropdown-toggle" data-toggle="dropdown" href="#">
    <span id="bot_status_txt_<?=$bot->id?>"><?=$bot->get('status')?></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">
    <? if ($bot->get('status') != 'working' && $bot->get('status') != 'offline'): ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/offline"><i class="icon-pause"></i> take offline</a></li>
    <? elseif ($bot->get('status') == 'offline'): ?>
		  <li><a href="<?=$bot->getUrl()?>/setstatus/idle"><i class="icon-play"></i> bring online</a></li>
		<? endif ?>
		<li><a href="<?=$bot->getUrl()?>/edit"><i class="icon-cog"></i> edit</a></li>
		<li><a href="<?=$bot->getUrl()?>/delete"><i class="icon-remove"></i> delete</a></li>
  </ul>
</div>