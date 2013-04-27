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
    <h1>User Leaderboard</h1>
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
            <td><?=$row['total']?></td>
        <? endforeach ?>
      </tbody>
    </table>
  </div>
  <div class="span6">
    <h1>Bot Leaderboard</h1>
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
            <td><?=$row['total']?></td>
        <? endforeach ?>
      </tbody>
    </table>
  </div>
</div>