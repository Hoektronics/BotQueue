#!/bin/sh

sudo apt-get update
sudo apt-get install git-core vim screen
git clone git://github.com/Hoektronics/BotQueue.git

sudo usermod -a -G dialout pi
sudo apt-get install python-pip
sudo pip install pyserial

screen -dR botqueue