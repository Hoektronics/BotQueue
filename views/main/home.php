<? if (User::isLoggedIn()): ?>
  <? if (empty($bots)): ?>
    <div class="alert alert-success">
      <strong>It looks like you're new here!</strong>  Head over to the <a href="/help">help page</a> for information on getting up and running.  You're going to like it here.
    </div>
  <? endif ?>
	<div class="row">
		<div class="span6">
			<h3>
				Latest Jobs
				:: 1-<?=min(10, $job_count)?> of <?=$job_count?> :: <a href="/jobs">see all</a>
			</h3>
			<?= Controller::byName('job')->renderView('draw_jobs_small', array('jobs' => $jobs)); ?>
		</div>
		<div class="span6">
			<h3>
				Latest Activity
				 :: 1-<?=min(10, $activity_count)?> of <?=$activity_count?> :: <a href="/activity">see all</a>
			</h3>
			<?= Controller::byName('main')->renderView('draw_activities', array('activities' => $activities)); ?>
		</div>
	</div>
	<div class="row">
		<div class="span6">
			<h3>
				My Queues
				 :: 1-<?=min(10, $queue_count)?> of <?=$queue_count?> :: <a href="/queues">see all</a>
			</h3>
			<?= Controller::byName('queue')->renderView('draw_queues', array('queues' => $queues)); ?>
		</div>
		<div class="span6">
			<h3>
				My Bots
				 :: 1-<?=min(10, $bot_count)?> of <?=$bot_count?> :: <a href="/bots">see all</a>
			</h3>
			<?= Controller::byName('bot')->renderView('draw_bots', array('bots' => $bots)); ?>
		</div>
	</div>
	<? if (!empty($errors)): ?>
  	<div class="row">
  	  <div class="span12">
    	  <h3>Error Log</h3>
  	    <?= Controller::byName('main')->renderView('draw_error_log', array('errors' => $errors, 'hide' => 'user'))?>
  	  </div>
  	</div>
  <? endif ?>
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