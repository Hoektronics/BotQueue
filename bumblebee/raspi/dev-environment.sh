#!/bin/sh

#install slic3r dependencies:
sudo apt-get install -qy git-core build-essential libgtk2.0-dev libwxgtk2.8-dev libwx-perl libmodule-build-perl libnet-dbus-perl cpanminus libextutils-cbuilder-perl gcc-4.7 g++-4.7 libwx-perl libperl-dev
sudo cpanm AAR/Boost-Geometry-Utils-0.14.tar.gz Math::Clipper Math::ConvexHull Math::ConvexHull::MonotoneChain Math::Geometry::Voronoi Math::PlanePath Moo IO::Scalar Class::XSAccessor Growl::GNTP XML::SAX::ExpatXS cpanm PAR::Packer

# install Wx with a fake X framebuffer
sudo apt-get install -qy xvfb
sudo Xvfb :1 &
sudo env DISPLAY=:1 cpanm Wx
sudo killall Xvfb

#install slic3r
git clone https://github.com/alexrj/Slic3r.git
cd Slic3r
git checkout 1.0.0RC1
sudo perl Build.PL 

#compile slic3r binary:
pp slic3r.pl -c -o slic3r -M Method::Generate::BuildAll
