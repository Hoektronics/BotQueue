<?php $i = 1 ?>
<div class="row">
	<div class="span8">
		<h2>How Do I Get Started?</h2>

		<h4>Prerequisites:</h4>
		<ul>
			<li>A <em>reliable</em> 3D printer. BotQueue is designed for printing dozens or hundreds of jobs.</li>
			<li>An <em>automated</em> 3D printer. You should not need to touch your machine after hitting print.</li>
			<li>A <em>supported</em> 3D printer running software like GRBL, Sprinter, Marlin, or Teacup.</li>
			<li><strong>Mac or Linux preferred.</strong> Never been tested on Windows. Likely broken. Testers/Devs
				wanted.
			</li>
		</ul>

		<h4>Step <?php echo $i++ ?>: Register an account</h4>

		<p>Super easy - just visit the <a href="/register">register</a> page and sign up. Totally free.</p>


		<h4>Step <?php echo $i++ ?>: Install BumbleBee, the client software</h4>

		<p>
		</p>

		<div class="tabbable"> <!-- Only required for left/right tabs -->
			<ul class="nav nav-tabs install_tabs">
				<li class="active"><a href="#linux_install" data-toggle="tab">Linux Instructions</a></li>
				<li><a href="#raspi_install" data-toggle="tab">Raspberry Pi Instructions</a></li>
				<li><a href="#osx_install" data-toggle="tab">OSX Instructions</a></li>
				<li><a href="#windows_install" data-toggle="tab">Windows Instructions</a></li>
			</ul>

			<div class="tab-content install_tabs">
				<div class="tab-pane active" id="linux_install">
					<h5>Install These Programs:</h5>
					<pre>sudo apt-get install fswebcam uvcdynctrl v4l-utils</pre>

					<h5>Install The Client:</h5>
					<pre>pip install bqclient</pre>

					<h5>Run it!</h5>
					<pre>bumblebee</pre>
				</div>

				<div class="tab-pane" id="osx_install">
					<?php echo Controller::byName('help')->renderView('osx_installation') ?>
				</div>

				<div class="tab-pane" id="raspi_install">
					<?php echo Controller::byName('help')->renderView('raspberry_pi_installation') ?>
				</div>

				<div class="tab-pane" id="windows_install">
					<span class="text-error">Windows is not currently supported by BotQueue - patches welcome!</span>
				</div>
			</div>
		</div>

		<h4>Step <?php echo $i++ ?>: Authorize the client software</h4>

		<p>From the command line, run this command: <strong>bumblebee</strong></p>

		<p>If you are on the same internet-facing IP (such as on most home networks) then when you visit the main
			botqueue.com dashboard, it will show you that an app is requesting access. Accept it and give the app a name
			that will identify the computer that it is running on.</p>

		<p>If you are not on the same ip, the script in the terminal will give you a link that you can visit to
			authorize the app to access your account.</p>


		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Important:</strong> the Bumblebee client will scan all of your serial ports and attached cameras. It
			will upload one picture from each camera to make selecting the right webcam easy. After granting access to
			the app, please wait 1-2 minutes for this scan data to upload to the site.
		</div>

		<h4>Step <?php echo $i++ ?>: Register and Configure your Bots</h4>

		<p>Once you <a href="/bot/register">register a bot</a>, configure the slicer and driver settings. Follow the
			instructions in the bot config area for more details. No need to touch Bumblebee after you config your bot.
			The new config will be automatically downloaded.</p>

		<h4>Step <?php echo $i++ ?>: Bring your Bot Online</h4>

		<p>
			Bots default to the offline mode so that you have time to configure it. From the dashboard, select the
			'bring online' option from the bot dropdown. You now have a network linked 3D printer. If you want it to
			stop processing jobs, use the same dropdown to take it offline again.
		</p>

		<h4>Step <?php echo $i++ ?>: Upload a job to BotQueue</h4>

		<p>
			Next, <a href="/upload">upload STL or GCode files to the site.</a> These files will be automatically
			downloaded and executed by any eligible machines. If your client software is running, it will automatically
			download and process uploaded jobs.
		</p>

		<h4>Step <?php echo $i++ ?>: Removing a print upon completion</h4>

		<p>
			When a job is complete is done, you must go to the Botqueue site to go through the output verification (QA)
			process. It will request that you remove the print and confirm that the print was successful. If it is not
			successful, the bot will go offline and not take any more jobs. If the print is successful, the bot will
			look at the queue for new jobs, grab the next available one, and immediately begin printing it.
		</p>
	</div>
	<div class="span4">
		<h2>Getting Further Help</h2>

		<p>
			This project is a labor of love by <a href="http://www.zachhoeken.com">Zach Hoeken</a> and <a href="http://blog.jnesselr.org/">Justin Nesselrotte</a>. Both the website and client code are 100% open source.
		</p>

		<p>
			This project is considered mostly stable, but new changes can always break things. If you are stuck and can't get it
			working, try some of the places below for help. You'll probably have better luck getting in touch with Justin (jnesselr)
			than with Zach (hoeken).
		</p>
		<ul>
			<li><a href="https://groups.google.com/d/forum/botqueue">Google Group / Mailing List</a></li>
			<li><a href="irc://irc.freenode.net/botqueue">irc.freenode.net #botqueue</a></li>
			<li>Follow us on twitter, <a href="http://twitter.com/hoeken">@hoeken</a> and <a href="http://twitter.com/jnesselr">@jnesselr</a></li>
		</ul>
	</div>
</div>