
import os
import time
from models import Users
from .settings import exam


columns = 2


def monitor(app):
    db = app.DbSession()
    now = int(time.time())

    print 'Vytlacene:', db.query(Users).filter_by(printed=True).count()

    results = db.execute('''SELECT
        u.pid, COUNT(e.serial), COUNT(DISTINCT e.qorder, e.qsubord),
        MAX(e.time) - u.begintime, u.submitted, u.begintime
        FROM users u LEFT JOIN events e ON u.pid = e.pid
        WHERE printed = 0
        GROUP BY e.pid
        ORDER BY e.pid''').fetchall()

    num_submitted = 0
    num_expired = 0
    num = 0
    for row in results:
        their_time = now - row.begintime

        if row.submitted:
            subdesc = 'odovzdane '
            num_submitted += 1
        elif their_time > exam.server_time_limit:
            subdesc = 'expirovane'
            num_expired += 1
        else:
            subdesc = ' cas: %02d:%02d' % (their_time // 60, their_time % 60)

        if row[1]:
            idledesc = '%02d:%02d' % (row[3] // 60, row[3] % 60)
        else:
            idledesc = ' N/A '

        line = '{} {} odp: {:3d} (ev: {:3d} last: {})'.format(
            row.pid, subdesc, row[2], row[1], idledesc)

        num += 1
        if num % columns == 0:
            print line
        else:
            print line + ' | ',

    if num % columns != 0: print ''
    print ''

    print 'Celkovy pocet: %2d' % num
    print ' - odovzdane : %2d' % num_submitted
    print ' - expirovane: %2d' % num_expired
    print ' - vyplna    : %2d' % (num - num_submitted - num_expired)

    db.close()

monitor.help = '  $0 monitor'


commands = {
    'monitor': monitor,
}
