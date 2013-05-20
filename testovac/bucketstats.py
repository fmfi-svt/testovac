
from fractions import Fraction
from models import Questions, Users, Buckets, user_closed, get_results


def bucketstats(app):
    db = app.DbSession()

    pids = [user.pid for user in db.query(Users) if user_closed(user)]
    results, details = get_results(db, pids)

    bucket_of = dict((q.qid, q.bid) for q in db.query(Questions))

    buckets = list(db.query(Buckets))
    sums = dict((b.bid, Fraction(0)) for b in buckets)

    for user_details in details.itervalues():
        for (qid, qsubord), question_points in user_details.iteritems():
            sums[bucket_of[qid]] += question_points

    print 'Bid,Size,Average reached,Maximum possible,Percent'

    for b in buckets:
        avg = float(sums[b.bid] / len(pids))
        max = b.size * b.points
        print '%s,%s,%s,%s,%s' % (b.bid, b.size, avg, max, 100 * avg / max)

    db.close()
bucketstats.help = '  $0 bucketstats > stats.csv'


commands = {
    'bucketstats': bucketstats,
}
