
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
    Column('serial', Integer, nullable=False, primary_key=True, autoincrement=False),
    Column('qorder', Integer, nullable=False),
    Column('qsubord', CHAR(1), nullable=False),
    Column('value', String(255)),
    Column('time', Integer, nullable=False),
    ForeignKeyConstraint(['pid', 'qorder'], ['user_questions.pid', 'user_questions.qorder']),
)
Events.key_fields = ['pid', 'qorder', 'qsubord']

Questions = Table('questions', metadata,
    Column('qid', Integer, nullable=False, primary_key=True, autoincrement=False),
    Column('body', UnicodeText, nullable=False),
)

Subquestions = Table('subquestions', metadata,
    Column('qid', Integer, ForeignKey('questions.qid'), nullable=False, primary_key=True, autoincrement=False),
    Column('qsubord', CHAR(1), nullable=False, primary_key=True),
    Column('body', UnicodeText, nullable=False),
    Column('value', String(255), nullable=False),
)

UserQuestions = Table('user_questions', metadata,
    Column('pid', String(255), ForeignKey('users.pid'), nullable=False, primary_key=True),
    Column('qorder', Integer, nullable=False, primary_key=True, autoincrement=False),
    Column('qid', Integer, ForeignKey('questions.qid'), nullable=False)
)


# metadata.create_all() can't create views, so we declare current_events using
# the lower-level table() instead of Table(..., metadata, ...)
CurrentEvents = table('current_events', *(c.copy() for c in Events.columns))

def create_current_events(db_engine):
    drop_current_events(db_engine)

    # http://stackoverflow.com/a/2111420
    on_clause = ' AND '.join('e1.{0} = e2.{0}'.format(f)
                             for f in Events.key_fields)
    db_engine.execute('''
        CREATE VIEW current_events AS
        SELECT e1.* FROM events e1
        LEFT OUTER JOIN events e2 ON ({0} AND e1.serial < e2.serial)
        WHERE e2.serial IS NULL'''.format(on_clause))

def drop_current_events(db_engine):
    db_engine.execute('''DROP VIEW IF EXISTS current_events''')


def user_closed(user):
    return (user.submitted or
            time.time() > user.begintime + exam.server_time_limit)


def get_user_questions(db, pid):
    result = {}

    for qorder, body in (db
            .query(UserQuestions.c.qorder, Questions.c.body)
            .filter(UserQuestions.c.qid == Questions.c.qid,
                    UserQuestions.c.pid == pid)):
        result[qorder] = { 'body': body }

    for qorder, qsubord, body, isbool in (db
            .query(UserQuestions.c.qorder, Subquestions.c.qsubord,
                   Subquestions.c.body,
                   Subquestions.c.value.in_(('true', 'false')))
            .filter(UserQuestions.c.qid == Subquestions.c.qid,
                    UserQuestions.c.pid == pid)):
        type = 'bool' if isbool else 'text'
        result[qorder][qsubord] = { 'body': body, 'type': type }

    result_list = [result[i] for i in xrange(len(result))]
    return result_list


def generate_user_questions(db, pid):
    chosen_qids = exam.choose_user_questions(models, db)
    for i, qid in enumerate(chosen_qids):
        db.execute(UserQuestions.insert().values(pid=pid, qorder=i, qid=qid))
    db.commit()
    return get_user_questions(db, pid)


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
