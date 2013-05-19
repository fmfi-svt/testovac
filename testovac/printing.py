# -*- coding: utf-8 -*-

import os
import re
from fractions import Fraction
from .settings import exam
from . import models
from models import (Users, UserQuestions, CurrentEvents, Subquestions,
    user_closed, get_user_questions)
from jinja2 import Template


# allow changing the binary with an environment variable
pdfcslatex = os.getenv('PDFCSLATEX', 'pdfcslatex')


with open(os.path.dirname(__file__) + '/printing.tex') as f:
    template_content = f.read().decode('utf-8')
    template = Template(template_content, '<$', '$>', '<<', '>>', '<<%', '%>>')


def mkdirs():
    for name in ['aux', 'spool', 'exams']:
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
    latexed = re.sub(r'<hr\s*/?>', '\Qlines{1} ', latexed)
    latexed = re.sub(r'&#?[a-z0-9]{2,8};', '', latexed)
    return latexed


def format_questions(questions, answers=None, points=None):
    for qorder, question in enumerate(questions):
        qid = question['qid']
        body = question_to_tex(question['body'])
        subs = []
        for qsubord, value in sorted(question.iteritems()):
            if qsubord == 'body' or qsubord == 'qid': continue
            subbody = qsubord + ') ' + subquestion_to_tex(value['body'])
            subs.append({ 'body': subbody })

            if points:
                subs[-1]['points'] = points[(qid, qsubord)]

            if answers:
                answer = answers.get((qorder, qsubord))
                if answer: answer = unicode(answer)
                if not answer: answer = u'Nezodpovedané.'
                if answer == u'true': answer = u'Áno.'
                if answer == u'false': answer = u'Nie.'
                subs[-1]['answer'] = answer

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

            filename = 'aux/%s.tex' % pid
            print "filename: %s" % filename
            with open(filename, 'w') as f:
                f.write(template.render(
                    show_pid=pid,
                    questions=format_questions(questions, answers)).encode('utf-8'))

            os.system(pdfcslatex + ' -output-directory aux '+pid+'.tex')
            os.system('mv aux/*.pdf spool/')

            db.execute(Users.update().where(Users.c.pid==pid).
                       values(printed=True))
            db.commit()

    db.close()
printfinished.help = '  $0 printfinished'


def printallexams(app, pid):
    mkdirs()
    db = app.DbSession()

    user = db.query(Users).filter_by(pid=pid).first()
    if not user: raise ValueError('invalid pid')

    questions = get_user_questions(db, pid, with_qid=True)
    points = exam.get_question_scores(models, db)
    answers = {}
    for qorder, qsubord, correct_value in (db
            .query(UserQuestions.c.qorder, Subquestions.c.qsubord, Subquestions.c.value)
            .filter(UserQuestions.c.pid == pid,
                    UserQuestions.c.qid == Subquestions.c.qid)):
        answers[(qorder, qsubord)] = correct_value

    db.close()

    filename = 'aux/%s.tex' % pid
    print "filename: %s" % filename
    with open(filename, 'w') as f:
        f.write(template.render(questions=format_questions(questions, answers, points)).encode('utf-8'))

    os.system(pdfcslatex + ' -output-directory aux '+pid+'.tex')
    os.system('mv aux/'+pid+'.pdf exams')
printallexams.help = '  $0 printallexams <pid>'


commands = {
    'printfinished': printfinished,
    'printexamlarge': printexamlarge,
    'printallexams': printallexams,
}
