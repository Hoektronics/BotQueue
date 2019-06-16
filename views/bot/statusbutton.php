<div class="btn-group bot_status_button">
  <a id="bot_status_button_<?php echo $bot->id ?>" class="btn btn-mini btn-bot-status btn-<?php echo BotStatus::getStatusHTMLClass($bot) ?> dropdown-toggle" data-toggle="dropdown" href="#">
    <span id="bot_status_txt_<?php echo $bot->id ?>"><?php echo $bot->getStatus() ?></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu">
    <?php if ($bot->getStatus() == 'working'): ?>
      <li><a href="<?php echo $bot->getUrl() ?>/pause"><i class="icon-pause"></i>pause job</a></li>
      <li><a href="<?php echo $bot->getUrl() ?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <?php elseif ($bot->getStatus() == 'paused'): ?>
      <li><a href="<?php echo $bot->getUrl() ?>/play"><i class="icon-play"></i>resume job</a></li>
      <li><a href="<?php echo $bot->getUrl() ?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <?php elseif ($bot->getStatus() == 'slicing'): ?>
      <li><a href="<?php echo $bot->getUrl() ?>/dropjob"><i class="icon-stop"></i> stop job</a></li>
    <?php elseif ($bot->getStatus() == 'waiting'): ?>
      <li><a href="<?php echo $bot->getCurrentJob()->getUrl() ?>/qa"><i class="icon-check"></i> verify output</a></li>
    <?php elseif ($bot->getStatus() == 'idle'): ?>
		  <li><a href="<?php echo $bot->getUrl() ?>/setstatus/offline"><i class="icon-stop"></i> take offline</a></li>
    <?php elseif ($bot->getStatus() != 'retired'): ?>
		  <li><a href="<?php echo $bot->getUrl() ?>/setstatus/idle"><i class="icon-play"></i> bring online</a></li>
	<?php endif ?>
    <?php if ($bot->getStatus() != 'retired'): ?>
		  <li><a href="<?php echo $bot->getUrl() ?>/edit"><i class="icon-cog"></i> edit bot</a></li>
    <?php endif ?>
		<li><a href="<?php echo $bot->getUrl() ?>/delete"><i class="icon-remove"></i> delete bot</a></li>
    <?php if ($bot->getStatus() == 'offline'): ?>
      <li><a href="<?php echo $bot->getUrl() ?>/retire"><i class="icon-lock"></i> retire bot</a></li>
    <?php endif ?>
  </ul>
</div>