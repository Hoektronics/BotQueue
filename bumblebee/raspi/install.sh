#!/bin/sh

#make sure we're on the latest raspbian
sudo apt-get update -qy
sudo apt-get upgrade -qy

#install slic3r dependencies:
sudo apt-get install -qy git-core build-essential libgtk2.0-dev libwxgtk2.8-dev libwx-perl libmodule-build-perl libnet-dbus-perl cpanminus libextutils-cbuilder-perl gcc-4.7 g++-4.7 libwx-perl
#sudo cpanm Wx Boost::Geometry::Utils Encode::Locale File::Basename File::Spec Getopt::Long Math::Clipper Math::ConvexHull Math::ConvexHull::MonotoneChain Math::Geometry::Voronoi Math::PlanePath Moo Scalar::Util Time::HiRes Test::More IO::Scalar
sudo cpanm AAR/Boost-Geometry-Utils-0.06.tar.gz Math::Clipper Math::ConvexHull Math::ConvexHull::MonotoneChain Math::Geometry::Voronoi Math::PlanePath Moo IO::Scalar Class::XSAccessor Growl::GNTP XML::SAX::ExpatXS

# install Wx with a fake X framebuffer
sudo apt-get install -qy xvfb
sudo Xvfb :1 &
sudo env DISPLAY=:1 cpanm Wx
sudo killall Xvfb

#install slic3r
git clone https://github.com/alexrj/Slic3r.git
cd Slic3r
git checkout 0.9.9
sudo perl Build.PL 
sudo ./Build install

#get Botqueue linked up and working on boot.
sudo apt-get install -qy git-core vim screen python-pip
git clone git://github.com/Hoektronics/BotQueue.git
sudo usermod -a -G dialout pi
sudo pip install pyserial
sudo /bin/sh -c 'cat /home/pi/BotQueue/bumblebee/raspi/inittab >> /etc/inittab'
chmod a+x $HOME/BotQueue/bumblebee/raspi/bin/bumblebee
cat $HOME/BotQueue/bumblebee/raspi/profile >> $HOME/.profile
source $HOME/.profile

#authorize our app now.
clear
screen -dR botqueue bumblebee

#reboot to make everything cool
sudo reboot