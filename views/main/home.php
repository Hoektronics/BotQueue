<? if (User::isLoggedIn()): ?>
  <? $style = User::$me->get('dashboard_style') ?>
  <div id="DashtronController">
    <h3 class="pull-left" style="margin: 0px;">Live Dashboard
      <a class="btn btn-primary" href="/upload">Create Job</a>
      <a class="btn btn-primary" href="/bot/register">Register Bot</a>
    </h3>
    
    <form class="form-inline pull-right muted">
      <label for="autoload_dashboard" style="display: inline">Auto-refresh?</label>
      <input type="checkbox" id="autoload_dashboard" value="1" checked="1">
      <label for="dashboard_style"></label>
      <select id="dashboard_style" onchange="loadDashtron()">
        <option value="large_thumbnails" <?= ($style == 'large_thumbnails') ? 'selected' : ''?>>Large Thumbnails</option>
        <option value="medium_thumbnails" <?= ($style == 'medium_thumbnails') ? 'selected' : ''?>>Medium Thumbnails</option>
        <option value="small_thumbnails" <?= ($style == 'small_thumbnails') ? 'selected' : ''?>>Small Thumbnails</option>
        <option value="list" <?= ($style == 'list') ? 'selected' : ''?>>Detailed List</option>
      </select>
    </form>
    <div class="clearfix"></div>
  </div>
  <div id="Dashtron"><?=Controller::byName('main')->renderView('dashboard')?></div>

<? else: ?>
  <div class="hero-unit">
    <h1>BotQueue has arrived!</h1>
    <p>The open source, distributed fabrication software you've been dreaming about. Srsly.</p>
    <p>
      <img src="/img/botqueue.png" width="1013" height="403" align="center">
    </p>
    <h3>Okay, so what does that mean?</h3>
    <p>
      Simple.  BotQueue lets you control multiple 3D printers through the Internet and turn them into your own manufacturing center.  Think cloud-based computing, but for making things in the real world.  Now you can build the robot army you've always dreamed of!  Oh yeah, and its 100% open source because that's how I roll. 
    </p>
    <h3>Want to learn more?</h3>
    <p>
      Check out the <a href="http://www.hoektronics.com/2012/09/13/introducing-botqueue-open-distributed-manufacturing/">blog entry about the launch of BotQueue</a>.
    </p>
  </div>
<? endif ?>