/bin/sh

#    This file is part of BotQueue.
#
#    BotQueue is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    BotQueue is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  
sudo apt-get update
#TODO:Should these be passed in -y?
sudo apt-get install git apache2 php5-mysql php-pear build-essential libpcre3-dev php5-curl
sudo apt-get install libcurl3 php5-dev libcurl4-gnutls-dev libmagic-dev

#TODO:the following 6 commands will fail if they're already installed
sudo pecl install oauth
sudo pecl install pecl_http

sudo pear channel-discover pear.amazonwebservices.com
sudo pear channel-discover guzzlephp.org/pear
sudo pear channel-discover pear.symfony.com
sudo pear install aws/sdk

git status > /dev/null 2>&1
if [ $? -ne 0 ] ; then
  git clone git://github.com/Hoektronics/BotQueue.git
  botqueue_dir="`pwd`/BotQueue"
else
  botqueue_dir="`pwd`/.." # We're in the install directory, so one level above
fi

cd "$botqueue_dir"
botqueue_dir=`pwd` #Remove any .. in the path
sudo cp install/oauth.ini /etc/php5/apache2/conf.d/oauth.ini
sudo cp install/apache.conf /etc/apache2/sites-available/BotQueue
$oldPath="/home/ubuntu/BotQueue"
$oldPath="${oldPath//\//\/}" # Replace / with \/
$newPath="${botqueue_dir//\//\\/}" # Replace / with \/
sed -i "s/$oldPath/$newPath/g" /etc/apache2/sites-available/BotQueue
#TODO: Figure out how to change host name as well as remove serverAlias if not needed

sudo a2ensite BotQueue
sudo a2enmod rewrite

sudo /etc/init.d/apache2 reload
sudo /etc/init.d/apache2 restart

#TODO:fix issue with database name not specified
#TODO:fix issue with root user having a passward
mysql -u root < install/createdb.sql

#TODO:Change apache.conf file in sites-available to reflect a possibly different host than http://botqueue.com
#TODO:Change config.php to reflect host name, database name, user, and password changes here
cp extensions/config-example.php extensions/config.php
#TODO:Change client config in bumblebee to reflect this hostname

