
Testovač EPV
------------

Závislosti: Python 2.7 a štandardné súvisiace veci (headre, virtualenv).
Napr. na Ubuntu sú to balíky python-dev a python-virtualenv. Ak chcete MySQL,
potrebujete aj MySQL headre (a rozbehaný server).

Quick start:

    virtualenv venv
    source venv/bin/activate
    pip install werkzeug jinja2 sqlalchemy
    pip install mysql-python   # optional
    ./console.py initschema
    ./console.py import demo.xml
    ./console.py serve --debug

(`source venv/bin/activate` pridá venv/bin do $PATH shellu, kde to spustíte.
Treba to v každom shelli, kde chcete používať console.py. Alebo sa dá všade
písať `venv/bin/pip ...` a `venv/bin/python console.py ...`.)

Vlastné settings:

Treba vyrobiť `testovac/local_settings.py` a napísať tam tie veci, čo majú byť
iné oproti `testovac/settings.py`. Napríklad:

    demo_mode = True
    disable_refresh = False

Podobne sa tam dá zmeniť `db_connect()` a ostatné nastavenia.
