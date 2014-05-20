
import os
import sys
import time
from models import Users
from .settings import exam


columns = 2


login_block_file = os.path.join(os.path.dirname(os.path.dirname(__file__)),
                                'loginblock')


colors = dict(grey=30, red=31, green=32, yellow=33, blue=34, magenta=35, cyan=36, white=37)
def maybecolor(text, color, when):
    return '\033[1;{}m{}\033[0m'.format(colors[color], text) if when else text


def monitor(app):
    print "Prihlasovanie:", maybecolor("zakazane", "green", True) if os.path.exists(login_block_file) else maybecolor("POVOLENE", "red", True)
    print

    db = app.DbSession()
    now = int(time.time())

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
        delta_time = their_time   # delta_time == cas od posledneho eventu

        if row.submitted:
            subdesc = maybecolor('odovzdane  ', 'red', True)
            num_submitted += 1
        elif their_time > exam.server_time_limit:
            subdesc = maybecolor('expirovane ', 'red', True)
            num_expired += 1
        else:
            subdesc = 'cas: %+03d:%02d' % (their_time // 60, their_time % 60)

        if row[1]:
            idledesc = '%+03d:%02d' % (row[3] // 60, row[3] % 60)
            delta_time -= row[3]
        else:
            idledesc = '  N/A '

        line = '{} {} odp: {} (ev: {:3d} last: {})'.format(
            row.pid, subdesc,
            maybecolor('%3d' % row[2], 'green', row[2] == 116),
            row[1],
            maybecolor(idledesc, 'yellow', delta_time > 10*60))

        num += 1
        if num % columns == 0:
            print line
        else:
            print line, '|',

    if num % columns != 0: print ''
    print ''

    print 'Celkovy pocet: %2d' % num
    print maybecolor(' - odovzdane : %2d' % num_submitted, 'red', num_submitted > 0)
    print maybecolor(' - expirovane: %2d' % num_expired, 'red', num_expired > 0)
    print ' - vyplna    : %2d' % (num - num_submitted - num_expired)

    print
    print 'Vytlacenych:', '%4d' % db.query(Users).filter_by(printed=True).count()

    db.close()

monitor.help = '  $0 monitor'


def monitorwatch(app):
    os.execlp('watch', 'watch', '-c', sys.argv[0], 'monitor')
monitorwatch.help = '  $0 monitorwatch'


commands = {
    'monitor': monitor,
    'monitorwatch': monitorwatch,
}
