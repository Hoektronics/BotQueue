<div class="row">
  <div class="span3">
    <h1><?=$total_active_bots?></h1>
    total active bots
  </div>
  <div class="span3">
    <h1><?=$total_pending_jobs?></h1>
      total pending jobs
  </div>
  <div class="span3">
    <h1><?=$total_completed_jobs?></h1>
      total completed jobs
  </div>
  <div class="span3">
    <h1><?=$total_printing_time?></h1>
    total hours of printing time
  </div>
</div>

<? if (User::isLoggedIn()): ?>
  <br/><br/>
  <h1>My BotQueue.com Stats</h1>
  <div class="row">
    <div class="span3">
      <h1><?=$my_total_active_bots?></h1>
      total active bots
    </div>
    <div class="span3">
      <h1><?=$my_total_pending_jobs?></h1>
        total pending jobs
    </div>
    <div class="span3">
      <h1><?=$my_total_completed_jobs?></h1>
        total completed jobs
    </div>
    <div class="span3">
      <h1><?=$my_total_printing_time?></h1>
      total hours of printing time
    </div>
  </div>
<? endif ?>

<br/><br/>

<div class="row">
  <div class="span6">
    <div class="tabbable"> <!-- Only required for left/right tabs -->
      <div style="position: relative;">
        <h1 class="pull-left">User Leaderboard</h1>
        <ul class="nav nav-pills pull-right" style="margin-bottom: 0px; position: absolute; right: 0px; bottom: 12px;">
          <li class="active"><a href="#user_alltime" data-toggle="tab">All Time</a></li>
          <li><a href="#user_lastmonth" data-toggle="tab">Last Month</a></li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="tab-content">
        <div class="tab-pane" id="user_lastmonth">
          <table class="table table-striped table-bordered table-condensed">
          	<tbody>
              <tr>
                <th style="width: 50px">Rank</th>
                <th>User</th>
                <th>Hours</th>
              </tr>
              <? $rank=0 ?>
              <? foreach ($user_leaderboard_30 AS $row): ?>
                <? $rank++ ?>
                <? $user = new User($row['user_id']) ?>
                <tr <?= ($user->id == User::$me->id) ? 'class="success"' : ''?>>
                  <td><?=$rank?></td>
                  <td><?=$user->getName()?></td>
                  <td><?=$row['hours']?></td>
              <? endforeach ?>
            </tbody>
          </table>
        </div>
        <div class="tab-pane active" id="user_alltime">
          <table class="table table-striped table-bordered table-condensed">
          	<tbody>
              <tr>
                <th style="width: 50px">Rank</th>
                <th>User</th>
                <th>Hours</th>
              </tr>
              <? $rank=0 ?>
              <? foreach ($user_leaderboard AS $row): ?>
                <? $rank++ ?>
                <? $user = new User($row['user_id']) ?>
                <tr <?= ($user->id == User::$me->id) ? 'class="success"' : ''?>>
                  <td><?=$rank?></td>
                  <td><?=$user->getName()?></td>
                  <td><?=$row['hours']?></td>
              <? endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  
  <div class="span6">
    <div class="tabbable"> <!-- Only required for left/right tabs -->
      <div style="position: relative;">
        <h1 class="pull-left">Bot Leaderboard</h1>
        <ul class="nav nav-pills pull-right" style="margin-bottom: 0px; position: absolute; right: 0px; bottom: 12px;">
          <li class="active"><a href="#bot_alltime" data-toggle="tab">All Time</a></li>
          <li><a href="#bot_lastmonth" data-toggle="tab">Last Month</a></li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="tab-content">
        <div class="tab-pane" id="bot_lastmonth">
          <table class="table table-striped table-bordered table-condensed">
          	<tbody>
              <tr>
                <th style="width: 50px">Rank</th>
                <th>Bot</th>
                <th>Owner</th>
                <th>Hours</th>
              </tr>
              <? $rank=0 ?>
              <? foreach ($bot_leaderboard_30 AS $row): ?>
                <? $rank++ ?>
                <? $bot = new Bot($row['bot_id']) ?>
                <tr <?= ($bot->get('user_id') == User::$me->id) ? 'class="success"' : ''?>>
                  <td><?=$rank?></td>
                  <td><?=$bot->getName()?></td>
                  <td><?=$bot->getUser()->getName()?></td>
                  <td><?=$row['hours']?></td>
              <? endforeach ?>
            </tbody>
          </table>
        </div>
        <div class="tab-pane active" id="bot_alltime">
          <table class="table table-striped table-bordered table-condensed">
          	<tbody>
              <tr>
                <th style="width: 50px">Rank</th>
                <th>Bot</th>
                <th>Owner</th>
                <th>Hours</th>
              </tr>
              <? $rank=0 ?>
              <? foreach ($bot_leaderboard AS $row): ?>
                <? $rank++ ?>
                <? $bot = new Bot($row['bot_id']) ?>
                <tr <?= ($bot->get('user_id') == User::$me->id) ? 'class="success"' : ''?>>
                  <td><?=$rank?></td>
                  <td><?=$bot->getName()?></td>
                  <td><?=$bot->getUser()->getName()?></td>
                  <td><?=$row['hours']?></td>
              <? endforeach ?>
            </tbody>
          </table>        </div>
      </div>
    </div>
  </div>
</div>