
import itertools
from .settings import checkpid
from .models import Users, Events, user_closed


def with_user(orig_fn):
    def wrapped(request):
        db = request.db
        pid = request.form['pid']
        sessid = int(request.form['sessid'])
        if not checkpid.check(pid): return { 'error': 'invalid pid' }
        user = db.query(Users).filter_by(pid=pid).first()
        if not user: return { 'error': 'invalid pid' }
        if user.sessid != sessid: return { 'error': 'invalid sessid' }
        return orig_fn(request, db, user)
    return wrapped


@with_user
def save(request, db, user):
    events = []
    fields = [c.name for c in Events.c if c.name not in ['pid', 'serial']]
    client_saved_events = int(request.form['savedEvents'])
    for i in itertools.count():
        try:
            event = { 'pid': user.pid, 'serial': client_saved_events + i }
            for c in fields:
                event[c] = request.form['events[%d][%s]' % (i, c)]
            events.append(event)
        except KeyError:
            break

    if user_closed(user): return { 'error': 'closed' }

    server_saved_events = db.query(Events).filter_by(pid=user.pid).count()

    # server_saved_events = how many events we have in the database.
    # client_saved_events = how many events the client believes we have.
    # that might be less than server_saved_events if the client's info is out
    # of date (we ignore the duplicates in that case), but it can't be more
    if client_saved_events > server_saved_events:
        return { 'error': 'invalid savedEvents' }

    for i, event in enumerate(events):
        # if the server already has this event, ignore it
        if event['serial'] < server_saved_events: continue
        db.execute(Events.insert().values(**event))
        server_saved_events += 1

    db.commit()

    return {
        'savedEvents': server_saved_events,
        'beginTime': user.begintime,
    }


@with_user
def close(request, db, user):
    db.execute(Users.update().where(Users.c.pid==user.pid).
               values(submitted=True))
    db.commit()

    return {}


actions = {
    'save': save,
    'close': close,
}
