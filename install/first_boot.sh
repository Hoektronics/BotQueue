#!/bin/sh

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
sudo apt-get install git apache2 php5-mysql php-pear build-essential libpcre3-dev php5-curl

sudo pecl install oauth

git clone git://github.com/Hoektronics/BotQueue.git
sudo cp ~/BotQueue/install/oauth.ini /etc/php5/apache2/conf.d/oauth.ini

sudo cp ~/BotQueue/install/apache.conf /etc/apache2/sites-available/BotQueue
sudo a2ensite BotQueue
sudo a2enmod rewrite

sudo /etc/init.d/apache2 reload
sudo /etc/init.d/apache2 restart

mysql -u root < install/createdb.sql