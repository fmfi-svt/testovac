# -*- coding: utf-8 -*-

import random


title_html = u'Prijímacia skúška'

client_time_limit = 60 * 60

# extra time for the client JS to send the last batch of events
server_time_limit = client_time_limit + 30


def add_exam_models(models):
    from sqlalchemy import Table, Column, Integer, Boolean, ForeignKey
    models.Buckets = Table('buckets', models.metadata,
        Column('bid', Integer, nullable=False, primary_key=True),
        Column('border', Integer, nullable=False, index=True),
        Column('size', Integer, nullable=False),
        Column('points', Integer, nullable=False),
    )
    models.Users.append_column(
        Column('printed', Boolean, nullable=False, default=False))
    models.Questions.append_column(
        Column('bid', Integer, ForeignKey('buckets.bid'), nullable=False))


def import_questions(models, db, filename):
    from sqlalchemy.sql import func
    try:
        import xml.etree.cElementTree as ElementTree
    except ImportError:
        import xml.etree.ElementTree as ElementTree

    bid = db.query(func.max(models.Buckets.c.bid) + 1).scalar() or 0
    qid = db.query(func.max(models.Questions.c.qid) + 1).scalar() or 0

    def inner_html(element):
        # ElementTree doesn't have innerHtml/innerXml :(
        t = ElementTree.Element('t')
        t.text = element.text
        t[:] = element
        string = ElementTree.tostring(t, 'utf-8', 'html').decode('utf-8')
        return string[len(u'<t>'):-len(u'</t>')]

    tree = ElementTree.parse(filename, parser=ElementTree.XMLParser(encoding='utf-8'))
    libraries = tree.getroot()
    for library in libraries:
        db.execute(models.Buckets.insert().values(
            bid=bid,
            border=int(library.attrib['order']),
            size=int(library.attrib['size']),
            points=int(library.attrib['points'])))
        for question in library:
            db.execute(models.Questions.insert().values(
                qid=qid, bid=bid, body=inner_html(question.find('body'))))
            qsubord = ord('a')
            for answer in question.findall('answer'):
                db.execute(models.Subquestions.insert().values(
                    qid=qid, qsubord=chr(qsubord), body=inner_html(answer),
                    value=answer.attrib['val']))
                qsubord += 1
            qid += 1
        bid += 1

    db.commit()
