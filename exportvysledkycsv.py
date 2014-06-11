
import os
import sys
import csv
import bisect
from exportais import run_php, load_php


def export_ais(form, cbody, hbody):
    # load cbody, hbody
    cpoints = load_php(cbody, '$bodyC')
    hpoints = load_php(hbody, '$bodyH')

    # load students in db
    students = run_php('''<?php
        require "src/actions.php";
        $db = connect_db();
        $q = $db->query("SELECT meno, priezvisko, datum_narodenia, pid, forma_studia FROM Students;");
        $res = array(); foreach($q as $r) $res[] = $r;
        print json_encode($res);
    ''')
    writer = csv.writer(sys.stdout)
    writer.writerow(['Meno', 'Priezvisko', 'Datum narodenia', 'PID', 'Vysvedcenie body', 'Test body', 'Sucet body', 'Poradie'])
    results = []
    body = []
    uniques = set()
    for row in students:
        rforma = 'denna' if row['forma_studia'].startswith('denn') else 'externa'
        if rforma != form: continue
        if not row['pid']: continue
        ukey = (row['meno'], row['priezvisko'], row['datum_narodenia'], rforma)
        if ukey in uniques:
            print 'warning: tento je tam dvakrat:', ukey
            continue
        uniques.add(ukey)
        my_c = cpoints[row['pid']]['body']
        my_h = (1.0 * hpoints[row['pid']][0] / hpoints[row['pid']][1])
        results.append([row['meno'], row['priezvisko'], row['datum_narodenia'], row['pid'], my_c, my_h, my_c + my_h])
        body.append(my_c + my_h)

    body.sort()
    for riadok in results:
        poradie = 1 + (len(body) - bisect.bisect_right(body, riadok[-1]))
        riadok.append(poradie)
        writer.writerow([unicode(s).encode('utf8') for s in riadok])

if __name__ == '__main__':
    if len(sys.argv) == 4:
        export_ais(*sys.argv[1:])
    else:
        print 'usage: %s [denna|externa] cbody.php hbody.php > output.csv'
        sys.exit(1)

