
# ktory exam
from .exam import flaw as exam

# ktory pid checker
from .checkpid import verhoeff as checkpid

def db_connect():
    from sqlalchemy import create_engine
    return create_engine('sqlite:///db.sqlite')

try:
    from testovac.local_settings import *
except ImportError:
    pass
