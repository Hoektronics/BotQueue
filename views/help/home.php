<div class="row">
	<div class="span8">
		<h2>How Do I Get Started?</h2>

		<h4>Prerequisites:</h4>
		<ul>
			<li>A reliable 3D printer.  BotQueue is designed for printing dozens or hundreds of jobs.</li>
			<li>An automated 3D printer.  You should not need to touch your machine after hitting print until the job is done.</li>
			<li>A 3D printer running supported software like GRBL, Sprinter, Marlin, or MakerBot 5D (eg. Replicator)</li>
			<li>Mac or Linux preferred.  Never been tested on Windows.  Likely broken. Testers/Devs wanted.</li>
		</ul>

		<h4>Step 1: Register an account</h4>
		<p>Visit the <a href="/register">register</a> page and sign up.</p>

		<h4>Step 2: Download BumbleBee, the client software</h4>
		<p>There will be official releases in the future, but for now you should pull down the <a href="https://github.com/Hoektronics/BotQueue">git repository</a>.</p>

		<h4>Step 3: Authorize the client software</h4>
		<p>From the command line, navigate to the <strong>BotQueue/bumblebee</strong> folder and run this command: <strong>python bumblebee.py</strong></p>
		<p>It should spawn a web browser and take you to the BotQueue.com page where you authorize the app to access your account.  Take the code it gives you and enter it into the terminal.</p>

		<h4>Step 4: Configure your bots</h4>
		<p>
			On the botqueue.com website, <a href="/bot/register">register a new bot</a>.  Give it a unique name.  Now, edit the <strong>BotQueue/bumblebee/config.json</strong> file and modify the file with your particular bot information.  After you modify this file, you will need to restart the client software by quitting and re-running it.
	
		<h5>A typical RepRap configuration with 2 machines might look like this:</h5>
<pre>{
 "app": {
  "consumer_secret": "ffffffffffffffffffffffffffffffffffffffff", 
  "consumer_key": "ffffffffffffffffffffffffffffffffffffffff", 
  "token_secret": "ffffffffffffffffffffffffffffffffffffffff", 
  "token_key": "ffffffffffffffffffffffffffffffffffffffff"
 }, 
 "workers": [
	{
		"name": "MendelMax",
		"driver" : "printcore",
		"port" : "/dev/tty.usbmodem123",
		"baud" : "250000"
	},
	{
		"name": "Prusa",
		"driver" : "printcore",
		"port" : "/dev/tty.usbmodem345",
		"baud" : "250000"
	}
 ]
}</pre>
      <div class="alert alert-info">
        <strong>Please note:</strong> the worker name must exactly match the name of the bot you registered on the website, or the client software will not attempt to control the machine.  The reason for this is that you can use multiple machines to control different bots (for example you have 10 machines in one room, and 10 machines in another room, each with their own control computer)
      </div>
		</p>	

    <h4>Step 5: Restart the client software</h4>
    <p>
      You can exit it by hitting <strong>q</strong> or <strong>Ctrl+c</strong>.  Re-start it by running <strong>python bumblebee.py</strong>.  This is required because it only pulls down the list of bots from the botqueue.com website once, during the startup of the program.
    </p>

		<h4>Step 6: Upload a job to BotQueue</h4>
		<p>
			Slice an STL into GCode using your favorite slicer such as Skeinforge, Slic3r, or any of the other software pieces out there.  Then visit our upload page and upload your .gcode file to the site.  Warning: If your client software is running, it will automatically download and process jobs.
		</p>

		<h4>Step 7: Enjoy watching your bot work.</h4>
		<p>
			You now have a network linked 3D printer with a print queue.  It will make you things.  Sit back and enjoy.  I recommend a cold one.
		</p>

		<h4>Step 8: Remove print upon completion</h4>
		<p>
			Once your bot is done, a web browser window will open and guide you through the output verification (QA) process. It will request that you remove the print and confirm that the print was successful.  If it is not successful, the bot will go offline and not take any more jobs.  If the print is successful, the bot will look at the queue for new jobs, grab the next available one, and immediately begin printing it.
		</p>
	</div>
	<div class="span4">
		<h2>Getting Help</h2>
		<p>
			This project is a labor of love by yours truly, <a href="http://www.zachhoeken.com">Zach Hoeken</a>. Both the website and client code are 100% open source.
		</p>
		<p>It is also a very new project, so it will probably be buggy.  If you are stuck and can't get it working, try some of the places below for help.  If you're a dev and don't mind getting your hands dirty, I would love to take commits and pull requests.
		</p>
		<ul>
			<li><a href="https://groups.google.com/d/forum/botqueue">Google Group  / Mailing List</a></li>
			<li><a href="irc://irc.freenode.net/botqueue">irc.freenode.net #botqueue</a></li>
			<li><a href="http://twitter.com/hoeken">Hit me up on Twitter: @hoeken</a></li>
			<li><a href="http://www.hoektronics.com">Follow the blog to keep up to date</a></li>
		</ul>
	</div>
</div>