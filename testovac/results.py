# -*- coding: utf-8 -*-

import os
from fractions import Fraction
from models import Users, user_closed, get_results


def exportresults(app):
    db = app.DbSession()
    pids = [user.pid for user in db.query(Users) if user_closed(user)]
    results = get_results(db, pids)
    db.close()

    print '<?php $bodyH = array('
    for pid, points in results.iteritems():
        points = Fraction(points)
        print "  '{}' => array({}, {})," % (pid, points.numerator, points.denominator)
    print ');'
exportresults.help = '  $0 exportresults > hbody.php'


commands = {
    'exportresults': exportresults,
}
