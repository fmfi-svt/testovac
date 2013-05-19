
# v demo mode sa prihlasuje umelym kodom
demo_mode = False

# ci sa ma pokusat o casovu synchronizaciu so serverom
# (samozrejme ak sa da chceme radsej NTP, to je presnejsie)
attempt_time_correction = False

# ci sa ma zakazat refreshovanie testovaca klavesovymi skratkami
disable_refresh = True

# ci sa ma po kazdom evente (kliknuti apod.) spustit saveEvents
# (vynimka: ak prave nejaky saveEvents bezi, nic sa nestane)
save_after_emit = True

# kazdych kolko milisekund sa ma spustit saveEvents (0 = vypnut)
# (vynimka: ak prave nejaky saveEvents bezi, nic sa nestane)
saving_interval = 15000

# ktory exam
from .exam import flaw as exam

# ktory pid checker
from .checkpid import verhoeff as checkpid


def db_connect():
    from sqlalchemy import create_engine

    # MySQL
    # (poznamka ad pool_recycle: vid http://www.sqlalchemy.org/trac/wiki/FAQ#MySQLserverhasgoneaway - ten error to hadzalo)
#    return create_engine('mysql://myuser:mypass@localhost/mydbname?charset=utf8', pool_recycle=7200)

    # SQLite
    return create_engine('sqlite:///db.sqlite')


# nacitame lokalne upravene nastavenia z local_settings
import os
if os.path.exists(os.path.dirname(__file__) + '/local_settings.py'):
    from testovac.local_settings import *
