<div class="row">
	<div class="span8">
		<h2>How Do I Get Started?</h2>

		<h4>Prerequisites:</h4>
		<ul>
			<li>A <em>reliable</em> 3D printer.  BotQueue is designed for printing dozens or hundreds of jobs.</li>
			<li>An <em>automated</em> 3D printer.  You should not need to touch your machine after hitting print.</li>
			<li>A <em>supported</em> 3D printer running software like GRBL, Sprinter, Marlin, or Teacup.</li>
			<li><strong>Mac or Linux preferred.</strong>  Never been tested on Windows.  Likely broken. Testers/Devs wanted.</li>
		</ul>

		<h4>Step 1: Register an account</h4>
		<p>Visit the <a href="/register">register</a> page and sign up.</p>


		<h4>Step 2: Register your Bots</h4>
		<p>
			On the BotQueue.com website, <a href="/bot/register">register a new bot</a>.  Give it a unique name.  If you want to use the integrated Slic3r support, you'll need to choose the slic3r version and upload your config.  From slic3r, click File-&gt;Export Config.  Upload that as your slice config.  Once you do that, you can upload STL files directly to the site and they will be sliced before printing.
		</p>
		
		<h4>Step 3: Download BumbleBee, the client software</h4>
		<p>
		  Download the <a href="http://dl.botqueue.com/bumblebee">official release</a> or run the cutting edge client from <a href="https://github.com/Hoektronics/BotQueue">git</a>
		</p>
		<p>
		  If you are running bumblebee on a Raspberry Pi, see these <a href="/help/raspberry-pi-installation">installation instructions</a>.
		</p>

		<h4>Step 4: Authorize the client software</h4>
		<p>From the command line, navigate to the <strong>bumblebee</strong> folder and run this command: <strong>python bumblebee.py</strong></p>
		<p>It should give you a url where you authorize the app to access your account.  Take the code that page gives you and enter it into the terminal.</p>

		<h4>Step 5: Configure your bots</h4>
		<p>
			  Now, edit the <strong>bumblebee/config.json</strong> file and modify the file with your particular bot information.  After you modify this file, you will need to restart the client software by quitting and re-running it.
		</p>
		<p>
		  You can exit it by hitting <strong>q</strong> or <strong>Ctrl+c</strong>.  Re-start it by running <strong>python bumblebee.py</strong>.  This is required because it only pulls down the list of bots from the botqueue.com website once, during the startup of the program.
	  </p>
	  
	  <h5>Webcam Support</h5>
	  <p>
	    Bumblebee supports USB webcams, and its easy to configure bumblebee to find the right camera.
	  </p>
	  <p>
	    To find your camera location in OSX, you can open photobooth and copy the string from the Camera dropdown menu, or you can open a terminal, navigate to the bumblebee directory and run <b>./imagesnap -l</b> which will give you a list of available cameras.<br/>
	  </p>
	  <p>
	    On Linux, you'll need to install the <b>fswebcam</b> program.  To get a list of your webcams, open a terminal and run <b>ls /dev/video*</b>.  Enter the webcam name in the configuration file as shown below.  If no webcam option is listed, bumblebee will not attempt to take or upload a photo.
	  </p>
	  
		<h5>A typical RepRap configuration with 2 machines might look like this:</h5>
    <div class="alert alert-info">
      <strong>Please note:</strong> the worker name must exactly match the name of the bot you registered on the website, or the client software will not attempt to control the machine.  The reason for this is that you can use multiple machines to control different bots (for example you have 10 machines in one room, and 10 machines in another room, each with their own control computer)
    </div>

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
		"baud" : "115200",
		"webcam": {
      "device": "/dev/video0"
    }
	},
	{
		"name": "Prusa",
		"driver" : "printcore",
		"port" : "/dev/ttyACM0",
		"baud" : "115200",
		"webcam": {
      "device": "/dev/video1"
    }
	}
 ]
}</pre>

		<h4>Step 6: Upload a job to BotQueue</h4>
		<p>
			You can <a href="/upload">upload STL or GCode files to the site</a> and then they will be automatically downloaded and sliced.  Warning: If your client software is running, it will automatically download and process jobs.
		</p>

		<h4>Step 7: Enjoy watching your bot work.</h4>
		<p>
			You now have a network linked 3D printer with a print queue.  It will make you things.  Sit back and enjoy.  I recommend a cold one.
		</p>

		<h4>Step 8: Removing a print upon completion</h4>
		<p>
			When a job is complete is done, you must go to the Botqueue site to go through the output verification (QA) process. It will request that you remove the print and confirm that the print was successful.  If it is not successful, the bot will go offline and not take any more jobs.  If the print is successful, the bot will look at the queue for new jobs, grab the next available one, and immediately begin printing it.
		</p>
	</div>
	<div class="span4">
		<h2>Getting Help</h2>
		<p>
			This project is a labor of love by yours truly, <a href="http://www.zachhoeken.com">Zach Hoeken</a>. Both the website and client code are 100% open source.
		</p>
		<p>It is also a new project, so it will probably be buggy.  If you are stuck and can't get it working, try some of the places below for help.  If you're a dev and don't mind getting your hands dirty, I would love to take commits and pull requests.
		</p>
		<ul>
			<li><a href="https://groups.google.com/d/forum/botqueue">Google Group  / Mailing List</a></li>
			<li><a href="irc://irc.freenode.net/botqueue">irc.freenode.net #botqueue</a></li>
			<li><a href="https://github.com/Hoektronics/BotQueue">Github</a></li>
			<li><a href="http://twitter.com/hoeken">Hit me up on Twitter: @hoeken</a></li>
			<li><a href="http://www.hoektronics.com">Follow the blog to keep up to date</a></li>
		</ul>
	</div>
</div>