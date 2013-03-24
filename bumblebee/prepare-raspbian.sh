#!/bin/sh

sudo apt-get update
sudo apt-get install git-core vim screen
git clone git://github.com/Hoektronics/BotQueue.git

sudo usermod -a -G dialout pi
sudo apt-get install python-pip
sudo pip install pyserial

screen -dR botqueue


#installing slic3r:

sudo apt-get install git build-essential libgtk2.0-dev libwxgtk2.8-dev libwx-perl libmodule-build-perl libnet-dbus-perl
sudo apt-get install cpanminus
sudo apt-get install libextutils-cbuilder-perl
sudo apt-get install gcc-4.7
sudo apt-get install g++-4.7
sudo apt-get install libwx-perl
sudo cpanm Boost::Geometry::Utils Math::Clipper Math::ConvexHull Math::Geometry::Voronoi Math::PlanePath Moo Wx Math::ConvexHull::MonotoneChain 
git clone https://github.com/alexrj/Slic3r.git
cd Slic3r
git checkout 0.9.8
sudo perl Build.PL 