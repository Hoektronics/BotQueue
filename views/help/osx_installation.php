<h5>Download Bumblebee</h5>
    		  <p>
You can use the <a href="http://dl.botqueue.com/bumblebee">official release</a> or run the cutting edge client from <a href="https://github.com/Hoektronics/BotQueue">git.</a>
    		  </p>
				<h5>Verify python version is at least > 2.6.3, preferably 2.7</h5>
<pre>python --version</pre>
				<h5>If it is not the correct version, then follow these steps:</h5>
<ol>
	<li>Download the latest python 2.7.X version from <a href="http://www.python.org/downloads/">here</a> for mac OSX 10.6 and later</li>
	<li>Open the disk image, and run Python.mpkg</li>
	<li>Check the python version again (You may have to re-open your terminal window)</li>
	<li>Grab ez_setup.py from <a href="http://peak.telecommunity.com/dist/ez_setup.py">here</a>.</li>
	<li>In a terminal, run "sudo python ez_setup.py" in the directory where you stored ez_setup.py</li>
	<li>Your python version should be correct. Simply run the commands below</li>
</ol>
    			<h5>Install These Libraries:</h5>
<pre>sudo easy_install pip
sudo pip install Pygments pyserial requests requests-oauth</pre>
