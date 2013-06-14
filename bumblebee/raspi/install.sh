#!/bin/sh

#make sure we're on the latest raspbian
sudo apt-get update -qy
sudo apt-get upgrade -qy

#git is ridiculously important to us
sudo apt-get install -qy git-core

#install rpi-update to update our firmware
sudo wget http://goo.gl/1BOfJ -O /usr/bin/rpi-update
sudo chmod +x /usr/bin/rpi-update
sudo /usr/bin/rpi-update

#install webcam tools
#sudo apt-get install uvcdynctrl
sudo apt-get install fswebcam 

#get Botqueue linked up and working on boot.
sudo apt-get install -qy git-core vim screen python-pip
git clone git://github.com/Hoektronics/BotQueue.git
sudo usermod -a -G dialout pi
sudo pip install pyserial Pygments requests requests-oauth
sudo /bin/sh -c 'cat /home/pi/BotQueue/bumblebee/raspi/inittab >> /etc/inittab'
chmod a+x $HOME/BotQueue/bumblebee/raspi/bin/bumblebee
cat $HOME/BotQueue/bumblebee/raspi/profile >> $HOME/.profile
source $HOME/.profile

#authorize our app now.
clear
screen -dR botqueue bumblebee