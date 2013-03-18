
import sys
import time
from sqlalchemy import (MetaData, Table, Column, ForeignKey,
    ForeignKeyConstraint, Integer, String, Boolean, UnicodeText, CHAR)
from sqlalchemy.sql import table
from .settings import exam
models = sys.modules[__name__]


metadata = MetaData()


Users = Table('users', metadata,
    Column('pid', String(255), nullable=False, primary_key=True),
    Column('sessid', Integer, nullable=False),
    Column('begintime', Integer, nullable=False),
    Column('submitted', Boolean, nullable=False, default=False),
)

Events = Table('events', metadata,
    Column('pid', String(255), ForeignKey('users.pid'), nullable=False, primary_key=True, index=True),
    Column('serial', Integer, nullable=False, primary_key=True),
    Column('qorder', Integer, nullable=False),
    Column('qsubord', Integer, nullable=False),
    Column('value', String(255)),
    Column('time', Integer, nullable=False),
    ForeignKeyConstraint(['pid', 'qorder'], ['user_questions.pid', 'user_questions.qorder']),
)
Events.key_fields = ['pid', 'qorder', 'qsubord']

Questions = Table('questions', metadata,
    Column('qid', Integer, nullable=False, primary_key=True),
    Column('body', UnicodeText, nullable=False),
)

Subquestions = Table('subquestions', metadata,
    Column('qid', Integer, ForeignKey('questions.qid'), nullable=False, primary_key=True),
    Column('qsubord', CHAR(1), nullable=False, primary_key=True),
    Column('body', UnicodeText, nullable=False),
    Column('value', String(255), nullable=False),
)

UserQuestions = Table('user_questions', metadata,
    Column('pid', String(255), ForeignKey('users.pid'), nullable=False, primary_key=True),
    Column('qorder', Integer, nullable=False, primary_key=True),
    Column('qid', Integer, ForeignKey('questions.qid'), nullable=False)
)


# metadata.create_all() can't create views, so we declare current_events using
# the lower-level table() instead of Table(..., metadata, ...)
CurrentEvents = table('current_events', *(c.copy() for c in Events.columns))

def create_current_events(db_engine):
    fields_clause = ', '.join(Events.key_fields)
    on_clause = ' AND '.join('events.{0} = x.{0}'.format(f)
                             for f in Events.key_fields + ['serial'])
    db_engine.execute('''
        CREATE VIEW IF NOT EXISTS current_events AS
        SELECT events.* FROM events JOIN (
            SELECT {0}, MAX(serial) AS serial FROM events
            GROUP BY {0}) x
        ON {1}'''.format(fields_clause, on_clause))

def drop_current_events(db_engine):
    db_engine.execute('''DROP VIEW IF EXISTS current_events''')


def user_closed(user):
    return (user.submitted or
            time.time() > user.begintime + exam.server_time_limit)


def initschema(app):
    metadata.create_all(app.db_engine)
    create_current_events(app.db_engine)
initschema.help = '  $0 initschema'


def droptables(app, *args):
    if list(args) != ['--delete-everything']:
        raise ValueError("must use --delete-everything")
    drop_current_events(app.db_engine)
    metadata.drop_all(app.db_engine)
droptables.help = '  $0 droptables --delete-everything'


def import_(app, filename):
    db = app.DbSession()
    exam.import_questions(models, db, filename)
    db.close()
import_.help = '  $0 import questions.xml'


commands = {
    'initschema': initschema,
    'droptables': droptables,
    'import': import_,
}


exam.add_exam_models(models)
