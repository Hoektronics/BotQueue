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