
import os
import random
import time
from .settings import exam, checkpid
from .models import (Users, Events, CurrentEvents, user_closed,
    get_user_questions, generate_user_questions)


login_block_file = os.path.join(os.path.dirname(os.path.dirname(__file__)),
                                'loginblock')


def login(request):
    db = request.db
    pid = request.form['pid']

    if os.path.exists(login_block_file):
        return { 'error': 'login blocked' }
    if not checkpid.check(pid):
        return { 'error': 'invalid pid' }

    new_sessid = random.randint(0, 65535)

    user = db.query(Users).filter_by(pid=pid).first()

    if user:
        if user_closed(user):
            return { 'error': 'closed' }

        db.execute(Users.update().where(Users.c.pid==pid).
                   values(sessid=new_sessid))

        begintime = user.begintime
        questions = get_user_questions(db, pid)
        want_columns = (c for c in CurrentEvents.columns
                        if c.name not in ['pid', 'serial'])
        state = [t._asdict() for t in
                 db.query(CurrentEvents).filter_by(pid=pid)
                   .values(*want_columns)]
        saved_events = db.query(Events).filter_by(pid=pid).count()
    else:
        begintime = int(time.time())

        db.execute(Users.insert().values(
            pid=pid, sessid=new_sessid, begintime=begintime))

        questions = generate_user_questions(db, pid)
        state = []
        saved_events = 0

    db.commit()

    return {
        'sessid': new_sessid,
        'beginTime': begintime,
        'now': time.time(),
        'questions': questions,
        'state': state,
        'savedEvents': saved_events,
    }


def loginctl(app, *args):
    if args == ('block',):
        if os.path.exists(login_block_file):
            return "login is already blocked!"
        with open(login_block_file, 'a'): pass
    elif args == ('unblock',):
        if not os.path.exists(login_block_file):
            return "login is already unblocked!"
        os.remove(login_block_file)
    else:
        raise ValueError("must use block or unblock")
loginctl.help = '  $0 login block\n  $0 login unblock'


actions = {
    'login': login,
}


commands = {
    'login': loginctl,
}
