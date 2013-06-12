
from fractions import Fraction
from models import Questions, Users, Buckets, user_closed, get_results
from subprocess import Popen, PIPE


def genstats(app):
    db = app.DbSession()

    pids = [user.pid for user in db.query(Users) if user_closed(user)]
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


def bucketstats(app):
    for line in genstats(app):
        print ','.join(line[:-1])
bucketstats.help = '  $0 bucketstats > stats.csv'


def bucketstars(app):
    p = Popen(['column', '-s,', '-t'], stdin=PIPE)
    for line in genstats(app):
        p.stdin.write(','.join(line) + '\n')
    p.stdin.close()
    if p.wait() != 0: raise OSError("zly returncode")
bucketstars.help = '  $0 bucketstars'


commands = {
    'bucketstats': bucketstats,
    'bucketstars': bucketstars,
}
