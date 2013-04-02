#!/bin/sh

#make sure we're on the latest raspbian
sudo apt-get update -qy
sudo apt-get upgrade -qy

#install botqueue
sudo apt-get install -qy git-core vim screen python-pip
git clone git://github.com/Hoektronics/BotQueue.git
sudo usermod -a -G dialout pi
sudo pip install pyserial

#install slic3r:
sudo apt-get install -qy git-core build-essential libgtk2.0-dev libwxgtk2.8-dev libwx-perl libmodule-build-perl libnet-dbus-perl cpanminus libextutils-cbuilder-perl gcc-4.7 g++-4.7 libwx-perl
sudo cpanm Boost::Geometry::Utils Math::Clipper Math::ConvexHull Math::Geometry::Voronoi Math::PlanePath Moo Wx Math::ConvexHull::MonotoneChain 
git clone https://github.com/alexrj/Slic3r.git
cd Slic3r
git checkout 0.9.9
sudo perl Build.PL 
sudo ./Build install

#get it linked up and working on boot.
sudo chmod a+x $HOME/BotQueue/bumblebee/raspi/bin/bumblebee
sudo /bin/sh -c 'cat $HOME/BotQueue/bumblebee/raspi/inittab >> /etc/inittab'
cat $HOME/BotQueue/bumblebee/raspi/profile >> $HOME/.profile
source $HOME/profile

#authorize our app now.
clear
screen -dR botqueue bumblebee

#reboot to make everything cool
sudo reboot