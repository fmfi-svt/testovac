c-server
========

Inštalácia produkčného servera:

```
sudo apt-get install apache2 libapache2-mod-php5 php5-cli php5-mysql mysql-server mysql-client texlive texlive-lang-czechslovak texlive-latex-extra

git clone -b c-server-new https://github.com/fmfi-svt/testovac.git

cd testovac

git show origin/h-server:mysql-kodovanie.cnf | sudo tee /etc/mysql/conf.d/mysql-kodovanie.cnf

mysql -u root -p <<< 'CREATE DATABASE testovacdb'

mysql -u root -p <<< 'GRANT ALL ON testovacdb.* TO testovac@localhost IDENTIFIED BY "heslo"'

mysql -u testovac -p testovacdb < schema.sql

cp -i config.template.php config.php
vim config.php

cp -i users.template.php users.php
vim users.php   # staci potom na mieste

mkdir logs
touch logs/log.log
setfacl -m u:www-data:rw logs/log.log

iconv -f cp1250 -t utf8 < uchadzaci.csv > uchadzaci-utf8.csv
php src/importStudents.php uchadzaci-utf8.csv

sudo a2enmod ssl
sudo a2ensite default-ssl
sudo a2dissite 000-default
sudo sed -i 's/^Listen 80/#Listen 80/' /etc/apache2/ports.conf
sudo mv /var/www/html /var/www/html.old
sudo ln -s `pwd`/web /var/www/html
```
