
from calendar import timegm
from fractions import Fraction
from models import Questions, Users, Buckets, user_closed, get_results
from subprocess import Popen, PIPE


def genstats(app, tfrom=None, tto=None):
    db = app.DbSession()

    if tfrom:
        convert = lambda d: timegm(map(int, d.split('-')) + [0, 0, 0])
        tfrom, tto = convert(tfrom), convert(tto or tfrom) + 86400

    users = [user for user in db.query(Users) if user_closed(user)]
    if tfrom: users = [user for user in users if tfrom < user.begintime < tto]
    pids = [user.pid for user in users]
    results, details = get_results(db, pids)

    bucket_of = dict((q.qid, q.bid) for q in db.query(Questions))

    buckets = list(db.query(Buckets))
    sums = dict((b.bid, Fraction(0)) for b in buckets)

    for user_details in details.itervalues():
        for (qid, qsubord), question_points in user_details.iteritems():
            sums[bucket_of[qid]] += question_points

    yield ('Bid', 'Size', 'Average reached', 'Maximum possible', 'Percent', 'Graph')

    for b in buckets:
        avg = float(sums[b.bid] / len(pids))
        max = b.size * b.points
        stars = '|' + ('*' * int(round(50 * avg / max))).ljust(50) + '|'
        yield map(str, (b.bid, b.size, avg, max, 100 * avg / max, stars))

    db.close()


def bucketstats(app, tfrom=None, tto=None):
    for line in genstats(app, tfrom, tto):
        print ','.join(line[:-1])
bucketstats.help = '  $0 bucketstats [YYYY-MM-DD [YYYY-MM-DD]] > stats.csv'


def bucketstars(app, tfrom=None, tto=None):
    p = Popen(['column', '-s,', '-t'], stdin=PIPE)
    for line in genstats(app, tfrom, tto):
        p.stdin.write(','.join(line) + '\n')
    p.stdin.close()
    if p.wait() != 0: raise OSError("zly returncode")
bucketstars.help = '  $0 bucketstars [YYYY-MM-DD [YYYY-MM-DD]]'


commands = {
    'bucketstats': bucketstats,
    'bucketstars': bucketstars,
}
