
import os
import sys
import time
from models import Users
from .settings import exam
from termcolor import colored


columns = 2


def maybecolor(text, color, when):
    return colored(text, color, attrs=['bold']) if when else text


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
        delta_time = their_time

        if row.submitted:
            subdesc = colored('odovzdane  ', 'red', attrs=['bold'])
            num_submitted += 1
        elif their_time > exam.server_time_limit:
            subdesc = colored('expirovane ', 'red', attrs=['bold'])
            num_expired += 1
        else:
            subdesc = 'cas: %+03d:%02d' % (their_time // 60, their_time % 60)

        if row[1]:
            idledesc = '%+03d:%02d' % (row[3] // 60, row[3] % 60)
            delta_time -= row[3]
        else:
            idledesc = ' N/A  '

        line = '{} {} odp: {} (ev: {:3d} last: {})'.format(
            row.pid, subdesc,
            maybecolor('%3d' % row[2], 'green', row[2] == 116),
            row[1],
            maybecolor(idledesc, 'yellow', delta_time > 10*60))

        num += 1
        if num % columns == 0:
            print line
        else:
            print line + ' | ',

    if num % columns != 0: print ''
    print ''

    print 'Celkovy pocet: %2d' % num
    print maybecolor(' - odovzdane : %2d' % num_submitted, 'red', num_submitted > 0)
    print maybecolor(' - expirovane: %2d' % num_expired, 'red', num_expired > 0)
    print ' - vyplna    : %2d' % (num - num_submitted - num_expired)

    db.close()

monitor.help = '  $0 monitor'


def monitorwatch(app):
    os.execlp('watch', 'watch', '-c', sys.argv[0], 'monitor')
monitorwatch.help = '  $0 monitorwatch'


commands = {
    'monitor': monitor,
    'monitorwatch': monitorwatch,
}
