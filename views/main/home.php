<? if (User::isLoggedIn()): ?>
  <div id="Dashtron"><?=Controller::byName('main')->renderView('dashboard')?></div>
  <script>
    $(function() {
        loadDashtron();
    });

    function loadDashtron() {
       console.log("running");
       setTimeout(loadDashtron, 10000);
       $('#Dashtron').load('/ajax/main/dashboard', function() {
         prepare_jobqueue_drag();
       });
    }
  </script>
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