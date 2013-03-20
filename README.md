
Testovač EPV
------------

Quick start:

- virtualenv venv
- source venv/bin/activate
- pip install werkzeug sqlalchemy mysql-python jinja2
- ./console.py initschema
- ./console.py import demo.xml
- ./console.py serve --debug

Vlastné settings:

Treba vyrobiť `testovac/local_settings.py` a napísať tam tie veci, čo majú byť
iné oproti `testovac/settings.py`. Napríklad:

    demo_mode = True
    disable_refresh = False

Podobne sa tam dá zmeniť `db_connect()` a ostatné nastavenia.
