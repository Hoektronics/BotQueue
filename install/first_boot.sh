#/bin/bash
sudo apt-get update
sudo apt-get install git apache2 php5-mysql

git clone git://github.com/Hoektronics/BotQueue.git

sudo cp ~/BotQueue/install/apache.conf /etc/apache2/sites-available/BotQueue
sudo a2ensite BotQueue
sudo a2enmod rewrite

sudo /etc/init.d/apache2 restart
