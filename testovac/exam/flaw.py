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
