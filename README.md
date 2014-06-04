Testovač EPV
------------

Quick start:


### Systémové závislosti

* Python 2.7
* Python headre (`apt-get install python-dev` alebo podobne)
* virtualenv (`apt-get install python-virtualenv`)
* TeX (na generovanie PDF) (`sudo apt-get install texlive texlive-lang-czechslovak texlive-latex-extra`)

A ak chcete MySQL namiesto SQLite:

* MySQL server (`apt-get install mysql-server` a zapnúť ho)
* MySQL headre (`apt-get install libmysqlclient-dev`)
* databáza (`CREATE DATABASE testovacdb`)
* užívateľ (`GRANT ALL ON testovacdb.* TO testovac@localhost IDENTIFIED BY 'heslo'`)


### Environment a projektové závislosti

    virtualenv venv
    source venv/bin/activate
    pip install werkzeug jinja2 sqlalchemy
    pip install mysql-python   # optional

(`source venv/bin/activate` pridá venv/bin do $PATH shellu, kde to spustíte.
Treba to v každom shelli, kde chcete používať console.py. Alebo sa dá všade
písať `venv/bin/pip ...` a `venv/bin/python console.py ...`.)


### Settings

Vyrobte prázdny súbor `testovac/local_settings.py` a skopírujte doňho tie
časti zo `testovac/settings.py`, ktoré chcete zmeniť. (Bežne to sú
`demo_mode`, `disable_refresh` a `db_connect`.)


### Spustenie

    ./console.py initschema
    ./console.py import demo.xml
    ./console.py serve --debug


### Produkčný server

Naklonujte repozitár so štandardnou HTTPS adresou:
`git clone -b h-server https://github.com/fmfi-svt/testovac.git`
(GitHub si pri pushoch vypýta login a heslo.)

`venv` môže byť aktívny stále, takže pridajte do `.bashrc` riadok
`source ~/venv/bin/activate`.

Defaultná konfigurácia MySQL (okrem zopár distribúcii ako ArchLinux)
nezvláda UTF-8. Do `/etc/mysql/conf.d/` treba pridať `mysql-kodovanie.cnf`
(pôvodne zo ŠVT arény)
alebo vhodný ekvivalent. Zlé kódovania sú nákazlivé, takže ak bola databáza
vyrobená predtým, radšej treba celú databázu dropnúť a vyrobiť nanovo.
Testovací príkaz: `SHOW VARIABLES LIKE 'character_set%';`

Tiež treba spustiť NTP server a nastaviť ho, nech dáva svoj čas klientom. Ditto DHCP server.
IP adresa H-servera má byť 192.168.0.1.

Ďalej treba Apache: `sudo apt-get install apache2 libapache2-mod-wsgi`

Do konfigurácie vhodného VirtualHostu (asi `*:80`) potom treba pridať:

    WSGIScriptAlias / /home/USERNAME/testovac/console.py
    Alias /static /home/USERNAME/testovac/testovac/static
    WSGIDaemonProcess testovacprod processes=2 threads=15 display-name=%{GROUP} python-path=/home/USERNAME/testovac:/home/USERNAME/venv/lib/python2.7/site-packages home=/home/USERNAME/testovac
    WSGIProcessGroup testovacprod
    <Directory /home/USERNAME/testovac>
            Order allow,deny
            Allow from all
    </Directory>

Vyzerá, že Apache 2.4 navyše potrebuje toto:

    <Directory /home/USERNAME/testovac>
            Require all granted
    </Directory>

