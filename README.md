Testovač EPV
------------

Quick start:


### Systémové závislosti

* Python 2.7
* Python headre (`apt-get install python-dev` alebo podobne)
* virtualenv (`apt-get install python-virtualenv`)

A ak chcete MySQL namiesto SQLite:

* MySQL server (`apt-get install mysql-server` a zapnúť ho)
* MySQL headre (`apt-get install libmysqlclient-dev`)
* databáza (`CREATE DATABASE testovacdb`)
* užívateľ (`GRANT ALL ON testovacdb.* TO testovac@localhost`)


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

