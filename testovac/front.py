# -*- coding: utf-8 -*-

import os
import json
from werkzeug.wrappers import Response
from . import settings


with open(os.path.dirname(__file__) + '/front.html') as f:
    content = f.read().decode('utf-8')


def front(request):
    client_config = {
        'disableRefresh': settings.disable_refresh,
        'attemptTimeCorrection': settings.attempt_time_correction,
        'saveAfterEmit': settings.save_after_emit,
        'savingInterval': settings.saving_interval,
        'timeLimit': settings.exam.client_time_limit,
    }
    if settings.demo_mode:
        client_config['demoPid'] = settings.checkpid.generate(demo=True)

    body = content.format(title_html=settings.exam.title_html,
                          client_config=json.dumps(client_config))
    return Response(body, content_type='text/html; charset=UTF-8')
