# -*- coding: utf-8 -*-

import os
import re
from subprocess import check_call
from .settings import exam
from . import models
from models import (Users, CurrentEvents, Subquestions,
    user_closed, get_user_questions, get_results)
from jinja2 import Template
import time


# allow changing the binary with an environment variable
pdfcslatex = os.getenv('PDFCSLATEX', 'pdfcslatex')


with open(os.path.dirname(__file__) + '/printing.tex') as f:
    template_content = f.read().decode('utf-8')
    template = Template(template_content, '<$', '$>', '<<', '>>', '<<%', '%>>')


def question_to_tex(body):
    #return re.sub(r'<br\s*/?>', '\\\\[10pt]', body)
    latexed = body
    latexed = re.sub(r'<br\s*/?>', '', latexed)
    latexed = latexed.replace('\n          \n          ', '\n\\\\[10pt]\n')   # TODO: toto predsa nemoze byt spravne...
    latexed = latexed.replace('\n          ', '\n\\\\[10pt]\n')
    return latexed


def subquestion_to_tex(body):
    latexed = body
    latexed = re.sub(r'<br\s*/?>', '', latexed)
    latexed = latexed.replace('\n          \n          ', '\n\\\\[10pt]\n')   # TODO: toto predsa nemoze byt spravne...
    latexed = re.sub(r'<hr\s*/?>', '\Qline ', latexed)
    latexed = re.sub(r'&#?[a-z0-9]{2,8};', '', latexed)
    return latexed


def format_answer(answer):
    if answer: answer = unicode(answer)
    if not answer: answer = u'nič'
    if answer == u'true': answer = u'áno'
    if answer == u'false': answer = u'nie'
    return answer


def format_questions(questions, sub_info=None):
    for qorder, question in enumerate(questions):
        qid = question['qid']
        body = question_to_tex(question['body'])
        subs = []
        for qsubord, value in sorted(question.iteritems()):
            if qsubord == 'body' or qsubord == 'qid': continue
            subbody = qsubord + ') ' + subquestion_to_tex(value['body'])
            subs.append({ 'body': subbody })

            if sub_info:
                subs[-1]['info'] = sub_info(qorder, qid, qsubord) or ''

        yield { 'body': body, 'subs': subs }


def render_pdf(pid, target_dir, **template_args):
    content = template.render(**template_args)

    if not os.path.isdir('aux'):
        os.mkdir('aux', 0700)

    with open('aux/%s.tex' % pid, 'w') as f:
        f.write(content.encode('utf-8'))

    check_call([pdfcslatex, '%s.tex' % pid], cwd='aux')

    if not os.path.isdir(target_dir):
        os.mkdir(target_dir, 0700)

    os.rename('aux/%s.pdf' % pid, '%s/%s.pdf' % (target_dir, pid))


def printexamlarge(app, pid):
    db = app.DbSession()

    user = db.query(Users).filter_by(pid=pid).first()
    if not user: raise ValueError('invalid pid')

    questions = get_user_questions(db, pid, with_qid=True)

    render_pdf(pid, 'exams',
        show_pid=pid, large_header=True,
        questions=format_questions(questions))

    db.close()
printexamlarge.help = '  $0 printexamlarge <pid>'


def printfinished(app, pid):
    db = app.DbSession()

    if pid == '--notprinted':
        pids = [user.pid for user in db.query(Users)
                if user_closed(user) and not user.printed]
    else:
        user = db.query(Users).filter_by(pid=pid).first()
        if not user: raise ValueError('invalid pid')
        if not user_closed(user): raise ValueError('user not yet closed')
        pids = [pid]
    printfinished_pids(db, pids)

    db.close()
printfinished.help = '  $0 printfinished --notprinted\n  $0 printfinished <pid>'

def printfinished_pids(db, pids):
    for pid in pids:
        questions = get_user_questions(db, pid, with_qid=True)
        answers = {}
        for event in db.query(CurrentEvents).filter_by(pid=pid):
            answers[(event.qorder, event.qsubord)] = event.value

        def sub_info(qorder, qid, qsubord):
            return (r'\hskip0.5cm \textbf{%s}' %
                format_answer(answers.get((qorder, qsubord))))

        render_pdf(pid, 'spool',
            show_pid=pid, show_sign=True,
            questions=format_questions(questions, sub_info))

        db.execute(Users.update().where(Users.c.pid==pid).
                   values(printed=True))
        db.commit()


def send_to_printer_and_backup(pids, backup_dir_name):
    spool_dir = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'spool')
    backup_dir_path = os.path.join(spool_dir, backup_dir_name)
    if not os.path.isdir(backup_dir_path):
        print 'Creating backup directory: %s' % backup_dir_path
        os.mkdir(backup_dir_path)
   
    for pid in pids:
        pdfname = '%s.pdf' % pid
        print 'Printing: %s' % pdfname
        check_call(['lpr', pdfname], cwd=spool_dir)
        print 'Backuping: %s' % pdfname
        os.rename(os.path.join(spool_dir, pdfname), os.path.join(backup_dir_path, pdfname))

def notification(title, message=None):
    # TODO: pouzime pynotify, teraz tu nie je, tak pouzijeme notify-send
    args = ['notify-send', title]
    if message != None:
        args += [message]
    check_call(args)

def printwatch(app, backup_dir_name):
    time_limit = 2 * 60
    first_pid = None
    printed_batches = []
    while True:
        db = app.DbSession()
        vyplna = len([user.pid for user in db.query(Users)
                if not user_closed(user) and not user.printed])
        pids = [user.pid for user in db.query(Users)
                if user_closed(user) and not user.printed]
        if vyplna == 0 and len(pids) == 0:
            break
        if len(pids) > 0:
            is_first_pid = first_pid == None
            if is_first_pid:
                first_pid = time.time()
            time_delta = time.time() - first_pid
            if time_delta > time_limit or len(pids) > 10 or vyplna == 0:
                notification('Tlacim %d testov' % len(pids))
                printfinished_pids(db, pids)
                send_to_printer_and_backup(pids, backup_dir_name)
                printed_batches.append(len(pids))
                first_pid = None
            else:
                if is_first_pid:
                    notification('Prvy odovzdal, cakam', 'Pidov: %d' % len(pids))
                print '%s (%s) Waiting: time_delta: %ds, pids: %d' % (time.ctime(), backup_dir_name, int(time_delta), len(pids))
        else:
            print '%s (%s) Nothing to print yet.' % (time.ctime(), backup_dir_name)
        db.close()
        if vyplna > 0:
            time.sleep(5)
    batches_descr = '+'.join(str(x) for x in printed_batches) + ' = ' + str(sum(printed_batches))
    if len(printed_batches) == 0:
        batches_descr = 'Nic nevytlacene'
    notification('Tlac %s dokoncena' % backup_dir_name,
        'Vytlacene: %s' % batches_descr)
    print '%s (%s) Printing finished: %s' % (time.ctime(), backup_dir_name, batches_descr)
printwatch.help = '  $0 printwatch backup_dir_name'


def printevaluatedexam(app, pid):
    db = app.DbSession()

    if pid == '--all':
        pids = [user.pid for user in db.query(Users)]
    else:
        user = db.query(Users).filter_by(pid=pid).first()
        if not user: raise ValueError('invalid pid')
        pids = [pid]

    correct_answers = {}
    for sq in db.query(Subquestions):
        correct_answers[(sq.qid, sq.qsubord)] = sq.value

    points = exam.get_question_scores(models, db)

    results, details = get_results(db, pids)

    for pid in pids:
        questions = get_user_questions(db, pid, with_qid=True)
        answers = {}
        for event in db.query(CurrentEvents).filter_by(pid=pid):
            answers[(event.qorder, event.qsubord)] = event.value

        def sub_info(qorder, qid, qsubord):
            their_answer = answers.get((qorder, qsubord))
            correct_answer = correct_answers[(qid, qsubord)]
            their_points = details[pid].get((qid, qsubord), 0)
            max_points = points[(qid, qsubord)]
            return (r'\\ \textbf{%.2f; %s} (%.2f; %s)' % (
                their_points, format_answer(their_answer),
                max_points, format_answer(correct_answer)))

        def format_points_sum(p):
            return re.sub(r'\.0+$', '', '%.3f' % p)

        my_qids = set(question['qid'] for question in questions)
        points_total = sum(points[(qid, qsubord)] for (qid, qsubord) in points if qid in my_qids)
        print results[pid]

        render_pdf(pid, 'evaluatedexams',
            show_pid=pid,
            points_total=format_points_sum(points_total),
            points_gained=format_points_sum(results[pid]),
            questions=format_questions(questions, sub_info))

    db.close()
printevaluatedexam.help = '  $0 printevaluatedexam <pid>\n  $0 printevaluatedexam --all'


commands = {
    'printwatch': printwatch, 
    'printfinished': printfinished,
    'printexamlarge': printexamlarge,
    'printevaluatedexam': printevaluatedexam,
}
