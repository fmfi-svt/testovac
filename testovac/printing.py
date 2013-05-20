# -*- coding: utf-8 -*-

import os
import re
from .settings import exam
from . import models
from models import (Users, CurrentEvents, Subquestions,
    user_closed, get_user_questions, get_results)
from jinja2 import Template


# allow changing the binary with an environment variable
pdfcslatex = os.getenv('PDFCSLATEX', 'pdfcslatex')


with open(os.path.dirname(__file__) + '/printing.tex') as f:
    template_content = f.read().decode('utf-8')
    template = Template(template_content, '<$', '$>', '<<', '>>', '<<%', '%>>')


def mkdirs():
    for name in ['aux', 'spool', 'exams', 'evaluatedexams']:
        if not os.path.isdir(name):
            os.mkdir(name, 0700)


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


def printexamlarge(app, pid):
    mkdirs()
    db = app.DbSession()

    user = db.query(Users).filter_by(pid=pid).first()
    if not user: raise ValueError('invalid pid')

    questions = get_user_questions(db, pid, with_qid=True)

    db.close()

    filename = 'aux/%s.tex' % pid
    print "filename: %s" % filename
    with open(filename, 'w') as f:
        f.write(template.render(show_pid=pid, large_header=True,
                                questions=format_questions(questions)).encode('utf-8'))

    os.system(pdfcslatex + ' -output-directory aux '+pid+'.tex')
    os.system('mv aux/'+pid+'.pdf exams')
printexamlarge.help = '  $0 printexamlarge <pid>'


def printfinished(app):
    mkdirs()
    db = app.DbSession()

    for user in db.query(Users):
        if user_closed(user) and not user.printed:
            pid = user.pid

            questions = get_user_questions(db, pid, with_qid=True)
            answers = {}
            for event in db.query(CurrentEvents).filter_by(pid=pid):
                answers[(event.qorder, event.qsubord)] = event.value

            def sub_info(qorder, qid, qsubord):
                return (r'\hskip0.5cm \textbf{%s}' %
                    format_answer(answers.get((qorder, qsubord))))

            filename = 'aux/%s.tex' % pid
            print "filename: %s" % filename
            with open(filename, 'w') as f:
                f.write(template.render(
                    show_pid=pid, show_sign=True,
                    questions=format_questions(questions, sub_info)).encode('utf-8'))

            os.system(pdfcslatex + ' -output-directory aux '+pid+'.tex')
            os.system('mv aux/*.pdf spool/')

            db.execute(Users.update().where(Users.c.pid==pid).
                       values(printed=True))
            db.commit()

    db.close()
printfinished.help = '  $0 printfinished'


def printevaluatedexam(app, pid):
    mkdirs()
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

        filename = 'aux/%s.tex' % pid
        print "filename: %s" % filename
        with open(filename, 'w') as f:
            f.write(template.render(
                show_pid=pid,
                points_total=format_points_sum(points_total),
                points_gained=format_points_sum(results[pid]),
                questions=format_questions(questions, sub_info)).encode('utf-8'))

        os.system(pdfcslatex + ' -output-directory aux '+pid+'.tex')
        os.system('mv aux/'+pid+'.pdf evaluatedexams')

    db.close()
printevaluatedexam.help = '  $0 printevaluatedexam <pid>\n  $0 printevaluatedexam --all'


commands = {
    'printfinished': printfinished,
    'printexamlarge': printexamlarge,
    'printevaluatedexam': printevaluatedexam,
}
