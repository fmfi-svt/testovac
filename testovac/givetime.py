# -*- coding: utf-8 -*-

import os
from models import Users, user_closed


def givetime(app, pid, minutes):
    minutes = int(minutes)

    db = app.DbSession()

    user = db.query(Users).filter_by(pid=pid).first()
    if not user: raise ValueError('invalid pid')

    db.execute(Users.update().where(Users.c.pid==pid).
               values(begintime=user.begintime + minutes * 60))

    db.commit()
    db.close()
givetime.help = '  $0 givetime <pid> <extra minutes>'


commands = {
    'givetime': givetime,
}
