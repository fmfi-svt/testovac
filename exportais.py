
import os
import sys
import json
from subprocess import Popen, PIPE

def run_php(phpcode):
    p = Popen(['php'], stdin=PIPE, stdout=PIPE, stderr=PIPE)
    pout, perr = p.communicate(phpcode)
    if p.wait() != 0: raise OSError("couldn't run php\n" + perr)
    if perr != '': raise OSError("php returned an error\n" + perr)
    return json.loads(pout)

def load_php(filename, varname):
    with open(filename) as f: text = f.read().rpartition(';')[0]
    return run_php(text + '; print json_encode(' + varname + ');')

def export_ais(form, cbody, hbody, ais_input, out1, out2):
    # load cbody, hbody
    cpoints = load_php(cbody, '$bodyC')
    hpoints = load_php(hbody, '$bodyH')

    # load ais_input
    with open(ais_input) as f: lines = [l.strip().decode('utf-8').split('|') for l in f]
    rows = [dict(zip(lines[0], l)) for l in lines[1:]]
    personids = dict(((r['Meno'], r['Priezvisko'], r['datumNarodenia']), r['identifikacia']) for r in rows)

    # load students in db
    # TODO: ked niekto ma v db narodenie 0000-00-00, a v aise ma taky len jedno meno (a iny datum narodenia), mozno ich sparovat (a mozno rovno ponuknut zmenu databazy alebo aspon vypisat vhodny sql prikaz)
    students = run_php('''<?php
        require "src/actions.php";
        $db = connect_db();
        $q = $db->query("SELECT meno, priezvisko, datum_narodenia, pid, forma_studia FROM Students;");
        $res = array(); foreach($q as $r) $res[] = $r;
        print json_encode($res);
    ''')
    idmap = []
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
        y, m, d = row['datum_narodenia'].split('-')
        aiskey = (row['meno'], row['priezvisko'], d+'.'+m+'.'+y)
        if aiskey not in personids:
            raise ValueError('tento nie je v aise: ' + str(aiskey))
        idmap.append((personids[aiskey], row['pid']))

    # write out1
    with open(out1, 'w') as f:
        print >>f, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        print >>f, '<PKProgramNaPrihlaskes xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" class="[Lais.bo.vs.pk.PKProgramNaPrihlaske;">'
        for aisid, pid in idmap:
            print >>f, '\t<item>'
            print >>f, '\t\t<idPrihlaska>%s</idPrihlaska>' % aisid
            print >>f, '\t\t<identifikacia>%s</identifikacia>' % pid.replace('-', '')
            print >>f, '\t</item>'
        print >>f, '</PKProgramNaPrihlaskes>'

    # write out2
    with open(out2, 'w') as f:
        print >>f, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        print >>f, '<PKVysledokPredmetPKs class="[Lais.bo.vs.pk.PKVysledokPredmetPK;">'
        for pid in cpoints:
            if cpoints[pid]['forma'] != form: continue
            total_points = (1.0 * hpoints[pid][0] / hpoints[pid][1]) + cpoints[pid]['body']
            print >>f, '\t<item>'
            print >>f, '\t\t<kodUchadzaca>'+pid.replace('-','')+'PVT</kodUchadzaca>'
            print >>f, '\t\t<sucetBodov>%.3f</sucetBodov>' % total_points
            print >>f, '\t</item>'
        print >>f, '</PKVysledokPredmetPKs>'

if __name__ == '__main__':
    if len(sys.argv) == 7:
        export_ais(*sys.argv[1:])
    else:
        print 'usage: %s [denna|externa] cbody.php hbody.php ais_input.csv ais_output_1.xml ais_output_2.xml'
        sys.exit(1)

