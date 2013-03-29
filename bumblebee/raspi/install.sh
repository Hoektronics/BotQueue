#!/bin/sh

#make sure we're on the latest raspbian
sudo apt-get update -qy
sudo apt-get upgrade -qy

#install botqueue
sudo apt-get install -qy git-core 
git clone git://github.com/Hoektronics/BotQueue.git
sudo apt-get install -qy vim screen python-pip
sudo usermod -a -G dialout pi
sudo pip install pyserial

#get it linked up and working on boot.
mkdir /home/pi/bin
sudo chmod a+x /home/pi/BotQueue/bumblebee/raspi/bumblebee
ln -s /home/pi/BotQueue/bumblebee/raspi/bumblebee /home/pi/bin/bumblebee
sudo /bin/sh -c 'cat /home/pi/BotQueue/bumblebee/raspi/inittab >> /etc/inittab'
cat raspi/profile >> /home/pi/.profile

#authorize our app now.
clear
bumblebee

#install slic3r:
sudo apt-get install -qy git-core build-essential libgtk2.0-dev libwxgtk2.8-dev libwx-perl libmodule-build-perl libnet-dbus-perl cpanminus libextutils-cbuilder-perl gcc-4.7 g++-4.7 libwx-perl
sudo cpanm Boost::Geometry::Utils Math::Clipper Math::ConvexHull Math::Geometry::Voronoi Math::PlanePath Moo Wx Math::ConvexHull::MonotoneChain 
cd /home/pi/
git clone https://github.com/alexrj/Slic3r.git
cd Slic3r
#git checkout
sudo perl Build.PL 
sudo ./Build install

#reboot to make everything cool
sudo reboot