
from models import UserQuestions, Questions, Subquestions
from sqlalchemy.orm.exc import NoResultFound


def disable(app, pid, qorder):
    qorder = int(qorder) - 1
    db = app.DbSession()
    try:
        qid = db.query(UserQuestions.c.qid).filter(
            UserQuestions.c.pid == pid,
            UserQuestions.c.qorder == qorder).one().qid
        question = db.query(Questions).filter_by(qid=qid).one()
    except NoResultFound:
        return 'No such row in user_questions!'

    print 'Question ID:', qid
    print
    print question.body
    print
    for s in db.query(Subquestions).filter(Subquestions.c.qid == qid):
        print u'{s.qsubord}) {s.body} [{s.value}]'.format(s=s)
    print

    pids = [uq.pid for uq in db.query(UserQuestions).filter(UserQuestions.c.qid == qid)];
    numuq = len(pids)
    print 'This question is already used in tests for following pids (%d in total):' % numuq
    print '\n'.join(pids)
    print

    if question.disabled:
        print 'Already disabled!'
    else:
        print 'Disable this question? (y/N)',
        if raw_input().lower()[0:1] == 'y':
            db.execute(Questions.update().where(Questions.c.qid==qid).
                       values(disabled=True))
            db.commit()
            print 'Question disabled.'
        else:
            print 'Not disabling question.'

    db.close()
disable.help = '  $0 disable <pid> <qorder>'


commands = {
    'disable': disable,
}
