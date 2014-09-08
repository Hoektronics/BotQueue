<h5>Prepare your Pi:</h5>

<ol>
  <li>Download the latest version of Raspbian “wheezy” from the <a href="http://www.raspberrypi.org/downloads">Raspberry Pi website</a>.</li>
  <li>Burn the Raspbian disk image to your SD card using <a href="http://elinux.org/RPi_Easy_SD_Card_Setup">these instructions</a>.</li>
  <li>Insert the SD card into your Pi, connect an HDMI monitor, ethernet or wifi, a keyboard, and then power it up.</li>
</ol>

<h5>On Pi First Boot:</h5>

<p>In the Raspbian config screen, make these changes:</p>

<ol>
  <li>expand_rootfs -> make the partition 100% of the sd card.</li>
  <li>change the password for the pi user</li>
  <li>change the timezone to your current location</li>
  <li>enable the ssh server</li>
  <li>do not start the desktop on boot</li>
  <li>finish and reboot</li>
</ol>

<h5>On Pi Second Boot:</h5>

<ol>
  <li>Log in as the user pi with your previous password</li>
  <li>Confirm that your internet is connected by entering "ping 8.8.8.8" or "ifconfig -a"</li>
  <li>Write down your IP address from the step above.  If you're using ethernet, it will say something like eth0..... inet addr:192.168.0.100</li>
  <li>Open a terminal on your main computer and ssh into the pi. Doing config / install stuff from an SSH session is much easier - you can copy and paste, and you don't need to be physically next to your Pi.<pre>ssh pi@IP.ADDRESS.FROM.BEFORE</pre></li>
  <li>Get screen installed and running. This will put you in a virtual screen.  You can exit by hitting CTRL+A and then CTRL+D.  The whatever you run will continue to run when close the terminal.  You can then rejoin it at any time by running screen -dR botqueue again.  This is also used to run bumblebee, so you can log in and see the command line interface later.<pre>sudo apt-get install screen
screen -dR botqueue</pre></li>
</ol>

<h5>Run these commands to configure your Pi.</h5>
<pre>
#make sure we're on the latest raspbian
sudo apt-get update
sudo apt-get upgrade

#install webcam tools
sudo apt-get install fswebcam uvcdynctrl v4l-utils

#get Botqueue linked up and working on boot.
sudo apt-get install -qy git-core vim screen python-pip
git clone https://github.com/Hoektronics/bumblebee.git
sudo usermod -a -G dialout pi
sudo pip install pyserial Pygments requests requests-oauth

#make botqueue start on boot 
sudo /bin/sh -c 'cat /home/pi/BotQueue/bumblebee/raspi/inittab >> /etc/inittab'
chmod a+x $HOME/BotQueue/bumblebee/raspi/bin/bumblebee
cat $HOME/BotQueue/bumblebee/raspi/profile >> $HOME/.profile
source $HOME/.profile

#authorize our app now.
screen -dR botqueue bumblebee
</pre>