
# ktory exam
from .exam import flaw as exam

def db_connect():
    from sqlalchemy import create_engine
    return create_engine('sqlite:///db.sqlite')

try:
    from testovac.local_settings import *
except ImportError:
    pass
