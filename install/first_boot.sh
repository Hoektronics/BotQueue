#!/bin/bash

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

echo "First, we're going to update and install our needed components"
sudo apt-get update
sudo apt-get install -qy apache2 mysql-server php5 php5-mysql libapache2-mod-php5 \
                          php5-dev php-pear make libpcre3-dev php5-curl \
                          libcurl3 libcurl4-gnutls-dev libmagic-dev git-core
sudo pecl install oauth

echo ""

DEFAULT_APP_NAME=BotQueue
read -p "What should we call this install? [$DEFAULT_APP_NAME]: " APP_NAME
[ -z "$APP_NAME" ] && APP_NAME=${DEFAULT_APP_NAME}

git status > /dev/null 2>&1
if [ $? -ne 0 ] ; then
  botqueue_dir="`pwd`/$APP_NAME"
  if [ -d ${botqueue_dir} ] ; then
    echo "I see you already have a $APP_NAME folder. I'll just use that."
  else
    echo "Let me download BotQueue for you"
    git clone git://github.com/Hoektronics/BotQueue.git ${APP_NAME}
  fi
else
  botqueue_dir="`pwd`/.." # We're in the install directory, so one level above
fi

cd "$botqueue_dir"
botqueue_dir=`pwd` #Remove any .. in the path
git checkout 0.5X-dev

echo ""
DEFAULT_OAUTH_PATH="/etc/php5/apache2/conf.d"
read -p "Where should we move the oauth.ini file? [$DEFAULT_OAUTH_PATH]: " OAUTH_PATH
[ -z "$OAUTH_PATH" ] && OAUTH_PATH=${DEFAULT_OAUTH_PATH}
sudo cp install/oauth.ini ${OAUTH_PATH}/oauth.ini

oldPath="/home/ubuntu/BotQueue"
sed -i "s|$oldPath|$botqueue_dir|g" install/apache.conf

echo ""
DEFAULT_APACHE_PATH="/etc/apache2/sites-available"
read -p "Where should we add the $APP_NAME apache config? [$DEFAULT_APACHE_PATH]: " APACHE_PATH
[ -z "$APACHE_PATH" ] && APACHE_PATH=${DEFAULT_APACHE_PATH}
APACHE_CONF=${APACHE_PATH}/${APP_NAME}
sudo cp install/apache.conf ${APACHE_CONF}

echo ""
echo "I'm going to launch a terminal to let you edit the hostname and alias."
echo "You might want to also remove the SSL section if you aren't using it, "
echo "or if you want to change the paths to the server certificates."
echo ""
echo "Press any key to continue"
read -n 1 -s

[ -z "$EDITOR" ] && EDITOR=vi

sudo "$EDITOR" ${APACHE_CONF}

echo ""
echo "Note, that if you have not created the database, give the username"
echo "and password of a user who can create the database"
read -p "Database name [$APP_NAME]: " database_name
[ -z "$database_name" ] && database_name=${APP_NAME}
read -p "Database user [root]: " database_user
database_user=${database_user:-root}
read -s -p "Database password (Won't be shown) []: " database_pass
database_pass=${database_pass:-""}
echo "";

echo "";
if [ -n "$database_pass" ]; then
  echo "Creating the database if it doesn't exist"
  mysql -u ${database_user} -p${database_pass} -e "CREATE DATABASE IF NOT EXISTS ${database_name}"
  echo "Importing the database"
  mysql -u ${database_user} -p${database_pass} ${database_name} < install/createdb.sql
else
  echo "Creating the database if it doesn't exist"
  mysql -u ${database_user} -e "CREATE DATABASE IF NOT EXISTS ${database_name}"
  echo "Importing the database"
  mysql -u ${database_user} ${database_name} < install/createdb.sql
fi

cp extensions/config-example.php extensions/config.php

echo ""
echo "I'm about to let you edit the config file for the installation"
echo "If you need to edit it again, it's located at:"
echo "${botqueue_dir}/extensions/config.php"
echo ""
echo "Press any key to continue"
read -n 1 -s
"$EDITOR" extensions/config.php

echo "Downloading composer"
php -r "readfile('https://getcomposer.org/installer');" | php

echo "Downloading components"
php composer.phar install


echo "Enabling site $APP_NAME"
sudo a2ensite ${APP_NAME} # reload apache for this
sudo a2enmod rewrite # restart for this
sudo service apache2 restart

echo "Done!"
